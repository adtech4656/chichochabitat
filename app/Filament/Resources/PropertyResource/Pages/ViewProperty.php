<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Models\Property;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(PropertyResource::canView($this->getRecord()), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('approve')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Property $record) => PropertyResource::canApprove()
                    && $record->status !== Property::STATUS_APPROVED)
                ->disabled(fn (Property $record) => ! $record->isApprovable())
                ->action(function (Property $record): void {
                    abort_unless(auth()->user()?->hasPermission('properties.approve'), 403);

                    if (! $record->isApprovable()) {
                        Notification::make()
                            ->title('Cannot approve')
                            ->body('Mandatory media, documents, or KYC are incomplete.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->forceFill([
                        'status' => Property::STATUS_APPROVED,
                        'reviewed_at' => now(),
                        'reviewed_by' => auth()->id(),
                        'published_at' => now(),
                    ])->save();

                    Notification::make()->title('Approved and published')->success()->send();
                }),
        ];
    }
}
