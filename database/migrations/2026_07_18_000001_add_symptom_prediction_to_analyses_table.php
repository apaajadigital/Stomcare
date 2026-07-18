<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mode HYBRID: menyimpan hasil model gejala biner (ASLAM BernoulliNB)
 * berdampingan dengan hasil model keparahan (Mixed NB) yang sudah ada
 * di kolom ai_prediction / ai_probabilities.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->string('symptom_prediction')->nullable()->after('ai_probabilities');
            $table->json('symptom_probabilities')->nullable()->after('symptom_prediction');
        });
    }

    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropColumn(['symptom_prediction', 'symptom_probabilities']);
        });
    }
};
