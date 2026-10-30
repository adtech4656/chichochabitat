<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivity extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Property::query()
                    ->with('reviewer')
                    ->whereNotNull('reviewed_at')
                    ->latest('reviewed_at')
                    ->limit(8),
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state) => match ($state) {
                        Property::STATUS_APPROVED => 'success',
                        Property::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('reviewer.name')->label('Reviewed by')->placeholder('—'),
                Tables\Columns\TextColumn::make('reviewed_at')->since()->label('When'),
            ])
            ->paginated(false);
    }
}
