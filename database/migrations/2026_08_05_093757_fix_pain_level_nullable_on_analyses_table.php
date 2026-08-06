<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaikan drift skema: migrasi pembuatan tabel (2026_05_07_031516) sudah
 * mendeklarasikan 'pain_level' sebagai nullable() (kolom legacy, tidak dipakai
 * model klinis), tapi kolom di database nyata masih NOT NULL tanpa default
 * (dibuat sebelum migrasi tsb diedit). Akibatnya setiap insert analisa baru
 * gagal dengan "Field 'pain_level' doesn't have a default value".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->integer('pain_level')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->integer('pain_level')->nullable(false)->change();
        });
    }
};
