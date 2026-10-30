<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Verification queue';

    protected static ?string $modelLabel = 'property';

    protected static ?string $pluralModelLabel = 'properties';

    protected static ?int $navigationSort = 1;

    /* ----------------------------- Authorisation -----------------------------
     | Every entry point is permission-checked server-side. Hiding a button is
     | a usability affordance, never a control: Filament calls these methods on
     | the request that actually renders or mutates the record.
     */

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('properties.view') ?? false;
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('properties.edit') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('properties.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('properties.delete') ?? false;
    }

    /** The approve action is gated on its own permission, not on panel access. */
    public static function canApprove(): bool
    {
        return auth()->user()?->hasPermission('properties.approve') ?? false;
    }

    public static function canReject(): bool
    {
        return auth()->user()?->hasAnyPermission(['properties.reject', 'properties.approve']) ?? false;
    }

    /** Pending work is the whole point of this screen, so surface the count. */
    public static function getNavigationBadge(): ?string
    {
        $pending = Property::query()
            ->whereIn('status', [Property::STATUS_PENDING, Property::STATUS_IN_REVIEW])
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Listing')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options(['House' => 'House', 'Land' => 'Land', 'Shop' => 'Shop', 'Hotel' => 'Hotel'])
                    ->required(),
                Forms\Components\Select::make('intent')
                    ->options(['sale' => 'For sale', 'rent' => 'For rent'])
                    ->required(),
                Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
                Forms\Components\TextInput::make('cover_image')
                    ->label('Cover image URL')
                    ->url()
                    ->maxLength(2048)
                    ->columnSpanFull()
                    ->helperText('Stored URL of the verified cover photograph.'),
                Forms\Components\TagsInput::make('tags')
                    ->label('Photo-verified amenities')
                    ->separator(',')
                    ->columnSpanFull(),
            ])->columns(3),

            Forms\Components\Section::make('Location')->schema([
                Forms\Components\TextInput::make('district')->required(),
                Forms\Components\TextInput::make('city')->required(),
                Forms\Components\TextInput::make('latitude')->numeric()->step('0.0000001'),
                Forms\Components\TextInput::make('longitude')->numeric()->step('0.0000001'),
            ])->columns(2),

            Forms\Components\Section::make('Commercials')->schema([
                Forms\Components\TextInput::make('price')->numeric()->required()->prefix('XOF'),
                Forms\Components\TextInput::make('platform_fee')->numeric()->prefix('XOF')
                    ->helperText('Added on top of the agent price. Never shown to the customer.'),
                Forms\Components\TextInput::make('bedrooms')->numeric(),
                Forms\Components\TextInput::make('bathrooms')->numeric(),
                Forms\Components\TextInput::make('area')->numeric()->suffix('m²'),
            ])->columns(3),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Property')->schema([
                Infolists\Components\TextEntry::make('title')->weight('bold')->size('lg'),
                Infolists\Components\TextEntry::make('location')
                    ->getStateUsing(fn (Property $record) => "{$record->district}, {$record->city}"),
                Infolists\Components\TextEntry::make('type'),
                Infolists\Components\TextEntry::make('intent')
                    ->formatStateUsing(fn (string $state) => $state === 'rent' ? 'For rent' : 'For sale'),
                Infolists\Components\TextEntry::make('reference')->badge()->color('gray'),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
            ])->columns(3),

            Infolists\Components\Section::make('Verification')->schema([
                Infolists\Components\TextEntry::make('status')->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state) => match ($state) {
                        Property::STATUS_APPROVED => 'success',
                        Property::STATUS_REJECTED => 'danger',
                        Property::STATUS_IN_REVIEW, Property::STATUS_NEEDS_INFO => 'warning',
                        default => 'gray',
                    }),

                Infolists\Components\TextEntry::make('photos')
                    ->label('Photo checklist')
                    ->getStateUsing(fn (Property $record) => "{$record->captured_shots} / {$record->required_shots}")
                    ->badge()
                    ->color(fn (Property $record) => $record->missingShots() === 0 ? 'success' : 'danger'),

                Infolists\Components\TextEntry::make('documents_count')
                    ->label('Documents')
                    ->getStateUsing(fn (Property $record) => $record->documents_count.' / 3')
                    ->badge()
                    ->color(fn (Property $record) => $record->missingDocuments() === 0 ? 'success' : 'danger'),

                Infolists\Components\TextEntry::make('agent_kyc_status')
                    ->label('Agent KYC')
                    ->badge()
                    ->color(fn (string $state) => $state === 'approved' ? 'success' : 'warning'),

                Infolists\Components\TextEntry::make('gps_delta_m')
                    ->label('GPS drift')
                    ->suffix(' m')
                    ->badge()
                    ->color(fn (Property $record) => $record->gps_delta_m <= 100 ? 'success' : 'danger'),

                Infolists\Components\TextEntry::make('agent.name')->label('Agent'),
                Infolists\Components\TextEntry::make('review_notes')->columnSpanFull()->placeholder('No internal notes.'),
            ])->columns(4),

            Infolists\Components\Section::make('Commercials')->schema([
                Infolists\Components\TextEntry::make('price')
                    ->money('XOF', locale: 'fr')
                    ->label('Agent payout'),
                Infolists\Components\TextEntry::make('platform_fee')
                    ->money('XOF', locale: 'fr'),
                Infolists\Components\TextEntry::make('customerPrice')
                    ->money('XOF', locale: 'fr')
                    ->label('Customer sees'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('agent'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Property $record) => "{$record->district}, {$record->city}")
                    ->wrap(),

                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state) => match ($state) {
                        Property::STATUS_APPROVED => 'success',
                        Property::STATUS_REJECTED => 'danger',
                        Property::STATUS_IN_REVIEW, Property::STATUS_NEEDS_INFO => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('media')
                    ->label('Media')
                    ->getStateUsing(fn (Property $record) => "{$record->captured_shots}/{$record->required_shots} · {$record->documents_count}/3")
                    ->badge()
                    ->color(fn (Property $record) => $record->isApprovable() || $record->status === Property::STATUS_APPROVED ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('price')
                    ->money('XOF', locale: 'fr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Property::STATUS_PENDING => 'Pending',
                        Property::STATUS_IN_REVIEW => 'In review',
                        Property::STATUS_APPROVED => 'Approved',
                        Property::STATUS_REJECTED => 'Rejected',
                        Property::STATUS_NEEDS_INFO => 'Needs info',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options(['House' => 'House', 'Land' => 'Land', 'Shop' => 'Shop', 'Hotel' => 'Hotel']),
                Tables\Filters\SelectFilter::make('intent')
                    ->options(['sale' => 'For sale', 'rent' => 'For rent']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve and publish?')
                    ->modalDescription('This makes the listing publicly visible and writes an immutable audit entry.')
                    ->visible(fn (Property $record) => static::canApprove()
                        && $record->status !== Property::STATUS_APPROVED)
                    ->disabled(fn (Property $record) => ! $record->isApprovable())
                    ->tooltip(fn (Property $record) => $record->isApprovable()
                        ? null
                        : 'Blocked: '.$record->missingShots().' shot(s) and '.$record->missingDocuments().' document(s) missing'
                            .($record->agent_kyc_status !== 'approved' ? '; agent KYC pending' : ''))
                    ->action(function (Property $record): void {
                        // Re-check authorisation and completeness on the server.
                        // A disabled or hidden button is a UI hint, never the
                        // enforcement point.
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

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Property $record) => static::canReject()
                        && $record->status !== Property::STATUS_REJECTED)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason (shared with the agent)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Property $record, array $data): void {
                        abort_unless(auth()->user()?->hasAnyPermission(['properties.reject', 'properties.approve']), 403);

                        $record->forceFill([
                            'status' => Property::STATUS_REJECTED,
                            'review_notes' => $data['reason'],
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                            'published_at' => null,
                        ])->save();

                        Notification::make()->title('Listing rejected')->danger()->send();
                    }),

                Tables\Actions\Action::make('requestInfo')
                    ->label('Request info')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('warning')
                    ->visible(fn (Property $record) => static::canReject())
                    ->form([
                        Forms\Components\Textarea::make('note')->required()->rows(3),
                    ])
                    ->action(function (Property $record, array $data): void {
                        abort_unless(auth()->user()?->hasAnyPermission(['properties.reject', 'properties.approve']), 403);

                        $record->forceFill([
                            'status' => Property::STATUS_NEEDS_INFO,
                            'review_notes' => $data['note'],
                        ])->save();

                        Notification::make()->title('Information requested')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->visible(fn () => auth()->user()?->hasPermission('properties.delete') ?? false),
            ])
            ->emptyStateHeading('Queue is clear')
            ->emptyStateDescription('No properties are waiting for verification.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'view' => Pages\ViewProperty::route('/{record}'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
