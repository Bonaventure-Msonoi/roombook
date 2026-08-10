<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_privileges', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('privilege');
            $table->timestamps();

            $table->unique(['role', 'privilege']);
            $table->index('role');
        });

        $defaults = \App\Support\Privileges::defaults();
        $now = now();

        foreach ($defaults as $role => $privileges) {
            foreach ($privileges as $privilege) {
                DB::table('role_privileges')->insert([
                    'role' => $role,
                    'privilege' => $privilege,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_privileges');
    }
};
