<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RolePrivilege extends Model
{
    protected $fillable = [
        'role',
        'privilege',
    ];

    /**
     * @return list<string>
     */
    public static function forRole(string $role): array
    {
        return Cache::remember("role_privileges:{$role}", 60, function () use ($role) {
            return static::query()
                ->where('role', $role)
                ->orderBy('privilege')
                ->pluck('privilege')
                ->all();
        });
    }

    public static function roleHas(string $role, string $privilege): bool
    {
        return in_array($privilege, static::forRole($role), true);
    }

    public static function clearCache(?string $role = null): void
    {
        if ($role) {
            Cache::forget("role_privileges:{$role}");

            return;
        }

        foreach (array_keys(\App\Support\Privileges::roleLabels()) as $knownRole) {
            Cache::forget("role_privileges:{$knownRole}");
        }
    }
}
