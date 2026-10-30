<?php

namespace App\Models;

use App\Models\Scopes\StaffProfileAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\ValidationException;

class StaffProfile extends Model
{
    protected $fillable = [
        'branch_id',
        'department_id',
        'employee_number',
        'job_title',
        'date_of_birth',
        'address',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new StaffProfileAccessScope);

        static::saving(function (self $profile): void {
            if ($profile->department_id === null) {
                return;
            }

            $departmentMatchesBranch = Department::query()
                ->whereKey($profile->department_id)
                ->where('branch_id', $profile->branch_id)
                ->exists();

            if (! $departmentMatchesBranch) {
                throw ValidationException::withMessages([
                    'department_id' => 'The assigned department must belong to the assigned branch.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return ['date_of_birth' => 'date'];
    }

    public function profileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
