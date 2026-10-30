<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach (Permissions::ALL as $key => [$group, $label]) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                [
                    'label' => $label,
                    'group' => $group,
                    'sort' => $sort++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            Permission::findOrCreate($key, 'web');
        }

        $allPermissionModels = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_keys(Permissions::ALL))
            ->get();

        foreach (Permissions::ROLES as $roleName => $_label) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($roleName === 'admin') {
                $role->syncPermissions($allPermissionModels);

                continue;
            }

            if ($role->wasRecentlyCreated) {
                $legacyKeys = RolePermission::query()
                    ->where('role', $roleName)
                    ->pluck('permission_key')
                    ->filter(fn ($key) => is_string($key) && array_key_exists($key, Permissions::ALL))
                    ->values()
                    ->all();

                $keys = $legacyKeys !== []
                    ? $legacyKeys
                    : Permissions::rolePreset($roleName);

                $role->syncPermissions($keys);
            }
        }

        // The bootstrap administrator. Credentials are intentionally simple for
        // the first sign-in; the profile screen allows changing them later.
        $admin = User::query()
            ->where('username', 'admin')
            ->orWhere('email', 'admin@chichochabitat.ci')
            ->first();

        $attributes = [
            'name' => 'System Administrator',
            'username' => 'admin',
            'email' => 'admin@chichochabitat.ci',
            'phone' => '+225 07 00 00 00',
            'role' => 'admin',
            'email_verified_at' => now(),
            'suspended_at' => null,
        ];

        if ($admin) {
            $admin->forceFill($attributes)->save();
        } else {
            $admin = new User;
            $admin->forceFill($attributes + ['password' => Hash::make('admin1234')])->save();
        }

        foreach (User::query()->get() as $user) {
            if (array_key_exists($user->role, Permissions::ROLES) && ! $user->hasRole($user->role)) {
                $user->assignRole($user->role);
            }

            $legacyPermissions = array_values(array_filter(
                (array) ($user->legacy_permissions ?? []),
                fn ($key) => is_string($key) && array_key_exists($key, Permissions::ALL),
            ));

            if ($legacyPermissions !== [] && $user->getDirectPermissions()->isEmpty()) {
                $user->syncPermissions($legacyPermissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Access control seeded: '.count(Permissions::ALL).' permissions, '.count(Permissions::ROLE_PRESETS).' role presets, admin login ready.');
    }
}
