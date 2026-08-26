<?php

namespace App\Models;

use App\Support\Privileges;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPER_USER = 'super_user';

    public const DEPARTMENTS = [
        'HR',
        'IT & Digital Transformation',
        'Wealth Solutions',
        'Finance Dept',
        'Investments',
        'Legal & Administration',
        'Operations Dept',
        'Business Distribution',
    ];

    protected $fillable = [
        'name',
        'email',
        'department',
        'password',
        'role',
        'is_active',
        'approved_at',
        'rejected_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at')->whereNull('rejected_at');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeSuperUsers($query)
    {
        return $query->where('role', self::ROLE_SUPER_USER);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isPendingApproval(): bool
    {
        return $this->approved_at === null && $this->rejected_at === null;
    }

    public function isRejected(): bool
    {
        return $this->rejected_at !== null;
    }

    public function accountStatusLabel(): string
    {
        if ($this->isRejected()) {
            return 'Rejected';
        }

        if ($this->isPendingApproval()) {
            return 'Pending approval';
        }

        return $this->isActive() ? 'Active' : 'Deactivated';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function vehicleBookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class);
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_USER], true);
    }

    public function isSuperUser(): bool
    {
        return $this->role === self::ROLE_SUPER_USER;
    }

    public function hasPrivilege(string $privilege): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        return RolePrivilege::roleHas($this->role, $privilege);
    }

    public function canBookBoardroom(): bool
    {
        return $this->hasPrivilege(Privileges::BOOK_BOARDROOM);
    }

    public function canBookVehicles(): bool
    {
        return $this->hasPrivilege(Privileges::BOOK_VEHICLES);
    }

    public function canAccessAdminDashboard(): bool
    {
        return $this->hasPrivilege(Privileges::VIEW_ADMIN_BOOKINGS);
    }

    public function canViewAnalytics(): bool
    {
        return $this->hasPrivilege(Privileges::VIEW_ADMIN_BOOKINGS);
    }

    public function canManageVehicles(): bool
    {
        return $this->hasPrivilege(Privileges::MANAGE_VEHICLES);
    }

    public function canManageUsers(): bool
    {
        return $this->hasPrivilege(Privileges::MANAGE_USERS);
    }

    public function canManageRolePrivileges(): bool
    {
        return $this->isSuperUser();
    }

    public function canCancelAnyBooking(): bool
    {
        return $this->hasPrivilege(Privileges::CANCEL_ANY_BOOKING);
    }

    public function canManageAnyBooking(): bool
    {
        return $this->hasPrivilege(Privileges::MANAGE_ANY_BOOKING);
    }

    public function canBookOnBehalf(): bool
    {
        return $this->hasPrivilege(Privileges::BOOK_ON_BEHALF);
    }

    public function canViewAuditLog(): bool
    {
        return $this->hasPrivilege(Privileges::VIEW_AUDIT_LOG);
    }
}
