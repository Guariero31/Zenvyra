<?php

namespace App\Filament\App\Resources\TeamMembers\Pages;

use App\Filament\App\Resources\TeamMembers\TeamMemberResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantTeamService;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTeamMember extends EditRecord
{
    protected static string $resource = TeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Remover da equipe')
                ->visible(fn (): bool => TeamMemberResource::canDelete($this->record))
                ->modalHeading('Remover membro da equipe')
                ->modalDescription('O usuário perderá acesso a esta loja, mas a conta continuará existindo na plataforma.')
                ->successNotificationTitle('Membro removido da equipe')
                ->action(function (): void {
                    $tenant = Filament::getTenant();
                    $actor = auth()->user();

                    abort_unless($tenant instanceof Tenant && $actor instanceof User, 403);

                    app(TenantTeamService::class)->removeMember(
                        $tenant,
                        $this->record,
                        $actor,
                    );

                    $this->redirect(TeamMemberResource::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Tenant) {
            $pivot = $this->record->tenants()
                ->where('te_tenants.id', $tenant->getKey())
                ->first()
                ?->pivot;

            $data['tu_papel'] = $pivot->tu_papel ?? null;
            $data['tu_rotulo_setor'] = $pivot->tu_rotulo_setor;
            $data['tu_commission_rate'] = $pivot->tu_commission_rate ?? 0;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $tenant = Filament::getTenant();

        abort_unless($tenant instanceof Tenant && $record instanceof User, 403);

        return app(TenantTeamService::class)->updateMember($tenant, $record, $data);
    }
}
