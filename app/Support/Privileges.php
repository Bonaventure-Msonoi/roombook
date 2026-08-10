<?php

namespace App\Support;

class Privileges
{
    public const BOOK_BOARDROOM = 'book_boardroom';

    public const BOOK_VEHICLES = 'book_vehicles';

    public const VIEW_ADMIN_BOOKINGS = 'view_admin_bookings';

    public const CANCEL_ANY_BOOKING = 'cancel_any_booking';

    public const MANAGE_ANY_BOOKING = 'manage_any_booking';

    public const BOOK_ON_BEHALF = 'book_on_behalf';

    public const VIEW_AUDIT_LOG = 'view_audit_log';

    public const MANAGE_VEHICLES = 'manage_vehicles';

    public const MANAGE_USERS = 'manage_users';

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            self::BOOK_BOARDROOM => 'Access Boardroom booking (home button & calendar)',
            self::BOOK_VEHICLES => 'Access Pool Vehicle booking (home button & calendar)',
            self::VIEW_ADMIN_BOOKINGS => 'View all bookings (admin boards)',
            self::CANCEL_ANY_BOOKING => 'Cancel any user\'s booking',
            self::MANAGE_ANY_BOOKING => 'Reschedule or reassign any booking',
            self::BOOK_ON_BEHALF => 'Book rooms/vehicles on behalf of another user',
            self::VIEW_AUDIT_LOG => 'View the audit log',
            self::MANAGE_VEHICLES => 'Manage the vehicle pool',
            self::MANAGE_USERS => 'Create and manage users',
        ];
    }

    /**
     * Default privileges matching the previous hard-coded role behaviour.
     *
     * @return array<string, list<string>>
     */
    public static function defaults(): array
    {
        return [
            'user' => [
                self::BOOK_BOARDROOM,
                self::BOOK_VEHICLES,
            ],
            'admin' => [
                self::BOOK_BOARDROOM,
                self::BOOK_VEHICLES,
                self::VIEW_ADMIN_BOOKINGS,
                self::CANCEL_ANY_BOOKING,
                self::MANAGE_ANY_BOOKING,
                self::BOOK_ON_BEHALF,
                self::VIEW_AUDIT_LOG,
            ],
            'super_user' => array_keys(self::all()),
        ];
    }

    /**
     * Roles that can have their privileges edited by a super user.
     *
     * @return list<string>
     */
    public static function editableRoles(): array
    {
        return ['user', 'admin'];
    }

    /**
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        return [
            'user' => 'Staff',
            'admin' => 'Admin',
            'super_user' => 'Super User',
        ];
    }
}
