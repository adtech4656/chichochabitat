<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffProfileResource\Pages;
use App\Models\StaffProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffProfileResource extends Resource
{
    protected static ?string $model = StaffProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?string $navigationLabel = 'Staff directory';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', StaffProfile::class) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update', $record) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('employee_number')->maxLength(64)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('job_title')->maxLength(255),
            Forms\Components\DatePicker::make('date_of_birth')->native(false)->maxDate(now()->subYears(16)),
            Forms\Components\Textarea::make('address')->rows(3)->maxLength(2000)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('profileable.name')->label('Staff member')->searchable(),
                Tables\Columns\TextColumn::make('employee_number')->searchable(),
                Tables\Columns\TextColumn::make('job_title')->searchable(),
                Tables\Columns\TextColumn::make('branch.name')->label('Branch'),
                Tables\Columns\TextColumn::make('department.name')->label('Department'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (StaffProfile $record) => static::canEdit($record)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffProfiles::route('/'),
            'edit' => Pages\EditStaffProfile::route('/{record}/edit'),
        ];
    }
}
