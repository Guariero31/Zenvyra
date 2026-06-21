<?php

namespace App\Filament\App\Resources\TeamMembers\Pages;

use App\Filament\App\Resources\TeamMembers\TeamMemberResource;
use App\Models\Tenant;
use App\Services\PlanLimitService;
use App\Services\TenantTeamService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamMemberResource::class;

    public function getTitle(): string
    {
        return 'Adicionar membro';
    }

    protected function beforeCreate(): void
    {
        $tenant = Filament::getTenant();

        if ($tenant === null) {
            return;
        }

        try {
            app(PlanLimitService::class)->assertCanAddUser($tenant);
        } catch (RuntimeException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $tenant = Filament::getTenant();

        abort_unless($tenant instanceof Tenant, 403);

        return app(TenantTeamService::class)->addMember($tenant, $data);
    }
}
