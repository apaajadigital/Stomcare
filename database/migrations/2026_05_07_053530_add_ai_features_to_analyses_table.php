<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom fitur subjektif/klinis (revisi: model Mixed Naive Bayes
 * keparahan GERD) beserta hasil prediksi AI ke tabel analyses.
 * Catatan: usia ditambahkan di sini agar migrasi berikutnya (add_height_weight)
 * yang memakai ->after('usia') dapat teresolusi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            // Data diri
            $table->integer('usia')->nullable()->after('user_id');
            $table->boolean('jenis_kelamin')->nullable()->after('usia'); // 0=Perempuan, 1=Laki-laki
            $table->float('bmi')->nullable();

            // Gejala & faktor risiko (ordinal 0-3 kecuali disebut)
            $table->tinyInteger('heartburn')->nullable();
            $table->tinyInteger('regurgitasi')->nullable();
            $table->tinyInteger('merokok')->nullable();
            $table->tinyInteger('alkohol')->nullable();
            $table->tinyInteger('waktu_makan_tidur')->nullable();
            $table->tinyInteger('nsaid')->nullable();
            $table->tinyInteger('stres')->nullable();
            $table->boolean('riwayat_keluarga')->nullable(); // 0/1
            $table->tinyInteger('kafein')->nullable();
            $table->tinyInteger('makanan_pedas')->nullable();
            $table->tinyInteger('makanan_berlemak')->nullable();
            $table->tinyInteger('posisi_tidur')->nullable();  // 0-2
            $table->boolean('batuk_kronis')->nullable();      // 0/1
            $table->tinyInteger('aktivitas_fisik')->nullable();
            $table->tinyInteger('minuman_soda')->nullable();
            $table->tinyInteger('kualitas_tidur')->nullable();

            // Fitur klinis Tier-2 (opsional, tidak dipakai model deploy Tier-1)
            $table->float('ph_esofagus')->nullable();
            $table->float('demeester_score')->nullable();
            $table->boolean('hernia_hiatal')->nullable();
            $table->float('tekanan_les')->nullable();
            $table->tinyInteger('grade_esofagitis')->nullable(); // 0-4
            $table->boolean('h_pylori')->nullable();
            $table->float('kadar_gastrin')->nullable();

            // Hasil prediksi AI
            $table->string('ai_prediction')->nullable();
            $table->json('ai_probabilities')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropColumn([
                'usia', 'jenis_kelamin', 'bmi',
                'heartburn', 'regurgitasi', 'merokok', 'alkohol', 'waktu_makan_tidur',
                'nsaid', 'stres', 'riwayat_keluarga', 'kafein', 'makanan_pedas',
                'makanan_berlemak', 'posisi_tidur', 'batuk_kronis', 'aktivitas_fisik',
                'minuman_soda', 'kualitas_tidur',
                'ph_esofagus', 'demeester_score', 'hernia_hiatal', 'tekanan_les',
                'grade_esofagitis', 'h_pylori', 'kadar_gastrin',
                'ai_prediction', 'ai_probabilities',
            ]);
        });
    }
};
