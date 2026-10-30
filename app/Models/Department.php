<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    protected $fillable = ['branch_id', 'name', 'code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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

        if (! $profile) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('branch_manager') && $profile->branch_id) {
            return $query->where('branch_id', $profile->branch_id);
        }

        if ($user->hasRole('department_head') && $profile->department_id) {
            return $query->whereKey($profile->department_id);
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
