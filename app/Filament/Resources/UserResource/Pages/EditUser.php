<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(UserResource::canEdit($this->getRecord()), 403);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $profile = $record->staffProfile()->withoutGlobalScopes()->first();

        $data['role_assignments'] = $record->getRoleNames()->all() ?: [$record->role];
        $data['permissions'] = $record->getDirectPermissions()->pluck('name')->all();
        $data['staff_branch_id'] = $profile?->branch_id;
        $data['staff_department_id'] = $profile?->department_id;
        $data['employee_number'] = $profile?->employee_number;
        $data['job_title'] = $profile?->job_title;
        $data['date_of_birth'] = $profile?->date_of_birth;
        $data['address'] = $profile?->address;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => UserResource::canDelete($this->getRecord())),
        ];
    }

    protected function afterSave(): void
    {
        if (! auth()->user()?->isAdmin()) {
            return;
        }

        UserResource::synchronizeAdminFields($this->getRecord(), $this->form->getRawState());
    }
}
