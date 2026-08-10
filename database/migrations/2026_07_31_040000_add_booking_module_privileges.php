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
            'user' => [
                Privileges::BOOK_BOARDROOM,
                Privileges::BOOK_VEHICLES,
            ],
            'admin' => [
                Privileges::BOOK_BOARDROOM,
                Privileges::BOOK_VEHICLES,
            ],
            'super_user' => [
                Privileges::BOOK_BOARDROOM,
                Privileges::BOOK_VEHICLES,
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
                Privileges::BOOK_BOARDROOM,
                Privileges::BOOK_VEHICLES,
            ])
            ->delete();
    }
};
