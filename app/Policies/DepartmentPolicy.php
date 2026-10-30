<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('departments.view') || $user->hasPermission('departments.manage');
    }

    public function view(User $user, Department $department): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin() || $user->hasPermission('manage_all_departments')) {
            return true;
        }

        $profile = $this->profileFor($user);

        if (! $profile) {
            return false;
        }

        if ($user->hasRole('branch_manager')) {
            return (int) $profile->branch_id === $department->branch_id;
        }

        return $user->hasRole('department_head')
            && (int) $profile->department_id === $department->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin()
            || ($user->hasPermission('departments.manage')
                && ($user->hasPermission('manage_all_departments') || $user->hasRole('branch_manager')));
    }

    public function update(User $user, Department $department): bool
    {
        if ($user->isAdmin() || $user->hasPermission('manage_all_departments')) {
            return $user->hasPermission('departments.manage');
        }

        $profile = $this->profileFor($user);

        return $user->hasPermission('departments.manage')
            && $user->hasRole('branch_manager')
            && $profile !== null
            && (int) $profile->branch_id === $department->branch_id;
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->isAdmin();
    }

    private function profileFor(User $user): ?object
    {
        return DB::table('staff_profiles')
            ->where('profileable_type', $user->getMorphClass())
            ->where('profileable_id', $user->getKey())
            ->first(['branch_id', 'department_id']);
    }
}
