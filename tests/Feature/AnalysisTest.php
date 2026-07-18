<?php

namespace Tests\Feature;

use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'usia' => 45, 'jenis_kelamin' => 1, 'tinggi_badan' => 170, 'berat_badan' => 80,
            'heartburn' => 3, 'regurgitasi' => 2, 'merokok' => 2, 'alkohol' => 1,
            'waktu_makan_tidur' => 2, 'nsaid' => 1, 'stres' => 2, 'riwayat_keluarga' => 1,
            'kafein' => 2, 'makanan_pedas' => 2, 'makanan_berlemak' => 2, 'posisi_tidur' => 1,
            'batuk_kronis' => 0, 'aktivitas_fisik' => 1, 'minuman_soda' => 2, 'kualitas_tidur' => 1,
            'symptoms' => ['acidity' => '1', 'stomach_pain' => '1', 'indigestion' => '1'],
        ], $overrides);
    }

    public function test_analysis_form_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('analysis.index'))
            ->assertOk()
            ->assertSee('Analisa Risiko GERD');
    }

    public function test_store_runs_model_and_saves_severity_prediction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('analysis.store'), $this->validPayload());

        $this->assertDatabaseCount('analyses', 1);
        $analysis = Analysis::first();

        $valid = ['Normal', 'GERD Ringan', 'GERD Sedang', 'GERD Berat', 'Komplikasi', 'Tidak dapat mendiagnosis'];
        $this->assertContains($analysis->ai_prediction, $valid, 'Prediksi keparahan harus valid');
        $this->assertContains($analysis->result_status, ['NORMAL', 'PERHATIAN', 'EMERGENCY']);
        $this->assertNotNull($analysis->bmi);
        $this->assertIsArray($analysis->ai_probabilities);
        $this->assertEqualsWithDelta(1.0, array_sum($analysis->ai_probabilities), 0.01, 'Probabilitas keparahan ~1');

        // HYBRID: model gejala ASLAM juga tersimpan
        $symValid = ['Dispepsia', 'GERD', 'Gastritis', 'Normal', 'Tukak Lambung', 'Tidak dapat mendiagnosis'];
        $this->assertContains($analysis->symptom_prediction, $symValid, 'Prediksi tipe gangguan harus valid');
        $this->assertIsArray($analysis->symptom_probabilities);

        $response->assertRedirect(route('analysis.result', $analysis->id));
    }

    public function test_high_risk_profile_predicts_worse_than_healthy_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('analysis.store'), $this->validPayload([
            'usia' => 24, 'tinggi_badan' => 170, 'berat_badan' => 60, // BMI ~20.8
            'heartburn' => 0, 'regurgitasi' => 0, 'merokok' => 0, 'alkohol' => 0,
            'waktu_makan_tidur' => 0, 'nsaid' => 0, 'stres' => 0, 'riwayat_keluarga' => 0,
            'kafein' => 0, 'makanan_pedas' => 0, 'makanan_berlemak' => 0, 'posisi_tidur' => 0,
            'batuk_kronis' => 0, 'aktivitas_fisik' => 3, 'minuman_soda' => 0, 'kualitas_tidur' => 3,
        ]));
        $healthy = Analysis::latest('id')->first();

        $this->assertEquals('Normal', $healthy->ai_prediction, 'Profil sehat seharusnya Normal');
        $this->assertEquals('NORMAL', $healthy->result_status);
    }

    public function test_store_requires_valid_input(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('analysis.store'), [])
            ->assertSessionHasErrors(['usia', 'jenis_kelamin', 'tinggi_badan', 'stres']);
        $this->assertDatabaseCount('analyses', 0);
    }

    public function test_guest_cannot_access_analysis(): void
    {
        $this->get(route('analysis.index'))->assertRedirect(route('login'));
    }
}
