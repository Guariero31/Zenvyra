<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderOrigin;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Tenant;
use App\Services\OrderLifecycleManager;
use App\Services\OrderRefundManager;
use App\Support\PgsqlTenantSearchPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Popula catálogo e vendas dentro do schema físico de cada loja ativa. O
 * search_path é apontado para o schema do tenant durante a carga e restaurado
 * ao final. É idempotente: lojas que já têm catálogo são ignoradas.
 */
class TenantDataSeeder extends Seeder
{
    private const CATALOG = [
        'Camisetas' => ['Camiseta Básica', 'Camiseta Estampada', 'Regata Fitness'],
        'Calçados' => ['Tênis Casual', 'Tênis Esportivo', 'Chinelo Slide'],
        'Acessórios' => ['Boné Aba Reta', 'Carteira de Couro', 'Mochila Urbana'],
        'Calças' => ['Calça Jeans', 'Bermuda Sarja', 'Calça Jogger'],
    ];

    public function run(): void
    {
        Tenant::query()
            ->where('te_ativo', true)
            ->get()
            ->each(fn (Tenant $tenant) => $this->seedTenant($tenant));
    }

    private function seedTenant(Tenant $tenant): void
    {
        $usesPgsql = DB::connection()->getDriverName() === 'pgsql';

        if ($usesPgsql) {
            PgsqlTenantSearchPath::apply($tenant->te_schema);
        }

        try {
            if (Category::query()->exists()) {
                return;
            }

            $products = $this->seedCatalog();
            $this->seedSales($tenant, $products);
        } finally {
            if ($usesPgsql) {
                PgsqlTenantSearchPath::reset();
            }
        }
    }

    /**
     * @return Collection<int, Product>
     */
    private function seedCatalog(): Collection
    {
        $products = new Collection;

        foreach (self::CATALOG as $categoryName => $productNames) {
            $category = Category::factory()->create([
                'name' => $categoryName,
                'slug' => str()->slug($categoryName),
            ]);

            foreach ($productNames as $index => $productName) {
                $isSimpleProduct = $index === array_key_last($productNames);

                $product = Product::factory()
                    ->for($category)
                    ->create([
                        'title' => $productName,
                        'stock' => $isSimpleProduct ? fake()->numberBetween(20, 80) : 0,
                    ]);

                if (! $isSimpleProduct) {
                    ProductVariation::factory()
                        ->count(fake()->numberBetween(2, 3))
                        ->for($product)
                        ->create();
                }

                ProductImage::factory()
                    ->count(fake()->numberBetween(2, 4))
                    ->for($product)
                    ->create();

                $products->push($product->load('variations'));
            }
        }

        return $products;
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function seedSales(Tenant $tenant, Collection $products): void
    {
        $userId = $tenant->users()->first()?->id;
        $customers = Customer::factory()->count(8)->create();

        $lifecycle = app(OrderLifecycleManager::class);
        $refunds = app(OrderRefundManager::class);

        $outcomes = [
            'pending', 'pending',
            'paid', 'paid', 'paid', 'paid',
            'partial_refund', 'partial_refund',
            'full_refund',
            'canceled', 'canceled',
        ];

        foreach ($outcomes as $outcome) {
            $order = $this->makeOrder($products, $customers->random(), $userId);

            match ($outcome) {
                'paid' => $order->update(['status' => OrderStatus::Paid]),
                'partial_refund' => $this->partiallyRefund($order, $refunds, $userId),
                'full_refund' => $this->fullyRefund($order, $refunds, $userId),
                'canceled' => $lifecycle->cancel($order),
                default => null,
            };
        }
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function makeOrder(Collection $products, Customer $customer, ?int $userId): Order
    {
        $order = Order::create([
            'customer_id' => $customer->id,
            'user_id' => $userId,
            'origin' => fake()->randomElement(OrderOrigin::cases()),
            'total_amount' => 0,
            'payment_method' => fake()->randomElement(['dinheiro', 'pix', 'cartao_credito', 'cartao_debito']),
            'status' => OrderStatus::Pending,
        ]);

        foreach ($products->random(fake()->numberBetween(1, 3)) as $product) {
            $variation = $product->variations->isNotEmpty()
                ? ($product->variations->firstWhere('stock', '>', 0) ?? $product->variations->first())
                : null;

            $maxQuantity = $variation
                ? max(1, min(3, $variation->stock))
                : max(1, min(3, $product->stock));

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variation_id' => $variation?->id,
                'quantity' => fake()->numberBetween(1, $maxQuantity),
                'price_at_sale' => $product->price,
                'cost_at_sale' => $product->cost_price,
            ]);
        }

        return $order->refresh();
    }

    private function partiallyRefund(Order $order, OrderRefundManager $refunds, ?int $userId): void
    {
        $order->update(['status' => OrderStatus::Paid]);

        $firstItem = $order->items()->first();

        if ($firstItem) {
            $refunds->refund(
                order: $order,
                items: [$firstItem->id => 1],
                reason: 'Cliente solicitou devolução parcial.',
                restock: true,
                userId: $userId,
            );
        }
    }

    private function fullyRefund(Order $order, OrderRefundManager $refunds, ?int $userId): void
    {
        $order->update(['status' => OrderStatus::Paid]);

        $refunds->refund(
            order: $order,
            items: $refunds->refundableItemsMap($order),
            reason: 'Pedido cancelado após pagamento.',
            restock: true,
            userId: $userId,
        );
    }
}
