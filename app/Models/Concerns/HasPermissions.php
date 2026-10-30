<?php

namespace App\Models\Concerns;

use App\Support\Permissions;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

/**
 * Compatibility helpers backed by Spatie Laravel Permission.
 *
 * Administrators always hold every permission and cannot be locked out, even
 * if their role rows are accidentally cleared.
 */
trait HasPermissions
{
    public function effectivePermissions(): array
    {
        if ($this->isAdmin()) {
            return array_keys(Permissions::ALL);
        }

        return $this->getAllPermissions()
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }

    public function hasPermission(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        try {
            return $this->checkPermissionTo($key);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }
}
