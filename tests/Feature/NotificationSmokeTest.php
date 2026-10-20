<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\Admin\Resources\StoreOwners\Pages\CreateStoreOwner;
use App\Filament\App\Resources\Categories\Pages\CreateCategory;
use App\Filament\App\Resources\Products\Pages\EditProduct;
use App\Filament\App\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationSmokeTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::create(['te_name' => 'Loja Notificacao Teste']);
        $user = User::factory()->create();
        $this->tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
        session(['tenant_id' => $this->tenant->getKey()]);
    }

    public function test_creating_a_category_sends_a_success_notification(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Categoria Notificação',
                'slug' => 'categoria-notificacao',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Notification::assertNotified();
    }

    public function test_upload_images_action_is_configured_with_a_success_notification(): void
    {
        $category = Category::create(['name' => 'Categoria Teste', 'slug' => 'categoria-teste']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Produto Teste',
            'sku' => 'PROD-TESTE',
            'price' => '10.00',
            'stock' => 5,
            'status' => 'active',
        ]);

        $component = Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ]);

        $action = collect($component->instance()->getTable()->getHeaderActions())
            ->first(fn ($action): bool => $action->getName() === 'uploadImages');

        $this->assertNotNull($action);
        $this->assertSame('Imagens adicionadas com sucesso', $action->getSuccessNotificationTitle());
    }

    public function test_creating_a_store_owner_shows_a_contextual_notification_title(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel('admin');
        Filament::bootCurrentPanel();

        Livewire::test(CreateStoreOwner::class)
            ->fillForm([
                'name' => 'Lojista Teste',
                'email' => 'lojista.teste@example.com',
                'password' => 'password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Notification::assertNotified('Lojista adicionado');
    }
}
