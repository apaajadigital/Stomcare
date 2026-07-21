<?php

namespace Tests\Feature;

use App\Models\Analysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisTest extends TestCase
{
    use RefreshDatabase;

    private const DISEASE_CLASSES = ['Dispepsia', 'GERD', 'Gastritis', 'Normal', 'Tukak Lambung', 'Tidak dapat mendiagnosis'];

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'usia' => 45, 'jenis_kelamin' => 1, 'tinggi_badan' => 170, 'berat_badan' => 80,
            'heartburn' => 3, 'regurgitasi' => 2, 'merokok' => 2, 'alkohol' => 1,
            'waktu_makan_tidur' => 2, 'nsaid' => 1, 'stres' => 2, 'riwayat_keluarga' => 1,
            'kafein' => 2, 'makanan_pedas' => 2, 'makanan_berlemak' => 2, 'posisi_tidur' => 1,
            'batuk_kronis' => 0, 'aktivitas_fisik' => 1, 'minuman_soda' => 2, 'kualitas_tidur' => 1,
            // gejala biner -> input model BernoulliNB
            'symptoms' => ['acidity' => '1', 'stomach_pain' => '1', 'indigestion' => '1'],
        ], $overrides);
    }

    public function test_analysis_form_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('analysis.index'))
            ->assertOk()
            ->assertSee('Gejala yang Dialami');
    }

    public function test_store_runs_single_model_and_saves_disease_prediction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('analysis.store'), $this->validPayload());

        $this->assertDatabaseCount('analyses', 1);
        $analysis = Analysis::first();

        // Prediksi = tipe gangguan (model tunggal BernoulliNB)
        $this->assertContains($analysis->ai_prediction, self::DISEASE_CLASSES, 'Prediksi tipe gangguan harus valid');
        $this->assertContains($analysis->result_status, ['NORMAL', 'PERHATIAN', 'EMERGENCY']);
        $this->assertIsArray($analysis->ai_probabilities);
        $this->assertCount(5, $analysis->ai_probabilities, 'Harus ada 5 kelas');
        $this->assertEqualsWithDelta(1.0, array_sum($analysis->ai_probabilities), 0.02, 'Probabilitas ~1');

        // Fitur subjektif DISIMPAN (informasi), tapi TIDAK dipakai model
        $this->assertNotNull($analysis->bmi);
        $this->assertEquals(2, $analysis->stres);
        // Mode single-model: kolom hybrid tidak dipakai
        $this->assertNull($analysis->symptom_prediction);

        $response->assertRedirect(route('analysis.result', $analysis->id));
    }

    public function test_no_symptoms_predicts_normal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('analysis.store'), $this->validPayload([
            'symptoms' => [], // tanpa gejala -> Normal (perilaku model)
        ]));
        $a = Analysis::latest('id')->first();

        $this->assertEquals('Normal', $a->ai_prediction, 'Tanpa gejala seharusnya Normal');
        $this->assertEquals('NORMAL', $a->result_status);
    }

    public function test_gerd_symptoms_are_not_normal(): void
    {
        $user = User::factory()->create();
        // acidity + stomach_pain + indigestion -> harus terdeteksi (bukan Normal)
        $this->actingAs($user)->post(route('analysis.store'), $this->validPayload());
        $a = Analysis::latest('id')->first();
        $this->assertNotEquals('Normal', $a->ai_prediction, 'Dengan gejala GERD, hasil tidak boleh Normal');
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
