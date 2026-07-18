<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('pain_level')->nullable(); // legacy, tidak dipakai model klinis
            $table->json('symptoms')->nullable();
            $table->text('last_meal')->nullable();
            $table->time('last_meal_time')->nullable();
            $table->string('water_intake')->nullable();
            $table->string('result_status')->default('NORMAL'); // NORMAL, PERHATIAN, EMERGENCY
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
