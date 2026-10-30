<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        if (auth()->user()?->isAdmin()) {
            UserResource::synchronizeAdminFields($this->getRecord(), $this->form->getRawState());

            return;
        }

        $record = $this->getRecord();
        $record->forceFill(['role' => 'customer'])->save();
        $record->assignRole(Role::findOrCreate('customer', 'web'));
    }
}
