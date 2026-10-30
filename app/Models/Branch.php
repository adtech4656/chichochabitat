<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->hasPermission('manage_all_departments')) {
            return $query;
        }

        $profile = $this->profileFor($user);

        if ($profile && $user->hasAnyRole(['branch_manager', 'department_head']) && $profile->branch_id) {
            return $query->whereKey($profile->branch_id);
        }

        return $query->whereRaw('1 = 0');
    }

    private function profileFor(User $user): ?object
    {
        return DB::table('staff_profiles')
            ->where('profileable_type', $user->getMorphClass())
            ->where('profileable_id', $user->getKey())
            ->first(['branch_id', 'department_id']);
    }
}
