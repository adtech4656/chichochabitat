<?php

namespace App\Policies;

use App\Models\Scopes\StaffProfileAccessScope;
use App\Models\StaffProfile;
use App\Models\User;

class StaffProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('staff.view') || $user->hasPermission('staff.view.all');
    }

    public function view(User $user, StaffProfile $staffProfile): bool
    {
        return $this->viewAny($user) && StaffProfileAccessScope::allows($user, $staffProfile);
    }

    public function update(User $user, StaffProfile $staffProfile): bool
    {
        return $user->isAdmin()
            || ($user->hasPermission('staff.manage') && StaffProfileAccessScope::allows($user, $staffProfile));
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, StaffProfile $staffProfile): bool
    {
        return $user->isAdmin();
    }
}
