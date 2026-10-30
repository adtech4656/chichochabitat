<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(DepartmentResource::canEdit($this->getRecord()), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => DepartmentResource::canDelete($this->getRecord())),
        ];
    }
}
