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
    /** Peta kelas keparahan GERD -> status severity aplikasi. */
    private const SEVERITY_STATUS = [
        'Normal'      => 'NORMAL',
        'GERD Ringan' => 'PERHATIAN',
        'GERD Sedang' => 'PERHATIAN',
        'GERD Berat'  => 'EMERGENCY',
        'Komplikasi'  => 'EMERGENCY',
    ];

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

    /** 20 gejala biner untuk model ASLAM (BernoulliNB). */
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

        // Susun input untuk model (kunci CamelCase sesuai metadata.json)
        $aiFeatures = [
            'Usia'          => (int) $validated['usia'],
            'BMI'           => $bmi,
            'Jenis_Kelamin' => (int) $validated['jenis_kelamin'],
        ];
        foreach (self::FEATURE_MAP as $formKey => $modelKey) {
            $aiFeatures[$modelKey] = (int) $validated[$formKey];
        }

        // Gejala biner (model ASLAM) - digabung ke input yang sama
        $symptomsInput = $request->input('symptoms', []);
        $symptomFlags = [];
        foreach (self::SYMPTOM_FEATURES as $sym) {
            $symptomFlags[$sym] = !empty($symptomsInput[$sym]) ? 1 : 0;
        }
        $aiFeatures = array_merge($aiFeatures, $symptomFlags);

        // Panggil engine AI HYBRID. Dua mode:
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

        // Output HYBRID: dua model berdampingan
        $severity = $output['severity'] ?? [];
        $symptom  = $output['symptom'] ?? [];
        $prediction    = $severity['prediction'] ?? 'Tidak dapat mendiagnosis';  // keparahan GERD
        $probabilities = $severity['probabilities'] ?? [];
        $symptomPred   = $symptom['prediction'] ?? 'Tidak dapat mendiagnosis';    // tipe gangguan (ASLAM)
        $symptomProbs  = $symptom['probabilities'] ?? [];

        // Status keseluruhan diambil dari tingkat keparahan
        $status = self::SEVERITY_STATUS[$prediction] ?? 'NORMAL';
        $recommendation = $this->buildRecommendation($prediction, $symptomPred, $status, $aiFeatures, $bmi);

        // Simpan record
        $analysis = new Analysis();
        $analysis->user_id          = Auth::id();
        $analysis->result_status    = $status;
        $analysis->recommendation   = $recommendation;
        $analysis->ai_prediction    = $prediction;          // keparahan GERD (Mixed NB)
        $analysis->ai_probabilities = $probabilities;
        $analysis->symptom_prediction    = $symptomPred;    // tipe gangguan (ASLAM BernoulliNB)
        $analysis->symptom_probabilities = $symptomProbs;

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

    /** Bangun rekomendasi berbasis tingkat keparahan + tipe gangguan + personalisasi gaya hidup. */
    private function buildRecommendation(string $prediction, string $symptomPred, string $status, array $f, float $bmi): string
    {
        if ($prediction === 'Tidak dapat mendiagnosis') {
            return 'Model belum dapat menyimpulkan kondisi Anda secara meyakinkan. '
                . 'Lengkapi data gejala/gaya hidup atau konsultasikan ke dokter untuk pemeriksaan klinis.';
        }

        // Kalimat pembuka per keparahan
        $intro = match ($prediction) {
            'Normal'      => 'Hasil analisa AI: tidak terindikasi GERD (Normal). Pertahankan pola hidup sehat.',
            'GERD Ringan' => 'Hasil analisa AI: indikasi GERD Ringan. Perbaikan gaya hidup umumnya cukup efektif.',
            'GERD Sedang' => 'Hasil analisa AI: indikasi GERD Sedang. Perbaikan gaya hidup diperlukan dan pertimbangkan konsultasi dokter.',
            'GERD Berat'  => 'Hasil analisa AI: indikasi GERD Berat. Disarankan segera konsultasi ke dokter spesialis.',
            'Komplikasi'  => 'Hasil analisa AI: indikasi Komplikasi GERD. Segera periksa ke dokter spesialis gastroenterologi.',
            default       => 'Hasil analisa AI: ' . $prediction . '.',
        };

        // Tips personalisasi berdasarkan input gaya hidup
        $tips = [];
        if ($bmi >= 25)               $tips[] = 'turunkan berat badan menuju IMT ideal (18,5–24,9)';
        if (($f['Merokok'] ?? 0) >= 2)          $tips[] = 'hentikan atau kurangi kebiasaan merokok';
        if (($f['Stres'] ?? 0) >= 2)            $tips[] = 'kelola stres (relaksasi, tidur cukup)';
        if (($f['Makanan_Pedas'] ?? 0) >= 2 || ($f['Makanan_Berlemak'] ?? 0) >= 2) $tips[] = 'kurangi makanan pedas/berlemak';
        if (($f['Kafein'] ?? 0) >= 2 || ($f['Minuman_Soda'] ?? 0) >= 2)            $tips[] = 'batasi kafein dan minuman bersoda';
        if (($f['Waktu_Makan_Tidur'] ?? 0) >= 2) $tips[] = 'beri jeda minimal 3 jam antara makan malam dan tidur';
        if (($f['Alkohol'] ?? 0) >= 2)          $tips[] = 'hindari konsumsi alkohol';
        if (($f['Aktivitas_Fisik'] ?? 0) <= 1)  $tips[] = 'tingkatkan aktivitas fisik rutin';

        $rec = $intro;
        if (!in_array($symptomPred, ['Normal', 'Tidak dapat mendiagnosis'], true)) {
            $rec .= ' Berdasarkan pola gejala yang dicentang, model juga mengindikasikan kemungkinan tipe gangguan: ' . $symptomPred . '.';
        }
        if (!empty($tips)) {
            $rec .= ' Saran khusus untuk Anda: ' . implode('; ', $tips) . '.';
        }
        if ($status === 'EMERGENCY') {
            $rec .= ' Jangan menunda pemeriksaan medis profesional.';
        }
        $rec .= ' (Catatan: hasil ini bukan diagnosis medis definitif.)';

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

        $stats = [
            'total'     => $records->count(),
            'stable'    => $records->where('result_status', 'NORMAL')->count(),
            'attention' => $records->whereIn('result_status', ['PERHATIAN', 'EMERGENCY'])->count(),
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
