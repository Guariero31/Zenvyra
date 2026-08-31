<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderOrigin;
use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\Scopes\TenantMemberOperatorScope;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderLifecycleManager;
use App\Services\TenantOrderPurgeService;
use App\Support\PgsqlTenantSearchPath;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo e vendas demo por loja. Vendas pagas são distribuídas entre vendedores
 * ao longo dos últimos meses para alimentar os gráficos do dashboard.
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
            $this->purgeForeignOperatorOrders($tenant);

            $products = Category::query()->exists()
                ? Product::query()->with('variations')->get()
                : $this->seedCatalog();

            if ($products->isEmpty()) {
                return;
            }

            $this->seedVendedorSales($tenant, $products);
            $this->seedOperationalOrders($tenant, $products);
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
                        'stock' => $isSimpleProduct ? fake()->numberBetween(50, 120) : 0,
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
    private function seedVendedorSales(Tenant $tenant, Collection $products): void
    {
        $vendedores = $this->vendedoresDoTenant($tenant);

        if ($vendedores->isEmpty()) {
            return;
        }

        $vendedoresSemVendas = $vendedores->reject(fn (User $vendedor): bool => $this->vendedorTemVendasPagas($vendedor));

        if ($vendedoresSemVendas->isEmpty()) {
            return;
        }

        $customers = Customer::query()->exists()
            ? Customer::all()
            : Customer::factory()->count(12)->create();

        $lifecycle = app(OrderLifecycleManager::class);
        $this->ensureStockForDemo($products);

        for ($monthsAgo = 0; $monthsAgo < 10; $monthsAgo++) {
            foreach ($vendedoresSemVendas as $vendedor) {
                $ordersCount = match ($monthsAgo) {
                    0 => fake()->numberBetween(3, 6),
                    default => fake()->numberBetween(1, 4),
                };

                for ($i = 0; $i < $ordersCount; $i++) {
                    $soldAt = Carbon::now()
                        ->subMonths($monthsAgo)
                        ->startOfMonth()
                        ->addDays(fake()->numberBetween(0, 25))
                        ->setTime(fake()->numberBetween(9, 18), fake()->numberBetween(0, 59));

                    $order = $this->makeOrder(
                        products: $products,
                        customer: $customers->random(),
                        userId: $vendedor->id,
                        soldAt: $soldAt,
                    );

                    $lifecycle->recalculateTotal($order->refresh());
                    $order->update(['status' => OrderStatus::Paid]);
                }
            }
        }
    }

    /**
     * Pedidos pendentes e cancelados para badges e fluxos operacionais.
     *
     * @param  Collection<int, Product>  $products
     */
    private function seedOperationalOrders(Tenant $tenant, Collection $products): void
    {
        if (Order::withoutGlobalScope(TenantMemberOperatorScope::class)
            ->where('status', OrderStatus::Pending)
            ->exists()) {
            return;
        }

        $operatorId = $tenant->users()->first()?->id;
        $customers = Customer::query()->exists()
            ? Customer::all()
            : Customer::factory()->count(3)->create();

        foreach (range(1, 3) as $_) {
            $this->makeOrder(
                products: $products,
                customer: $customers->random(),
                userId: $operatorId,
                soldAt: now(),
            );
        }

        $canceled = $this->makeOrder(
            products: $products,
            customer: $customers->random(),
            userId: $operatorId,
            soldAt: now()->subDays(2),
        );

        app(OrderLifecycleManager::class)->cancel($canceled);
    }

    private function purgeForeignOperatorOrders(Tenant $tenant): void
    {
        $memberIds = $tenant->users()->pluck('users.id');

        Order::withoutGlobalScope(TenantMemberOperatorScope::class)
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', $memberIds)
            ->each(fn (Order $order) => app(TenantOrderPurgeService::class)->purge($order));
    }

    /** @return Collection<int, User> */
    private function vendedoresDoTenant(Tenant $tenant): Collection
    {
        return $tenant->users()
            ->wherePivot('tu_papel', TenantPortalRole::Vendedor->value)
            ->get();
    }

    private function vendedorTemVendasPagas(User $vendedor): bool
    {
        return Order::withoutGlobalScope(TenantMemberOperatorScope::class)
            ->where('user_id', $vendedor->id)
            ->whereIn('status', OrderStatus::soldValues())
            ->exists();
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function makeOrder(
        Collection $products,
        Customer $customer,
        ?int $userId,
        Carbon $soldAt,
    ): Order {
        $order = Order::create([
            'customer_id' => $customer->id,
            'user_id' => $userId,
            'origin' => fake()->randomElement(OrderOrigin::cases()),
            'total_amount' => 0,
            'commission_amount' => 0,
            'payment_method' => fake()->randomElement(['dinheiro', 'pix', 'cartao_credito', 'cartao_debito']),
            'status' => OrderStatus::Pending,
            'created_at' => $soldAt,
            'updated_at' => $soldAt,
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

    /** @param  Collection<int, Product>  $products */
    private function ensureStockForDemo(Collection $products): void
    {
        foreach ($products as $product) {
            if ($product->variations->isEmpty()) {
                $product->update(['stock' => 500]);

                continue;
            }

            $product->variations()->update(['stock' => 200]);
        }
    }
}
