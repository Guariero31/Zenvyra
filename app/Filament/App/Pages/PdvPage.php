<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Enums\OrderOrigin;
use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Tenant;
use App\Services\StockManager;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class PdvPage extends Page
{
    protected static string $layout = 'filament-panels::components.layout.fullscreen';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'pdv';

    protected static ?string $title = 'PDV';

    protected string $view = 'filament.app.pages.pdv';

    // Search & filter
    public string $search = '';

    public ?int $categoryFilter = null;

    // Customer
    public string $customerSearch = '';

    public bool $showCustomerResults = false;

    public ?int $selectedCustomerId = null;

    public string $selectedCustomerName = '';

    public string $selectedCustomerPhone = '';

    // Cart: each item is ['key','product_id','variation_id','name','variation_name','sku','price','commission_rate','quantity','stock']
    /** @var array<int, array<string, mixed>> */
    public array $cart = [];

    // Checkout
    public string $discountAmount = '0';

    public string $paymentMethod = 'pix';

    public string $cashGiven = '';

    // Variation picker
    public bool $showVariationModal = false;

    public ?int $variationProductId = null;

    // Post-sale modal
    public bool $showSuccessModal = false;

    public ?int $lastOrderId = null;

    public string $lastOrderTotal = '';

    public static function canAccess(): bool
    {
        /** @var Authenticatable|FilamentUser|null $user */
        $user   = auth()->user();
        $tenant = Filament::getTenant();

        if (! $user || ! $tenant instanceof Tenant) {
            return false;
        }

        $role = $user->roleInTenant($tenant);

        return in_array($role, [
            TenantPortalRole::AdministradorCliente,
            TenantPortalRole::Leader,
            TenantPortalRole::Vendedor,
        ], true);
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    #[Computed]
    public function products(): Collection
    {
        return Product::with(['primaryImage', 'variations', 'category'])
            ->where('status', 'active')
            ->when(
                $this->search,
                fn ($query) => $query->where(function ($query): void {
                    $query->where('title', 'ilike', "%{$this->search}%")
                        ->orWhere('sku', 'ilike', "%{$this->search}%");
                }),
            )
            ->when(
                $this->categoryFilter,
                fn ($query) => $query->where('category_id', $this->categoryFilter),
            )
            ->orderBy('title')
            ->limit(60)
            ->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function customerResults(): Collection
    {
        if (strlen($this->customerSearch) < 2) {
            return collect();
        }

        return Customer::where('name', 'ilike', "%{$this->customerSearch}%")
            ->orWhere('phone', 'like', "%{$this->customerSearch}%")
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function cartSubtotal(): string
    {
        return array_reduce($this->cart, function (string $carry, array $item): string {
            return bcadd($carry, bcmul($item['price'], (string) $item['quantity'], 2), 2);
        }, '0.00');
    }

    #[Computed]
    public function cartTotal(): string
    {
        $discount = is_numeric($this->discountAmount) ? (float) $this->discountAmount : 0;
        $discount = min($discount, (float) $this->cartSubtotal);

        return bcsub($this->cartSubtotal, number_format($discount, 2, '.', ''), 2);
    }

    #[Computed]
    public function cartCount(): int
    {
        return (int) array_sum(array_column($this->cart, 'quantity'));
    }

    #[Computed]
    public function change(): string
    {
        if ($this->paymentMethod !== 'dinheiro' || ! $this->cashGiven) {
            return '0.00';
        }

        $given = (float) str_replace(',', '.', $this->cashGiven);
        $total = (float) $this->cartTotal;

        return number_format(max(0.0, $given - $total), 2, '.', '');
    }

    #[Computed]
    public function variationProduct(): ?Product
    {
        if (! $this->variationProductId) {
            return null;
        }

        return Product::with('variations')->find($this->variationProductId);
    }

    // ── Cart actions ──────────────────────────────────────────────────────────

    public function addProduct(int $productId): void
    {
        $product = Product::with('variations')->find($productId);

        if (! $product) {
            return;
        }

        if ($product->hasVariations()) {
            $this->variationProductId = $productId;
            $this->showVariationModal  = true;

            return;
        }

        $this->pushToCart($product, null);
    }

    public function addProductVariation(int $productId, int $variationId): void
    {
        $product = Product::with('variations')->find($productId);

        if (! $product) {
            return;
        }

        $variation = $product->variations->find($variationId);

        if (! $variation) {
            return;
        }

        $this->pushToCart($product, $variation);
        $this->showVariationModal  = false;
        $this->variationProductId  = null;
    }

    public function closeVariationModal(): void
    {
        $this->showVariationModal  = false;
        $this->variationProductId  = null;
    }

    private function pushToCart(Product $product, ?ProductVariation $variation): void
    {
        $key = $product->id . '-' . ($variation?->id ?? '0');

        foreach ($this->cart as $index => $item) {
            if ($item['key'] === $key) {
                $maxStock = $variation ? $variation->stock : (int) $product->stock;

                if ($this->cart[$index]['quantity'] < $maxStock) {
                    $this->cart[$index]['quantity']++;
                }

                return;
            }
        }

        $stock = $variation ? (int) $variation->stock : (int) $product->stock;

        $this->cart[] = [
            'key'             => $key,
            'product_id'      => $product->id,
            'variation_id'    => $variation?->id,
            'name'            => $product->title,
            'variation_name'  => $variation?->name,
            'sku'             => $product->sku,
            'price'           => (string) $product->price,
            'commission_rate' => (string) ($product->commission_rate ?? '0'),
            'quantity'        => 1,
            'stock'           => $stock,
        ];
    }

    public function removeFromCart(int $index): void
    {
        array_splice($this->cart, $index, 1);
        $this->cart = array_values($this->cart);
    }

    public function incrementQuantity(int $index): void
    {
        if (isset($this->cart[$index]) && $this->cart[$index]['quantity'] < $this->cart[$index]['stock']) {
            $this->cart[$index]['quantity']++;
        }
    }

    public function decrementQuantity(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        if ($this->cart[$index]['quantity'] <= 1) {
            $this->removeFromCart($index);
        } else {
            $this->cart[$index]['quantity']--;
        }
    }

    public function cancelSale(): void
    {
        $this->cart           = [];
        $this->discountAmount = '0';
        $this->cashGiven      = '';
        $this->clearCustomer();
    }

    // ── Customer actions ──────────────────────────────────────────────────────

    public function selectCustomer(int $customerId): void
    {
        $customer = Customer::find($customerId);

        if (! $customer) {
            return;
        }

        $this->selectedCustomerId   = $customer->id;
        $this->selectedCustomerName = $customer->name;
        $this->selectedCustomerPhone = (string) ($customer->phone ?? '');
        $this->customerSearch       = '';
        $this->showCustomerResults  = false;
    }

    public function clearCustomer(): void
    {
        $this->selectedCustomerId    = null;
        $this->selectedCustomerName  = '';
        $this->selectedCustomerPhone = '';
        $this->customerSearch        = '';
    }

    // ── Finalize ──────────────────────────────────────────────────────────────

    public function finalizeOrder(): void
    {
        if (empty($this->cart)) {
            return;
        }

        $userId = auth()->id();
        $total  = $this->cartTotal;

        try {
            DB::transaction(function () use ($userId, $total): void {
                $totalCommission = '0.00';

                $products = Product::whereIn('id', array_column($this->cart, 'product_id'))
                    ->get()
                    ->keyBy('id');

                $itemsData = array_map(function (array $item) use (&$totalCommission, $products): array {
                    $lineTotal        = bcmul($item['price'], (string) $item['quantity'], 2);
                    $commissionAmount = bcmul($lineTotal, bcdiv($item['commission_rate'], '100', 4), 2);
                    $totalCommission  = bcadd($totalCommission, $commissionAmount, 2);

                    /** @var Product|null $product */
                    $product = $products->get($item['product_id']);

                    return [
                        'product_id'        => $item['product_id'],
                        'variation_id'      => $item['variation_id'],
                        'quantity'          => $item['quantity'],
                        'price'             => $item['price'],
                        'cost_price'        => (string) ($product?->cost_price ?? '0.00'),
                        'commission_rate'   => $item['commission_rate'],
                        'commission_amount' => $commissionAmount,
                    ];
                }, $this->cart);

                // Discounts are not yet modeled on the `orders` table, so the
                // total is computed here (subtotal minus discount) instead of
                // via OrderLifecycleManager::recalculateTotal(), which would
                // silently discard the discount.
                $order = Order::create([
                    'customer_id'           => $this->selectedCustomerId,
                    'user_id'               => $userId,
                    'origin'                => OrderOrigin::Balcao,
                    'status'                => OrderStatus::Paid,
                    'total_amount'          => $total,
                    'commission_amount'     => $totalCommission,
                    'payment_method'        => $this->paymentMethod,
                    'refunded_amount'       => '0.00',
                    'inventory_deducted_at' => null,
                ]);

                $stockManager = app(StockManager::class);

                foreach ($itemsData as $item) {
                    $orderItem = OrderItem::create([
                        'order_id'             => $order->id,
                        'product_id'           => $item['product_id'],
                        'product_variation_id' => $item['variation_id'],
                        'quantity'             => $item['quantity'],
                        'price_at_sale'        => $item['price'],
                        'cost_at_sale'         => $item['cost_price'],
                        'commission_rate'      => $item['commission_rate'],
                        'commission_amount'    => $item['commission_amount'],
                    ]);

                    // Handles negative-stock policy, atomic decrement and the
                    // StockMovement audit trail — same path used elsewhere.
                    $stockManager->registerSale($orderItem);
                }

                $order->update(['inventory_deducted_at' => now()]);

                $this->lastOrderId    = $order->id;
                $this->lastOrderTotal = $total;
            });
        } catch (ValidationException $e) {
            Notification::make()
                ->danger()
                ->title('Não foi possível concluir a venda')
                ->body(collect($e->errors())->flatten()->implode(' '))
                ->send();

            return;
        }

        $this->showSuccessModal = true;
        $this->cancelSale();
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->lastOrderId      = null;
        $this->lastOrderTotal   = '';
    }
}
