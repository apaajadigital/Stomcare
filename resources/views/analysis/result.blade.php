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
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Laporan Perkiraan Keparahan GERD</h1>
        <p class="text-on-surface-variant font-body-lg max-w-2xl mx-auto">
            Hasil dari <strong>dua model Naive Bayes</strong>: perkiraan <strong>tingkat keparahan GERD</strong> (dari gaya hidup &amp; data diri) dan perkiraan <strong>tipe gangguan</strong> (dari gejala yang dicentang, model ASLAM).
        </p>
    </div>

    @php
        $severityOrder = ['Normal', 'GERD Ringan', 'GERD Sedang', 'GERD Berat', 'Komplikasi'];
        $probs = is_array($analysis->ai_probabilities) ? $analysis->ai_probabilities : (json_decode($analysis->ai_probabilities ?? '{}', true) ?: []);
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
                    <h2 class="font-label-sm text-outline uppercase tracking-widest mb-2">Perkiraan Tingkat Keparahan:</h2>
                    <h3 class="font-headline-lg text-headline-lg text-on-surface mb-6 uppercase">{{ $analysis->ai_prediction }}</h3>

                    <div class="bg-surface-container-low p-6 rounded-xl mb-8">
                        <h4 class="font-headline-md mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">clinical_notes</span>
                            Rekomendasi &amp; Saran Gaya Hidup
                        </h4>
                        <p class="text-body-lg text-on-surface-variant leading-relaxed">
                            {{ $analysis->recommendation }}
                        </p>
                    </div>

                    <!-- Probabilities Chart (urut berdasarkan keparahan) -->
                    <div class="space-y-4">
                        <h4 class="font-label-sm text-outline uppercase tracking-widest">Tingkat Keyakinan AI per Kelas</h4>
                        @foreach($severityOrder as $label)
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

            <!-- Symptom-based Type Card (ASLAM BernoulliNB) -->
            @php
                $symptomOrder = ['Normal', 'Dispepsia', 'GERD', 'Gastritis', 'Tukak Lambung'];
                $sprobs = is_array($analysis->symptom_probabilities) ? $analysis->symptom_probabilities : (json_decode($analysis->symptom_probabilities ?? '{}', true) ?: []);
            @endphp
            <div class="mt-8 bg-surface-container-lowest p-margin rounded-2xl shadow-lg border border-outline-variant/30">
                <div class="flex items-center gap-3 mb-1">
                    <span class="material-symbols-outlined text-primary">coronavirus</span>
                    <h3 class="font-headline-md text-headline-md">Perkiraan Tipe Gangguan</h3>
                </div>
                <p class="text-label-sm text-on-surface-variant mb-6">Model gejala <strong>ASLAM Naive Bayes</strong> (BernoulliNB) berdasarkan gejala yang Anda centang.</p>
                <h4 class="font-headline-lg text-headline-lg text-on-surface mb-6 uppercase">{{ $analysis->symptom_prediction ?? '-' }}</h4>
                <div class="space-y-4">
                    @foreach($symptomOrder as $label)
                    @php $sp = $sprobs[$label] ?? 0; $isTop = ($label === $analysis->symptom_prediction); @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-label-sm {{ $isTop ? 'font-bold text-primary' : '' }}">
                            <span>{{ $label }} @if($isTop)<span class="material-symbols-outlined text-[16px] align-middle">check_circle</span>@endif</span>
                            <span>{{ round($sp * 100, 1) }}%</span>
                        </div>
                        <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden">
                            <div class="h-full {{ $isTop ? 'bg-primary' : 'bg-primary/40' }} transition-all duration-1000" style="width: {{ $sp * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
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
            <div class="bg-surface-container-lowest p-gutter rounded-2xl border border-outline-variant/30">
                <h3 class="font-headline-md mb-6">Ringkasan Data Anda</h3>
                @php
                    $scale = [0 => 'Rendah / Tidak', 1 => 'Ringan', 2 => 'Sedang', 3 => 'Tinggi'];
                    $yn = [0 => 'Tidak', 1 => 'Ya'];
                    $bmi = (float) $analysis->bmi;
                    $bmiCat = $bmi < 18.5 ? 'Kurus' : ($bmi < 25 ? 'Normal' : ($bmi < 30 ? 'Overweight' : 'Obesitas'));
                    $rows = [
                        'Usia'             => $analysis->usia . ' tahun',
                        'Jenis Kelamin'    => $analysis->jenis_kelamin ? 'Laki-laki' : 'Perempuan',
                        'IMT'              => ($bmi ? number_format($bmi, 1) . ' (' . $bmiCat . ')' : '-'),
                        'Heartburn'        => $scale[$analysis->heartburn] ?? '-',
                        'Regurgitasi'      => $scale[$analysis->regurgitasi] ?? '-',
                        'Merokok'          => $scale[$analysis->merokok] ?? '-',
                        'Alkohol'          => $scale[$analysis->alkohol] ?? '-',
                        'Tingkat Stres'    => $scale[$analysis->stres] ?? '-',
                        'Aktivitas Fisik'  => $scale[$analysis->aktivitas_fisik] ?? '-',
                        'Riwayat Keluarga' => $yn[(int) $analysis->riwayat_keluarga] ?? '-',
                    ];
                @endphp
                <div class="space-y-3 max-h-[320px] overflow-y-auto pr-2 custom-scrollbar">
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
                    Simpan laporan ini untuk ditunjukkan kepada dokter Anda. "Normal" berarti tidak terindikasi GERD, bukan jaminan sehat total.
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
