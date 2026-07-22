<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('number_plate')->unique();
            $table->string('chassis_number')->unique();
            $table->string('engine_capacity');
            $table->string('make');
            $table->unsignedSmallInteger('year_of_make');
            $table->string('category');
            $table->string('color', 7)->default('#6366f1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
