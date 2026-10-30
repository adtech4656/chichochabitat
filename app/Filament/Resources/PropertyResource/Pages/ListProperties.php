<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProperties extends ListRecords
{
    protected static string $resource = PropertyResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(PropertyResource::canViewAny(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => PropertyResource::canCreate()),
        ];
    }

    /**
     * Status tabs mirror the brief's B2 queue: All / Pending / In review /
     * Approved / Rejected / Needs info.
     */
    public function getTabs(): array
    {
        $byStatus = fn (string $status) => fn (Builder $query) => $query->where('status', $status);

        return [
            'all' => Tab::make('All'),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing($byStatus('pending'))
                ->badge(fn () => PropertyResource::getModel()::where('status', 'pending')->count()),
            'in_review' => Tab::make('In review')
                ->modifyQueryUsing($byStatus('in_review'))
                ->badge(fn () => PropertyResource::getModel()::where('status', 'in_review')->count()),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing($byStatus('approved')),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing($byStatus('rejected')),
            'needs_info' => Tab::make('Needs info')
                ->modifyQueryUsing($byStatus('needs_info')),
        ];
    }
}
