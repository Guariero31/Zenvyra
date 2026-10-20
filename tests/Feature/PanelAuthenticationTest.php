<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_session_opens_master_login_instead_of_forbidden_page(): void
    {
        $storeUser = User::factory()->create();
        $this->actingAs($storeUser, 'web');

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
        $this->assertAuthenticatedAs($storeUser, 'web');
        $this->assertGuest('admin');
    }

    public function test_master_can_sign_in_without_replacing_store_identity(): void
    {
        $storeUser = User::factory()->create();
        $masterUser = User::factory()->platformAdmin()->create();
        $this->actingAs($storeUser, 'web');
        Filament::setCurrentPanel('admin');

        Livewire::test(Login::class)
            ->fillForm(['email' => $masterUser->email, 'password' => 'password'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($masterUser, 'admin');
        $this->assertAuthenticatedAs($storeUser, 'web');
        $this->get('/admin')->assertOk();
    }

    public function test_store_credentials_cannot_sign_in_to_master(): void
    {
        $storeUser = User::factory()->create();
        Filament::setCurrentPanel('admin');

        Livewire::test(Login::class)
            ->fillForm(['email' => $storeUser->email, 'password' => 'password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest('admin');
    }

    public function test_master_session_does_not_authenticate_store_panel(): void
    {
        $this->actingAs(User::factory()->platformAdmin()->create(), 'admin');

        $this->get('/app')->assertRedirect('/app/login');
        $this->assertGuest('web');
    }
}
