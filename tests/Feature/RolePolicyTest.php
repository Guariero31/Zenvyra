<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\TeamMemberPolicy;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $leader;

    private User $vendedor;

    private User $rh;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::create(['te_name' => 'Loja Role Test']);

        $attach = fn (User $user, TenantPortalRole $role) => $this->tenant->users()->attach($user, [
            'tu_papel' => $role->value,
            'tu_commission_rate' => 0,
        ]);

        $this->admin = tap(User::factory()->create(), fn ($u) => $attach($u, TenantPortalRole::AdministradorCliente));
        $this->leader = tap(User::factory()->create(), fn ($u) => $attach($u, TenantPortalRole::Leader));
        $this->vendedor = tap(User::factory()->create(), fn ($u) => $attach($u, TenantPortalRole::Vendedor));
        $this->rh = tap(User::factory()->create(), fn ($u) => $attach($u, TenantPortalRole::Rh));
        $this->member = tap(User::factory()->create(), fn ($u) => $attach($u, TenantPortalRole::Member));

        $this->actingAs($this->admin);

        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
    }

    // ── ActivityLogPolicy ─────────────────────────────────────────────────────

    public function test_only_admin_and_leader_can_view_activity_logs(): void
    {
        $policy = new ActivityLogPolicy;
        $log = new ActivityLog;

        $this->assertTrue($policy->viewAny($this->admin));
        $this->assertTrue($policy->viewAny($this->leader));

        $this->assertFalse($policy->viewAny($this->vendedor));
        $this->assertFalse($policy->viewAny($this->rh));
        $this->assertFalse($policy->viewAny($this->member));
    }

    public function test_nobody_can_create_or_delete_activity_logs(): void
    {
        $policy = new ActivityLogPolicy;
        $log = new ActivityLog;

        foreach ([$this->admin, $this->leader, $this->vendedor, $this->rh, $this->member] as $user) {
            $this->assertFalse($policy->create($user));
            $this->assertFalse($policy->delete($user, $log));
        }
    }

    // ── TeamMemberPolicy — Rh ─────────────────────────────────────────────────

    public function test_rh_can_view_and_manage_team_members(): void
    {
        $policy = new TeamMemberPolicy;

        $this->assertTrue($policy->viewAny($this->rh));
        $this->assertTrue($policy->view($this->rh, $this->member));
        $this->assertTrue($policy->create($this->rh));
        $this->assertTrue($policy->update($this->rh, $this->member));
    }

    public function test_rh_cannot_delete_team_members(): void
    {
        $policy = new TeamMemberPolicy;

        $this->assertFalse($policy->delete($this->rh, $this->member));
    }

    public function test_only_manager_can_delete_team_members(): void
    {
        $policy = new TeamMemberPolicy;

        $this->assertTrue($policy->delete($this->admin, $this->member));
        $this->assertTrue($policy->delete($this->leader, $this->member));

        $this->assertFalse($policy->delete($this->vendedor, $this->member));
        $this->assertFalse($policy->delete($this->rh, $this->member));
    }

    public function test_vendedor_and_member_cannot_manage_team(): void
    {
        $policy = new TeamMemberPolicy;

        $this->assertFalse($policy->viewAny($this->vendedor));
        $this->assertFalse($policy->viewAny($this->member));
        $this->assertFalse($policy->create($this->vendedor));
        $this->assertFalse($policy->create($this->member));
    }

    public function test_outsider_cannot_access_anything(): void
    {
        $outsider = User::factory()->create(); // não vinculado ao tenant

        $this->assertFalse((new ActivityLogPolicy)->viewAny($outsider));
        $this->assertFalse((new TeamMemberPolicy)->viewAny($outsider));
    }
}
