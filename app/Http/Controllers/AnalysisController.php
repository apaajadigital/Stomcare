<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    /**
     * Peta tipe gangguan (BernoulliNB) -> status aplikasi.
     * Model kini 4 kelas penyakit lambung; 'Normal' bukan lagi kelas model
     * melainkan hasil aturan di ai_predict.py saat tidak ada gejala sama sekali.
     */
    private const DISEASE_STATUS = [
        'Normal'                             => 'NORMAL',
        'Tidak terindikasi gangguan lambung' => 'NORMAL',
        // BELUM PASTI, bukan NORMAL: sistem sedang menyatakan "tidak tahu", bukan
        // "Anda sehat". Menandainya hijau NORMAL berisiko membuat pengguna merasa
        // aman padahal belum ada kesimpulan apa pun.
        'Tidak dapat mendiagnosis'           => 'BELUM PASTI',
        'GERD'                               => 'PERHATIAN',
        'Dispepsia'                          => 'PERHATIAN',
        'Gastritis'                          => 'EMERGENCY',
        'Tukak Lambung'                      => 'EMERGENCY',
    ];

    /** Kelas yang benar-benar dikeluarkan model (untuk tampilan probabilitas). */
    public const MODEL_CLASSES = ['GERD', 'Dispepsia', 'Gastritis', 'Tukak Lambung'];

    /** Fitur subjektif diskret: nama form (snake_case) => kunci model (CamelCase). */
    private const FEATURE_MAP = [
        'heartburn'         => 'Heartburn',
        'regurgitasi'       => 'Regurgitasi',
        'merokok'           => 'Merokok',
        'alkohol'           => 'Alkohol',
        'waktu_makan_tidur' => 'Waktu_Makan_Tidur',
        'nsaid'             => 'NSAID',
        'stres'             => 'Stres',
        'riwayat_keluarga'  => 'Riwayat_Keluarga',
        'kafein'            => 'Kafein',
        'makanan_pedas'     => 'Makanan_Pedas',
        'makanan_berlemak'  => 'Makanan_Berlemak',
        'posisi_tidur'      => 'Posisi_Tidur',
        'batuk_kronis'      => 'Batuk_Kronis',
        'aktivitas_fisik'   => 'Aktivitas_Fisik',
        'minuman_soda'      => 'Minuman_Soda',
        'kualitas_tidur'    => 'Kualitas_Tidur',
    ];

    /**
     * 20 gejala biner untuk model ASLAM (BernoulliNB).
     *
     * REVISI: dataset kini difokuskan HANYA pada penyakit lambung. Kelas 'Normal'
     * (yang dulu menampung 31 penyakit non-lambung) sudah dibuang, sehingga koreksi
     * 'Fungal infection' 15x tidak lagi diperlukan.
     *
     * Karena model tidak punya kelas 'Normal', ia akan selalu memaksakan salah satu
     * dari 4 kelas lambung. Penjagaan dilakukan di ai_predict.py lewat aturan
     * core_gastric_symptoms: bila tidak ada gejala inti lambung yang dicentang,
     * hasilnya "Tidak terindikasi gangguan lambung".
     */
    private const SYMPTOM_FEATURES = [
        'itching', 'stomach_pain', 'headache', 'chills', 'toxic_look_(typhos)',
        'belly_pain', 'internal_itching', 'passage_of_gases', 'indigestion',
        'fatigue', 'diarrhoea', 'ulcers_on_tongue', 'acidity', 'abdominal_pain',
        'irritation_in_anus', 'pain_in_anal_region', 'cough', 'high_fever',
        'bloody_stool', 'pain_during_bowel_movements',
    ];

    public function index()
    {
        return view('analysis.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usia'              => 'required|integer|min:1|max:120',
            'jenis_kelamin'     => 'required|boolean',
            'tinggi_badan'      => 'required|numeric|min:80|max:250',
            'berat_badan'       => 'required|numeric|min:20|max:300',
            // gejala & faktor risiko subjektif
            'heartburn'         => 'required|integer|min:0|max:3',
            'regurgitasi'       => 'required|integer|min:0|max:3',
            'merokok'           => 'required|integer|min:0|max:3',
            'alkohol'           => 'required|integer|min:0|max:3',
            'waktu_makan_tidur' => 'required|integer|min:0|max:3',
            'nsaid'             => 'required|integer|min:0|max:3',
            'stres'             => 'required|integer|min:0|max:3',
            'riwayat_keluarga'  => 'required|boolean',
            'kafein'            => 'required|integer|min:0|max:3',
            'makanan_pedas'     => 'required|integer|min:0|max:3',
            'makanan_berlemak'  => 'required|integer|min:0|max:3',
            'posisi_tidur'      => 'required|integer|min:0|max:2',
            'batuk_kronis'      => 'required|boolean',
            'aktivitas_fisik'   => 'required|integer|min:0|max:3',
            'minuman_soda'      => 'required|integer|min:0|max:3',
            'kualitas_tidur'    => 'required|integer|min:0|max:3',
            // gejala biner (model ASLAM) - opsional
            'symptoms'          => 'nullable|array',
            'symptoms.*'        => 'nullable|in:0,1',
        ]);

        // Hitung BMI dari tinggi (cm) & berat (kg)
        $tinggiM = $validated['tinggi_badan'] / 100;
        $bmi = $tinggiM > 0 ? round($validated['berat_badan'] / ($tinggiM * $tinggiM), 1) : 0;

        // Input MODEL = HANYA 20 gejala biner (fitur subjektif TIDAK dikirim ke model).
        $symptomsInput = $request->input('symptoms', []);
        $symptomFlags = [];
        foreach (self::SYMPTOM_FEATURES as $sym) {
            $symptomFlags[$sym] = !empty($symptomsInput[$sym]) ? 1 : 0;
        }
        $aiFeatures = $symptomFlags; // payload ke Python = gejala saja

        // Panggil engine AI (BernoulliNB tunggal). Dua mode:
        //  - Jika ML_API_URL diset (mis. Vercel Python Function) -> panggil via HTTP.
        //  - Jika tidak -> jalankan subprocess Python lokal (default, deploy container "utuh").
        $mlApiUrl = env('ML_API_URL');
        try {
            if (!empty($mlApiUrl)) {
                $response = Http::timeout(25)->acceptJson()->post($mlApiUrl, $aiFeatures);
                if (!$response->successful()) {
                    Log::error('AI API Failed', ['status' => $response->status(), 'body' => $response->body()]);
                    return back()->with('error', 'Gagal memanggil API AI (HTTP ' . $response->status() . ').')->withInput();
                }
                $output = $response->json();
            } else {
                $pythonPath = env('PYTHON_PATH', 'python');
                $pythonScript = base_path('model_web/ai_predict.py');
                $result = Process::input(json_encode($aiFeatures))
                    ->path(base_path())
                    ->env([
                        'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
                        'PATH'       => getenv('PATH'),
                    ])
                    ->run([$pythonPath, $pythonScript]);

                if (!$result->successful()) {
                    Log::error('AI Analysis Failed', [
                        'exit_code'    => $result->exitCode(),
                        'error_output' => $result->errorOutput(),
                        'output'       => $result->output(),
                    ]);
                    return back()->with('error', 'Gagal menjalankan analisa AI: ' . $result->errorOutput())->withInput();
                }
                $output = json_decode($result->output(), true);
            }
        } catch (\Exception $e) {
            Log::error('AI Process Exception', ['message' => $e->getMessage()]);
            return back()->with('error', 'Exception saat menjalankan AI: ' . $e->getMessage())->withInput();
        }

        // Guard: output tidak dapat diparse (mis. warning bocor ke stdout)
        if (!is_array($output) || !isset($output['status'])) {
            Log::error('AI Output tidak valid', ['raw' => $result->output()]);
            return back()->with('error', 'Output AI tidak valid. Periksa konfigurasi Python.')->withInput();
        }
        if ($output['status'] === 'error') {
            return back()->with('error', 'AI Error: ' . ($output['message'] ?? 'unknown'))->withInput();
        }

        // Output SINGLE model: tipe gangguan + probabilitas per-kelas
        $prediction    = $output['prediction'] ?? 'Tidak dapat mendiagnosis';
        $probabilities = $output['probabilities'] ?? [];

        // Status dari tipe gangguan
        $status = self::DISEASE_STATUS[$prediction] ?? 'NORMAL';
        $recommendation = $this->buildRecommendation($prediction, $status, $validated, $bmi);

        // Simpan record
        $analysis = new Analysis();
        $analysis->user_id          = Auth::id();
        $analysis->result_status    = $status;
        $analysis->recommendation   = $recommendation;
        $analysis->ai_prediction    = $prediction;          // tipe gangguan (BernoulliNB)
        $analysis->ai_probabilities = $probabilities;
        $analysis->symptom_prediction    = null;            // (mode single-model: tidak dipakai)
        $analysis->symptom_probabilities = null;

        $analysis->usia          = $validated['usia'];
        $analysis->jenis_kelamin = $validated['jenis_kelamin'];
        $analysis->tinggi_badan  = $validated['tinggi_badan'];
        $analysis->berat_badan   = $validated['berat_badan'];
        $analysis->bmi           = $bmi;
        foreach (self::FEATURE_MAP as $formKey => $modelKey) {
            $analysis->{$formKey} = $validated[$formKey];
        }
        // Simpan 20 gejala biner (untuk tampilan & jejak model ASLAM)
        $analysis->symptoms = $symptomFlags;

        $analysis->save();

        return redirect()->route('analysis.result', $analysis->id)
            ->with('success', 'Analisa AI berhasil diselesaikan.');
    }

    /**
     * Rekomendasi berbasis TIPE gangguan (hasil model) + tips gaya hidup.
     * Catatan: data subjektif ($v) hanya dipakai untuk saran gaya hidup (informasi),
     * BUKAN sebagai input model/prediksi.
     */
    private function buildRecommendation(string $prediction, string $status, array $v, float $bmi): string
    {
        if ($prediction === 'Tidak dapat mendiagnosis') {
            return 'Model belum dapat menyimpulkan kondisi Anda secara meyakinkan dari gejala yang dipilih. '
                . 'Lengkapi gejala yang dialami atau konsultasikan ke dokter untuk pemeriksaan klinis.';
        }

        // Gejala yang dipilih tidak mengarah ke lambung. Model ini hanya dilatih pada
        // 4 penyakit lambung, sehingga keluhan di luar itu berada di luar cakupannya.
        if ($prediction === 'Tidak terindikasi gangguan lambung') {
            return 'Gejala yang Anda pilih tidak mengarah pada gangguan lambung, sehingga berada di luar '
                . 'cakupan model ini. Hasil ini BUKAN berarti Anda sehat — keluhan Anda mungkin berkaitan '
                . 'dengan kondisi lain (misalnya alergi, infeksi, atau gangguan organ lain). '
                . 'Silakan konsultasikan ke dokter untuk pemeriksaan yang sesuai.';
        }

        // Kalimat pembuka per tipe gangguan
        $intro = match ($prediction) {
            'Normal'        => 'Hasil analisa AI: tidak ada gejala yang dilaporkan, sehingga tidak terindikasi gangguan lambung. Pertahankan pola hidup sehat.',
            'GERD'          => 'Hasil analisa AI: indikasi GERD (asam lambung naik ke kerongkongan). Perbaikan gaya hidup & pola makan sangat membantu.',
            'Dispepsia'     => 'Hasil analisa AI: indikasi Dispepsia (gangguan pencernaan/maag). Atur pola makan dan hindari faktor pemicu.',
            'Gastritis'     => 'Hasil analisa AI: indikasi Gastritis (peradangan lambung). Disarankan konsultasi dokter untuk penanganan yang tepat.',
            'Tukak Lambung' => 'Hasil analisa AI: indikasi Tukak Lambung. Segera periksa ke dokter untuk evaluasi lebih lanjut.',
            default         => 'Hasil analisa AI: ' . $prediction . '.',
        };

        // Tips gaya hidup dari data tambahan (INFORMASI - tidak memengaruhi prediksi)
        $tips = [];
        if ($bmi >= 25)                          $tips[] = 'jaga berat badan menuju IMT ideal (18,5–24,9)';
        if (($v['merokok'] ?? 0) >= 2)           $tips[] = 'kurangi atau hentikan kebiasaan merokok';
        if (($v['stres'] ?? 0) >= 2)             $tips[] = 'kelola stres (relaksasi, tidur cukup)';
        if (($v['makanan_pedas'] ?? 0) >= 2 || ($v['makanan_berlemak'] ?? 0) >= 2) $tips[] = 'kurangi makanan pedas/berlemak';
        if (($v['kafein'] ?? 0) >= 2 || ($v['minuman_soda'] ?? 0) >= 2)            $tips[] = 'batasi kafein dan minuman bersoda';
        if (($v['waktu_makan_tidur'] ?? 0) >= 2) $tips[] = 'beri jeda minimal 3 jam antara makan malam dan tidur';
        if (($v['alkohol'] ?? 0) >= 2)           $tips[] = 'hindari konsumsi alkohol';
        if (($v['aktivitas_fisik'] ?? 0) <= 1)   $tips[] = 'tingkatkan aktivitas fisik rutin';

        $rec = $intro;
        if (!empty($tips)) {
            $rec .= ' Saran gaya hidup untuk Anda: ' . implode('; ', $tips) . '.';
        }
        if ($status === 'EMERGENCY') {
            $rec .= ' Sebaiknya jangan menunda pemeriksaan medis profesional.';
        }
        $rec .= ' (Catatan: hasil ini pra-diagnosa AI, bukan diagnosis medis definitif.)';

        return $rec;
    }

    public function showResult($id)
    {
        $analysis = Analysis::where('user_id', Auth::id())->findOrFail($id);
        return view('analysis.result', compact('analysis'));
    }

    public function history(Request $request)
    {
        $query = Analysis::where('user_id', Auth::id());

        $filter = $request->get('filter', 'all');
        if ($filter === 'this_month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($filter === 'last_3_months') {
            $query->where('created_at', '>=', now()->subMonths(3));
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        // 'BELUM PASTI' sengaja dihitung terpisah agar tidak hilang dari rekap:
        // ia bukan kondisi stabil, tapi juga bukan temuan yang perlu perhatian medis.
        $stats = [
            'total'     => $records->count(),
            'stable'    => $records->where('result_status', 'NORMAL')->count(),
            'attention' => $records->whereIn('result_status', ['PERHATIAN', 'EMERGENCY'])->count(),
            'uncertain' => $records->where('result_status', 'BELUM PASTI')->count(),
        ];

        return view('analysis.history', compact('records', 'stats'));
    }

    public function dietGuide()
    {
        $latest = Analysis::where('user_id', Auth::id())->latest()->first();

        if (!$latest) {
            return redirect()->route('analysis.history')
                ->with('error', 'Silakan lakukan analisa terlebih dahulu untuk mendapatkan panduan diet.');
        }

        return view('analysis.diet_guide', compact('latest'));
    }
}
