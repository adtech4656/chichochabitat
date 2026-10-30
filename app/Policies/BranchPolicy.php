<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('branches.view') || $user->hasPermission('branches.manage');
    }

    public function view(User $user, Branch $branch): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin() || $user->hasPermission('manage_all_departments')) {
            return true;
        }

        $profile = $this->profileFor($user);

        return $profile !== null
            && $user->hasAnyRole(['branch_manager', 'department_head'])
            && (int) $profile->branch_id === $branch->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('branches.manage');
    }

    public function update(User $user, Branch $branch): bool
    {
        return ($user->isAdmin() || $user->hasPermission('branches.manage'))
            && ($user->isAdmin() || $user->hasPermission('manage_all_departments'));
    }

    public function delete(User $user, Branch $branch): bool
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
