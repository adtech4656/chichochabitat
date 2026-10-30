<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentPropertyResource\Pages;
use App\Models\Property;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Agent-facing listing manager.
 *
 * Scoped to the signed-in agent's own properties — an agent can never see or
 * edit another agent's inventory.
 */
class AgentPropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'My listings';

    protected static ?string $modelLabel = 'listing';

    protected static ?string $pluralModelLabel = 'listings';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('agent_id', auth()->id());
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasPanelRole(User::AGENT_PANEL_ROLES);
    }

    public static function canCreate(): bool
    {
        return false; // creation happens through the guided capture wizard
    }

    /**
     * Explicit object-level ownership check.
     *
     * The scoped query above already hides other agents' rows from the list,
     * but a crafted request to /agent/agent-properties/{id}/edit must be
     * refused on its own merits rather than relying on route binding.
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasPanelRole(['agent']) && (int) $record->agent_id === (int) $user->id;
    }

    public static function canView($record): bool
    {
        return static::canEdit($record);
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Listing')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\Select::make('intent')
                    ->options(['sale' => 'For sale', 'rent' => 'For rent'])
                    ->required(),
                Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Commercials')->schema([
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->prefix('XOF')
                    ->helperText('This is your payout. The platform fee is added on top and shown to the customer.'),
                Forms\Components\Placeholder::make('payout_note')
                    ->hiddenLabel()
                    ->content('Changes to price or details send the listing back to review.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->description(fn (Property $record) => "{$record->district}, {$record->city}"),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state) => match ($state) {
                        Property::STATUS_APPROVED => 'success',
                        Property::STATUS_REJECTED => 'danger',
                        Property::STATUS_IN_REVIEW, Property::STATUS_NEEDS_INFO => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price')->money('XOF', locale: 'fr')->label('Your payout'),
                Tables\Columns\TextColumn::make('views')->label('Views'),
                Tables\Columns\TextColumn::make('review_notes')
                    ->label('Reviewer note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Submitted'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Property $record) => $record->status !== Property::STATUS_APPROVED),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgentProperties::route('/'),
            'edit' => Pages\EditAgentProperty::route('/{record}/edit'),
        ];
    }
}
