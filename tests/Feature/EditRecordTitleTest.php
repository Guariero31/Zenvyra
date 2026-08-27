<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Filament's default EditRecord title is "Editar :label". The app overrides
 * that translation (lang/vendor/filament-panels/pt_BR) so the page just
 * shows the record's own label, with no "Editar" prefix, across the system.
 */
class EditRecordTitleTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_title_has_no_editar_prefix(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $tenant = Tenant::create(['te_name' => 'Loja Titulo Teste']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();
        session(['tenant_id' => $tenant->getKey()]);

        $category = Category::create(['name' => 'Camisetas', 'slug' => 'camisetas']);

        $component = Livewire::test(EditCategory::class, ['record' => $category->id]);

        $this->assertSame('Camisetas', $component->instance()->getTitle());
    }
}
