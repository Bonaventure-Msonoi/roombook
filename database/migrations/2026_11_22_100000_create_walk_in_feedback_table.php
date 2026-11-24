<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walk_in_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->dateTime('visited_at');
            $table->unsignedTinyInteger('recommend_score');
            $table->text('rating_reason');
            $table->unsignedTinyInteger('satisfaction_level');
            $table->text('improvement_suggestions')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('visited_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walk_in_feedback');
    }
};