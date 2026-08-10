<?php

use App\Support\Privileges;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $grants = [
            'admin' => [
                Privileges::MANAGE_ANY_BOOKING,
                Privileges::BOOK_ON_BEHALF,
            ],
            'super_user' => [
                Privileges::MANAGE_ANY_BOOKING,
                Privileges::BOOK_ON_BEHALF,
            ],
        ];

        foreach ($grants as $role => $privileges) {
            foreach ($privileges as $privilege) {
                $exists = DB::table('role_privileges')
                    ->where('role', $role)
                    ->where('privilege', $privilege)
                    ->exists();

                if (! $exists) {
                    DB::table('role_privileges')->insert([
                        'role' => $role,
                        'privilege' => $privilege,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('role_privileges')
            ->whereIn('privilege', [
                Privileges::MANAGE_ANY_BOOKING,
                Privileges::BOOK_ON_BEHALF,
            ])
            ->delete();
    }
};
