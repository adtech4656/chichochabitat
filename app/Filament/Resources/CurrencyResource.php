<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Currency & rate console.
 *
 * Administrators can override any rate manually or pull live rates on demand.
 * Every change is logged, so a price dispute can always be traced to the rate
 * that was in force at the time.
 */
class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Currencies & rates';

    protected static ?int $navigationSort = 7;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('settings.view') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('settings.edit') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Currency')->schema([
                Forms\Components\TextInput::make('code')->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('name')->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('symbol')->disabled()->dehydrated(false),
            ])->columns(3),

            Forms\Components\Section::make('Rate')
                ->description('Units of this currency per 1 XOF. Changing this immediately changes the prices customers see.')
                ->schema([
                    Forms\Components\TextInput::make('rate_to_base')
                        ->label('Rate to XOF')
                        ->numeric()
                        ->required()
                        ->step('0.00000001')
                        ->helperText(fn (Currency $record) => $record->is_base
                            ? 'The base currency always has a rate of 1.'
                            : null)
                        ->disabled(fn (?Currency $record) => (bool) $record?->is_base)
                        ->dehydrated(fn (?Currency $record) => ! $record?->is_base),
                    Forms\Components\Toggle::make('is_active')->label('Available to customers'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('is_base', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')->badge()->color('gray')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('symbol'),
                Tables\Columns\TextColumn::make('rate_to_base')
                    ->label('Rate to XOF')
                    ->numeric(decimalPlaces: 8),
                Tables\Columns\IconColumn::make('is_base')->boolean()->label('Base'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Live'),
                Tables\Columns\TextColumn::make('rate_updated_at')->since()->label('Updated'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refresh')
                    ->label('Refresh live rates')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn () => auth()->user()?->hasPermission('settings.edit') ?? false)
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $updated = Money::refreshFromApi();

                        if ($updated === []) {
                            Notification::make()
                                ->title('Could not reach the rate provider')
                                ->body('Existing rates were kept unchanged.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Rates updated')
                            ->body(implode(', ', array_keys($updated)))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencies::route('/'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
