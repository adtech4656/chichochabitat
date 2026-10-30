<?php

namespace App\Filament\Resources\AgentPropertyResource\Pages;

use App\Filament\Resources\AgentPropertyResource;
use App\Models\Property;
use Filament\Resources\Pages\EditRecord;

class EditAgentProperty extends EditRecord
{
    protected static string $resource = AgentPropertyResource::class;

    /**
     * Re-assert ownership on the write itself. Route model binding and the
     * scoped query are defence in depth, not the guarantee.
     */
    protected function authorizeAccess(): void
    {
        abort_unless(
            AgentPropertyResource::canEdit($this->getRecord()),
            403,
            'You may only edit your own listings.'
        );
    }

    /** Any detail change must be re-verified before it is public again. */
    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($record->status === Property::STATUS_APPROVED) {
            $record->forceFill([
                'status' => Property::STATUS_IN_REVIEW,
                'published_at' => null,
            ])->save();
        }
    }
}
