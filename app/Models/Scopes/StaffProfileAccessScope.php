<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffProfileAccessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            $builder->whereRaw('1 = 0');

            return;
        }

        if ($user->isAdmin() || $user->hasPermission('manage_all_departments') || $user->hasPermission('staff.view.all')) {
            return;
        }

        if (! $user->hasPermission('staff.view') && ! $user->hasPermission('staff.manage')) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $profile = DB::table('staff_profiles')
            ->where('profileable_type', $user->getMorphClass())
            ->where('profileable_id', $user->getKey())
            ->first(['branch_id', 'department_id']);

        if ($profile && $user->hasRole('branch_manager') && $profile->branch_id) {
            $builder->where($model->qualifyColumn('branch_id'), $profile->branch_id);

            return;
        }

        if ($profile && $user->hasRole('department_head') && $profile->department_id) {
            $builder->where($model->qualifyColumn('department_id'), $profile->department_id);

            return;
        }

        $builder->whereRaw('1 = 0');
    }

    public static function allows(User $user, Model $record): bool
    {
        if ($user->isAdmin() || $user->hasPermission('manage_all_departments') || $user->hasPermission('staff.view.all')) {
            return true;
        }

        if (! $user->hasPermission('staff.view') && ! $user->hasPermission('staff.manage')) {
            return false;
        }

        $profile = DB::table('staff_profiles')
            ->where('profileable_type', $user->getMorphClass())
            ->where('profileable_id', $user->getKey())
            ->first(['branch_id', 'department_id']);

        if (! $profile) {
            return false;
        }

        if ($user->hasRole('branch_manager')) {
            return $profile->branch_id !== null && (int) $record->branch_id === (int) $profile->branch_id;
        }

        return $user->hasRole('department_head')
            && $profile->department_id !== null
            && (int) $record->department_id === (int) $profile->department_id;
    }
}
