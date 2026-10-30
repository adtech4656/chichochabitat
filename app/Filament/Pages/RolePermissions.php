<?php

namespace App\Filament\Pages;

use App\Support\Permissions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role → permission matrix.
 *
 * Visible to administrators only: this is the screen that decides what every
 * other role is allowed to do anywhere in the console.
 */
class RolePermissions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Roles & permissions';

    protected static ?string $title = 'Roles & permissions';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.role-permissions';

    /** @var array<string, array<int, string>> */
    public array $matrix = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        foreach (array_keys(Permissions::ROLE_PRESETS) as $role) {
            $stored = Role::query()
                ->where('name', $role)
                ->where('guard_name', 'web')
                ->first();

            $this->matrix[$role] = $stored
                ? $stored->permissions->pluck('name')->all()
                : Permissions::rolePreset($role);
        }
    }

    public function form(Form $form): Form
    {
        $sections = [];

        foreach (Permissions::ROLE_PRESETS as $role => $_) {
            $sections[] = Forms\Components\Section::make(Permissions::ROLES[$role] ?? $role)
                ->description('What this role can do across the console.')
                ->collapsible()
                ->schema([
                    Forms\Components\CheckboxList::make("matrix.{$role}")
                        ->hiddenLabel()
                        ->options(collect(Permissions::ALL)->map(fn ($meta) => $meta[1])->all())
                        ->descriptions(collect(Permissions::ALL)->map(fn ($meta) => $meta[0])->all())
                        ->columns(2)
                        ->gridDirection('row')
                        ->searchable()
                        ->bulkToggleable(),
                ]);
        }

        return $form->schema($sections);
    }

    public function save(): void
    {
        // Defence in depth: the page is already admin-gated, but re-assert it
        // because this action rewrites every role's authority.
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->form->getState()['matrix'] ?? [];

        DB::transaction(function () use ($data): void {
            foreach ($data as $role => $keys) {
                if (! array_key_exists($role, Permissions::ROLE_PRESETS)) {
                    continue;
                }

                $roleRecord = Role::findOrCreate($role, 'web');
                $validKeys = array_values(array_filter(
                    (array) $keys,
                    fn ($key) => is_string($key) && array_key_exists($key, Permissions::ALL),
                ));

                $roleRecord->syncPermissions($validKeys);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Notification::make()
            ->title('Permissions updated')
            ->body('Every role takes effect on the next request.')
            ->success()
            ->send();
    }
}
