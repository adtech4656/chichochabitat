<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Models\Property;
use Filament\Resources\Pages\CreateRecord;

class CreateProperty extends CreateRecord
{
    protected static string $resource = PropertyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['agent_id'] ??= auth()->id();
        $data['status'] ??= Property::STATUS_PENDING;
        $data['agent_kyc_status'] ??= 'approved';
        $data['cover_image'] ??= 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=80';

        return $data;
    }
}

