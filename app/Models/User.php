<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasPermissions;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Mass-assignment hardening.
 *
 * `role` and `permissions` are intentionally NOT fillable. They are privilege
 * escalation primitives: if they were mass-assignable, any future
 * `update($request->all())` — or a crafted registration payload — would grant
 * administrator rights. They may only be set explicitly via forceFill() from
 * admin-only, permission-checked code paths.
 */
#[Fillable(['name', 'username', 'email', 'phone', 'country_code', 'locale', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPermissions, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    protected static function booted(): void
    {
        static::deleting(function (self $user): void {
            $user->staffProfile()->withoutGlobalScopes()->delete();
        });
    }

    /**
     * Roles permitted into the administrative control zone.
     *
     * Agents are deliberately excluded: they use /agent. Allowing them into
     * /admin previously let an agent reach the verification queue and approve
     * their own listing, defeating the entire review model.
     */
    public const ADMIN_PANEL_ROLES = [
        'sales_manager',
        'technical_manager',
        'legal',
        'general_manager',
        'branch_manager',
        'department_head',
        'admin',
    ];

    /** Roles permitted into the agent workspace. */
    public const AGENT_PANEL_ROLES = ['agent', 'admin'];

    public function canAccessPanel(Panel $panel): bool
    {
        // A suspended account loses access to every panel immediately.
        if ($this->suspended_at !== null) {
            return false;
        }

        return match ($panel->getId()) {
            'agent' => $this->hasPanelRole(self::AGENT_PANEL_ROLES),
            'admin' => $this->hasPanelRole(self::ADMIN_PANEL_ROLES),
            default => false,
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function hasPanelRole(array $roles): bool
    {
        if (in_array($this->role, $roles, true)) {
            return true;
        }

        return $this->hasAnyRole($roles);
    }

    public function staffProfile(): MorphOne
    {
        return $this->morphOne(StaffProfile::class, 'profileable');
    }

    public function savedProperties(): HasMany
    {
        return $this->hasMany(SavedProperty::class);
    }

    public function savedListings(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'saved_properties')->withTimestamps();
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];

        return collect($parts)->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'GH';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'legacy_permissions' => 'array',
            'password' => 'hashed',
        ];
    }
}
