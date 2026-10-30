<?php

namespace App\Filament\Resources\StaffProfileResource\Pages;

use App\Filament\Resources\StaffProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffProfile extends EditRecord
{
    protected static string $resource = StaffProfileResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(StaffProfileResource::canEdit($this->getRecord()), 403);
    }
}
