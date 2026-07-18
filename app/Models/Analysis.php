<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    protected $fillable = [
        'user_id',
        'usia',
        'tinggi_badan',
        'berat_badan',
        'jenis_kelamin',
        'heartburn',
        'regurgitasi',
        'merokok',
        'alkohol',
        'waktu_makan_tidur',
        'nsaid',
        'stres',
        'riwayat_keluarga',
        'kafein',
        'makanan_pedas',
        'makanan_berlemak',
        'posisi_tidur',
        'batuk_kronis',
        'aktivitas_fisik',
        'minuman_soda',
        'kualitas_tidur',
        'bmi',
        'ph_esofagus',
        'demeester_score',
        'hernia_hiatal',
        'tekanan_les',
        'grade_esofagitis',
        'h_pylori',
        'kadar_gastrin',
        'result_status',
        'recommendation',
        'ai_prediction',
        'ai_probabilities',
        'symptom_prediction',
        'symptom_probabilities',
        'symptoms',
    ];

    protected $casts = [
        'symptoms' => 'array',
        'ai_probabilities' => 'array',
        'symptom_probabilities' => 'array',
        'riwayat_keluarga' => 'boolean',
        'batuk_kronis' => 'boolean',
        'hernia_hiatal' => 'boolean',
        'h_pylori' => 'boolean',
        'jenis_kelamin' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
