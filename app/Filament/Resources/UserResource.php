<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Support\Permissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users & access';

    protected static ?int $navigationSort = 3;

    /* ----------------------------- Authorisation ----------------------------- */

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('users.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('users.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('users.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $actor = auth()->user();

        if (! $actor instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->isAdmin()) {
            return $query;
        }

        if ($actor->hasPermission('manage_all_departments')) {
            return $query->whereHas('staffProfile');
        }

        if ($actor->hasRole('branch_manager')) {
            return $query->whereHas('staffProfile', fn (Builder $profileQuery) => $profileQuery
                ->where('branch_id', $actor->staffProfile?->branch_id ?? 0));
        }

        if ($actor->hasRole('department_head')) {
            return $query->whereHas('staffProfile', fn (Builder $profileQuery) => $profileQuery
                ->where('department_id', $actor->staffProfile?->department_id ?? 0));
        }

        return $query->whereKey($actor->getKey());
    }

    public static function synchronizeAdminFields(User $record, array $state): void
    {
        $actor = auth()->user();
        abort_unless($actor?->isAdmin(), 403, 'Only an administrator can assign roles or permissions.');

        $roleNames = array_values(array_unique(array_filter(
            (array) ($state['role_assignments'] ?? []),
            fn ($role) => is_string($role) && array_key_exists($role, Permissions::ROLES),
        )));

        if ($roleNames === []) {
            throw ValidationException::withMessages([
                'role_assignments' => 'Assign at least one role to this account.',
            ]);
        }

        $directPermissions = array_values(array_unique(array_filter(
            (array) ($state['permissions'] ?? []),
            fn ($permission) => is_string($permission) && array_key_exists($permission, Permissions::ALL),
        )));

        $branchId = filled($state['staff_branch_id'] ?? null) ? (int) $state['staff_branch_id'] : null;
        $departmentId = filled($state['staff_department_id'] ?? null) ? (int) $state['staff_department_id'] : null;
        $staffDetails = [
            'employee_number' => filled($state['employee_number'] ?? null) ? trim($state['employee_number']) : null,
            'job_title' => filled($state['job_title'] ?? null) ? trim($state['job_title']) : null,
            'date_of_birth' => $state['date_of_birth'] ?? null,
            'address' => filled($state['address'] ?? null) ? trim($state['address']) : null,
        ];
        $requiresBranch = array_intersect($roleNames, ['branch_manager', 'department_head']) !== [];
        $requiresDepartment = in_array('department_head', $roleNames, true);

        if (($requiresBranch && ! $branchId) || ($requiresDepartment && ! $departmentId)) {
            throw ValidationException::withMessages([
                'staff_branch_id' => 'Branch managers and department heads need an assigned branch.',
                'staff_department_id' => 'Department heads need an assigned department.',
            ]);
        }

        if ($branchId && ! Branch::query()->whereKey($branchId)->exists()) {
            throw ValidationException::withMessages([
                'staff_branch_id' => 'Select a valid branch.',
            ]);
        }

        if ($departmentId && ! Department::query()
            ->whereKey($departmentId)
            ->where('branch_id', $branchId)
            ->exists()) {
            throw ValidationException::withMessages([
                'staff_department_id' => 'The selected department must belong to the assigned branch.',
            ]);
        }

        DB::transaction(function () use ($record, $roleNames, $directPermissions, $branchId, $departmentId, $staffDetails): void {
            $record->syncRoles($roleNames);
            $record->syncPermissions($directPermissions);
            $record->forceFill([
                'role' => $roleNames[0],
                'legacy_permissions' => $directPermissions,
            ])->save();

            $profile = $record->staffProfile()->withoutGlobalScopes()->first();
            $hasStaffDetails = $branchId !== null
                || $departmentId !== null
                || collect($staffDetails)->contains(fn ($value) => filled($value));

            if ($profile || $hasStaffDetails) {
                $record->staffProfile()->withoutGlobalScopes()->updateOrCreate([], [
                    'branch_id' => $branchId,
                    'department_id' => $departmentId,
                    ...$staffDetails,
                ]);
            }
        });
    }

    /* --------------------------------- Form --------------------------------- */

    public static function form(Form $form): Form
    {
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        return $form->schema([
            Forms\Components\Section::make('Account')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->required(fn (Get $get): bool => array_intersect(
                        (array) $get('role_assignments'),
                        User::ADMIN_PANEL_ROLES,
                    ) !== [])
                    ->unique(ignoreRecord: true)
                    ->maxLength(64)
                    ->helperText('Used to sign in to the console.'),
                Forms\Components\TextInput::make('email')->email()->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->tel()->maxLength(32),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->rules([Password::min(8)->letters()->numbers()])
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText(fn (string $operation) => $operation === 'create'
                        ? 'At least 8 characters.'
                        : 'Leave blank to keep the current password.'),
            ])->columns(2),

            Forms\Components\Section::make('Roles & permissions')
                ->description($isAdmin
                    ? 'Roles are additive. Only an administrator can assign roles or direct permissions.'
                    : 'Only an administrator can assign roles or direct permissions.')
                ->schema([
                    Forms\Components\CheckboxList::make('role_assignments')
                        ->label('Roles')
                        ->options(Permissions::ROLES)
                        ->required()
                        ->minItems(1)
                        ->columns(2)
                        ->bulkToggleable()
                        ->disabled(! $isAdmin)
                        ->dehydrated($isAdmin),

                    Forms\Components\CheckboxList::make('permissions')
                        ->label('Direct permissions')
                        ->options(collect(Permissions::ALL)->map(fn ($meta) => $meta[1])->all())
                        ->columns(2)
                        ->searchable()
                        ->bulkToggleable()
                        ->disabled(! $isAdmin)
                        ->dehydrated($isAdmin)
                        ->helperText('These permissions are added to all assigned roles.'),
                ]),

            Forms\Components\Section::make('Staff assignment')
                ->description('Required for branch managers and department heads. These fields never grant roles or permissions.')
                ->visible($isAdmin)
                ->schema([
                    Forms\Components\Select::make('staff_branch_id')
                        ->label('Branch')
                        ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->required(fn (Get $get): bool => array_intersect(
                            (array) $get('role_assignments'),
                            ['branch_manager', 'department_head'],
                        ) !== []),
                    Forms\Components\Select::make('staff_department_id')
                        ->label('Department')
                        ->options(fn (Get $get) => filled($get('staff_branch_id'))
                            ? Department::query()
                                ->where('branch_id', $get('staff_branch_id'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                            : [])
                        ->searchable()
                        ->live()
                        ->required(fn (Get $get): bool => in_array('department_head', (array) $get('role_assignments'), true))
                        ->rules([
                            fn (Get $get) => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                if (filled($value) && ! Department::query()
                                    ->whereKey($value)
                                    ->where('branch_id', $get('staff_branch_id'))
                                    ->exists()) {
                                    $fail('The selected department must belong to the assigned branch.');
                                }
                            },
                        ]),
                    Forms\Components\TextInput::make('employee_number')
                        ->maxLength(64)
                        ->rules(fn (?User $record): array => [
                            Rule::unique('staff_profiles', 'employee_number')
                                ->ignore($record?->staffProfile()->withoutGlobalScopes()->value('id')),
                        ]),
                    Forms\Components\TextInput::make('job_title')->maxLength(255),
                    Forms\Components\DatePicker::make('date_of_birth')->native(false)->maxDate(now()->subYears(16)),
                    Forms\Components\Textarea::make('address')->rows(2)->maxLength(2000)->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Status')
                ->description('Suspended accounts cannot access the console or list properties.')
                ->visible($isAdmin)
                ->schema([
                    Forms\Components\Placeholder::make('status')
                        ->label('Account status')
                        ->content(fn (?User $record) => $record?->suspended_at
                            ? 'Suspended on '.$record->suspended_at->format('d M Y')
                            : 'Active'),
                ]),
        ]);
    }

    /* --------------------------------- Table -------------------------------- */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->description(fn (User $record) => $record->username ? '@'.$record->username : null),

                Tables\Columns\TextColumn::make('email')->searchable()->copyable(),

                Tables\Columns\TextColumn::make('staffProfile.branch.name')->label('Branch'),

                Tables\Columns\TextColumn::make('staffProfile.department.name')->label('Department'),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Permissions::ROLES[$state] ?? $state)
                    ->description(fn (User $record) => $record->getRoleNames()
                        ->map(fn (string $role) => Permissions::ROLES[$role] ?? $role)
                        ->implode(', '))
                    ->color(fn (string $state) => match ($state) {
                        'admin' => 'danger',
                        'agent' => 'success',
                        'customer' => 'gray',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('suspended_at')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Suspended' : 'Active')
                    ->color(fn ($state) => $state ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')->label('Joined')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(Permissions::ROLES)
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, string $role): Builder => $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery
                            ->where('name', $role)
                            ->where('guard_name', 'web')),
                    )),
                Tables\Filters\Filter::make('suspended')
                    ->query(fn ($query) => $query->whereNotNull('suspended_at'))
                    ->label('Suspended only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('suspend')
                    ->label(fn (User $record) => $record->suspended_at ? 'Reactivate' : 'Suspend')
                    ->icon(fn (User $record) => $record->suspended_at ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (User $record) => $record->suspended_at ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => (auth()->user()?->hasPermission('users.suspend') ?? false)
                        && $record->id !== auth()->id())
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'suspended_at' => $record->suspended_at ? null : now(),
                        ])->save();

                        Notification::make()
                            ->title($record->suspended_at ? 'Account suspended' : 'Account reactivated')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
