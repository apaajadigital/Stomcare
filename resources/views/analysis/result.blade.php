@extends('layouts.app')

@section('title', 'Hasil Analisa AI - StomaCare')

@section('content')
<main class="mt-32 mb-section-gap max-w-container-max mx-auto px-gutter">
    <!-- Result Header -->
    <div class="mb-12 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-label-sm mb-4">
            <span class="material-symbols-outlined text-[18px]">verified_user</span>
            Analisa AI Selesai
        </div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Laporan Perkiraan Tipe Gangguan</h1>
        <p class="text-on-surface-variant font-body-lg max-w-2xl mx-auto">
            Hasil dari model <strong>Naive Bayes (BernoulliNB)</strong> berdasarkan <strong>gejala yang Anda alami</strong>.
        </p>
    </div>

    @php
        // Urutan tampilan kelas tipe gangguan.
        // Model kini 4 kelas penyakit lambung ('Normal' bukan lagi kelas model).
        // Diambil dari kunci probabilitas agar otomatis mengikuti model yang terpasang.
        $defaultOrder = ['GERD', 'Dispepsia', 'Gastritis', 'Tukak Lambung'];
        $probs = is_array($analysis->ai_probabilities) ? $analysis->ai_probabilities : (json_decode($analysis->ai_probabilities ?? '{}', true) ?: []);

        // Tampilkan kelas sesuai probabilitas yang tersimpan. Analisa lama (5 kelas,
        // termasuk 'Normal') tetap tampil utuh; analisa baru tampil 4 kelas.
        $diseaseOrder = !empty($probs)
            ? array_values(array_unique(array_merge(
                array_values(array_intersect($defaultOrder, array_keys($probs))),
                array_keys($probs)
              )))
            : $defaultOrder;

        // Peta label gejala (untuk menampilkan gejala yang dilaporkan)
        $symptomsList = [
            'itching' => 'Gatal-gatal', 'stomach_pain' => 'Sakit Perut', 'headache' => 'Sakit Kepala',
            'chills' => 'Meriang / Menggigil', 'toxic_look_(typhos)' => 'Wajah Pucat / Terlihat Sakit',
            'belly_pain' => 'Nyeri Perut Bawah', 'internal_itching' => 'Gatal Bagian Dalam',
            'passage_of_gases' => 'Sering Buang Angin', 'indigestion' => 'Gangguan Pencernaan / Maag',
            'fatigue' => 'Kelelahan / Lemas', 'diarrhoea' => 'Diare', 'ulcers_on_tongue' => 'Sariawan / Luka di Lidah',
            'acidity' => 'Asam Lambung Naik', 'abdominal_pain' => 'Nyeri Perut Atas',
            'irritation_in_anus' => 'Iritasi pada Anus', 'pain_in_anal_region' => 'Nyeri pada Daerah Anus',
            'cough' => 'Batuk', 'high_fever' => 'Demam Tinggi', 'bloody_stool' => 'BAB Berdarah',
            'pain_during_bowel_movements' => 'Nyeri Saat BAB',
        ];
        $reported = is_array($analysis->symptoms) ? $analysis->symptoms : (json_decode($analysis->symptoms ?? '{}', true) ?: []);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Main Result Card -->
        <div class="lg:col-span-8">
            <div class="bg-surface-container-lowest p-margin rounded-2xl shadow-xl border border-outline-variant/30 overflow-hidden relative">
                <!-- Status Banner -->
                <div class="absolute top-0 right-0 p-8">
                    <div class="flex items-center gap-2 px-6 py-2 rounded-full font-headline-md {{ $analysis->result_status == 'NORMAL' ? 'bg-secondary-container text-on-secondary-container' : ($analysis->result_status == 'PERHATIAN' ? 'bg-orange-100 text-orange-800' : 'bg-error-container text-on-error-container') }}">
                        {{ $analysis->result_status }}
                    </div>
                </div>

                <div class="relative z-10">
                    <span class="material-symbols-outlined text-primary text-6xl mb-6">psychology</span>
                    <h2 class="font-label-sm text-outline uppercase tracking-widest mb-2">Diagnosa Terdeteksi:</h2>
                    <h3 class="font-headline-lg text-headline-lg text-on-surface mb-6 uppercase">{{ $analysis->ai_prediction }}</h3>

                    <div class="bg-surface-container-low p-6 rounded-xl mb-8">
                        <h4 class="font-headline-md mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">clinical_notes</span>
                            Rekomendasi Medis
                        </h4>
                        <p class="text-body-lg text-on-surface-variant leading-relaxed">
                            {{ $analysis->recommendation }}
                        </p>
                    </div>

                    <!-- Probabilities Chart (4 kelas penyakit lambung) -->
                    <div class="space-y-4">
                        <h4 class="font-label-sm text-outline uppercase tracking-widest">Tingkat Keyakinan AI</h4>
                        @foreach($diseaseOrder as $label)
                        @php $prob = $probs[$label] ?? 0; $isTop = ($label === $analysis->ai_prediction); @endphp
                        <div class="space-y-1">
                            <div class="flex justify-between text-label-sm {{ $isTop ? 'font-bold text-primary' : '' }}">
                                <span>{{ $label }} @if($isTop)<span class="material-symbols-outlined text-[16px] align-middle">check_circle</span>@endif</span>
                                <span>{{ round($prob * 100, 1) }}%</span>
                            </div>
                            <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden">
                                <div class="h-full {{ $isTop ? 'bg-primary' : 'bg-primary/40' }} transition-all duration-1000" style="width: {{ $prob * 100 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('analysis.history') }}" class="px-8 py-4 rounded-full border border-outline text-on-surface font-headline-sm hover:bg-surface-container-low transition-all">
                    Lihat Riwayat
                </a>
                <a href="{{ route('analysis.diet-guide') }}" class="px-8 py-4 rounded-full border border-outline text-on-surface font-headline-sm flex items-center gap-2 hover:bg-surface-container-low transition-all">
                    <span class="material-symbols-outlined">restaurant_menu</span> Panduan Diet
                </a>
                <a href="https://www.halodoc.com/tanya-dokter" target="_blank" rel="noopener noreferrer" class="px-8 py-4 rounded-full bg-primary text-on-primary font-headline-sm flex items-center gap-2 shadow-lg hover:opacity-90 transition-all ml-auto">
                    Konsultasi Dokter <span class="material-symbols-outlined">medical_information</span>
                </a>
            </div>
        </div>

        <!-- Right Side: Details Summary -->
        <aside class="lg:col-span-4 space-y-6">
            <!-- Gejala yang dilaporkan (input model) -->
            <div class="bg-surface-container-lowest p-gutter rounded-2xl border border-outline-variant/30">
                <h3 class="font-headline-md mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">fact_check</span>
                    Gejala yang Dilaporkan
                </h3>
                @php $adaGejala = false; @endphp
                <div class="space-y-2">
                    @foreach($symptomsList as $key => $label)
                        @if(!empty($reported[$key]))
                            @php $adaGejala = true; @endphp
                            <div class="flex items-center gap-2 text-label-sm text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[18px]">check_circle</span>
                                {{ $label }}
                            </div>
                        @endif
                    @endforeach
                    @unless($adaGejala)
                        <p class="text-label-sm text-on-surface-variant italic">Tidak ada gejala yang dilaporkan.</p>
                    @endunless
                </div>
            </div>

            <!-- Informasi Tambahan (TIDAK dihitung AI) -->
            <div class="bg-surface-container-lowest p-gutter rounded-2xl border border-outline-variant/30">
                <h3 class="font-headline-md mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-on-surface-variant">info</span>
                    Informasi Tambahan
                </h3>
                <p class="text-label-sm text-on-surface-variant mb-5 italic">Catatan gaya hidup — tidak dihitung oleh AI.</p>
                @php
                    $scale = [0 => 'Rendah / Tidak', 1 => 'Ringan', 2 => 'Sedang', 3 => 'Tinggi'];
                    $freq  = [0 => 'Tidak pernah', 1 => 'Kadang', 2 => 'Sering', 3 => 'Selalu'];
                    $yn    = [0 => 'Tidak', 1 => 'Ya'];
                    $bmi = (float) $analysis->bmi;
                    $bmiCat = $bmi < 18.5 ? 'Kurus' : ($bmi < 25 ? 'Normal' : ($bmi < 30 ? 'Overweight' : 'Obesitas'));
                    $rows = [
                        'Usia'             => $analysis->usia . ' tahun',
                        'Jenis Kelamin'    => $analysis->jenis_kelamin ? 'Laki-laki' : 'Perempuan',
                        'IMT'              => ($bmi ? number_format($bmi, 1) . ' (' . $bmiCat . ')' : '-'),
                        'Heartburn'        => $freq[$analysis->heartburn] ?? '-',
                        'Regurgitasi'      => $freq[$analysis->regurgitasi] ?? '-',
                        'Merokok'          => $scale[$analysis->merokok] ?? '-',
                        'Konsumsi Alkohol' => $scale[$analysis->alkohol] ?? '-',
                        'Makanan Pedas'    => $freq[$analysis->makanan_pedas] ?? '-',
                        'Kafein'           => $freq[$analysis->kafein] ?? '-',
                        'Tingkat Stres'    => $scale[$analysis->stres] ?? '-',
                        'Aktivitas Fisik'  => $scale[$analysis->aktivitas_fisik] ?? '-',
                        'Riwayat Keluarga' => $yn[(int) $analysis->riwayat_keluarga] ?? '-',
                    ];
                @endphp
                <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($rows as $label => $val)
                    <div class="flex items-center justify-between border-b border-outline-variant/30 pb-2">
                        <span class="text-on-surface-variant text-label-sm">{{ $label }}</span>
                        <span class="text-on-surface font-medium text-label-sm text-right">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-primary/5 p-gutter rounded-2xl border border-primary/10">
                <h4 class="font-headline-sm text-primary mb-3">Langkah Selanjutnya</h4>
                <p class="text-body-sm text-on-surface-variant mb-4">
                    Simpan laporan ini untuk ditunjukkan kepada dokter Anda.
                    Model AI ini <strong>dilatih khusus pada data 4 penyakit lambung</strong> (GERD, Dispepsia, Gastritis, Tukak Lambung).
                    Hasil <strong>"Tidak terindikasi gangguan lambung"</strong> berarti gejala Anda tidak mengarah ke lambung — <strong>bukan berarti Anda pasti sehat</strong>.
                    Jika gejala Anda mengarah ke kondisi lain di luar lambung (mis. alergi, infeksi, migrain, gangguan hati, dsb.), kondisi tersebut berada di luar cakupan model ini sehingga tidak akan terdeteksi.
                    Bila gejala berlanjut atau mengganggu, tetap periksakan diri ke dokter.
                </p>
                <div class="flex items-center gap-3 text-primary">
                    <span class="material-symbols-outlined">info</span>
                    <span class="text-label-sm font-bold">Hasil telah disimpan di Riwayat</span>
                </div>
            </div>
        </aside>
    </div>
</main>
@endsection
