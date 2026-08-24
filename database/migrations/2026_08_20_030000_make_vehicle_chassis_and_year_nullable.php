<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE vehicles MODIFY chassis_number VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vehicles MODIFY year_of_make SMALLINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE vehicles MODIFY chassis_number VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vehicles MODIFY year_of_make SMALLINT UNSIGNED NOT NULL');
    }
};
