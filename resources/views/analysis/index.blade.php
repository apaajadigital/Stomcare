@extends('layouts.app')

@section('title', 'Analisa Risiko GERD AI - StomaCare')

@section('content')
<main class="mt-32 mb-section-gap max-w-container-max mx-auto px-gutter">
    <!-- Hero Section -->
    <div class="mb-margin text-center">
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Analisa Gangguan Pencernaan Berbasis Gejala</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">
            Model <strong>Naive Bayes (BernoulliNB)</strong> menilai <strong>gejala yang Anda alami</strong> untuk memperkirakan
            tipe gangguan lambung: <strong>GERD, Dispepsia, Gastritis, atau Tukak Lambung</strong>.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Form Container -->
        <div class="lg:col-span-8 space-y-8">
            @if ($errors->any())
                <div class="bg-error-container text-on-error-container p-4 rounded-xl mb-4">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-error-container text-on-error-container p-4 rounded-xl mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">error</span>
                    {{ session('error') }}
                </div>
            @endif

            @php
                // Opsi ordinal (kode 0..n harus sesuai encoding dataset klinis)
                $freq   = [0 => 'Tidak pernah', 1 => 'Kadang', 2 => 'Sering', 3 => 'Selalu / Setiap hari'];
                $yesno  = [0 => 'Tidak', 1 => 'Ya'];

                // Definisi seksi form: [name => [label, options]]
                $sections = [
                    'Gejala GERD Utama' => [
                        'icon' => 'medical_services',
                        'fields' => [
                            'heartburn'    => ['Sensasi terbakar di dada (heartburn)', $freq],
                            'regurgitasi'  => ['Naiknya asam / makanan ke tenggorokan (regurgitasi)', $freq],
                            'batuk_kronis' => ['Batuk kronis / suara serak berkepanjangan', $yesno],
                        ],
                    ],
                    'Pola Makan' => [
                        'icon' => 'restaurant',
                        'fields' => [
                            'makanan_pedas'     => ['Konsumsi makanan pedas', $freq],
                            'makanan_berlemak'  => ['Konsumsi makanan berlemak / gorengan', $freq],
                            'kafein'            => ['Konsumsi kopi / kafein', $freq],
                            'minuman_soda'      => ['Konsumsi minuman bersoda', $freq],
                            'waktu_makan_tidur' => ['Kebiasaan makan menjelang tidur', [
                                0 => 'Selalu beri jeda > 3 jam', 1 => 'Kadang makan < 3 jam sebelum tidur',
                                2 => 'Sering makan < 2 jam sebelum tidur', 3 => 'Hampir selalu makan menjelang tidur']],
                        ],
                    ],
                    'Gaya Hidup & Kebiasaan' => [
                        'icon' => 'directions_run',
                        'fields' => [
                            'merokok'         => ['Kebiasaan merokok', [0 => 'Tidak merokok', 1 => 'Ringan', 2 => 'Sedang', 3 => 'Berat']],
                            'alkohol'         => ['Konsumsi alkohol', [0 => 'Tidak', 1 => 'Kadang', 2 => 'Sering', 3 => 'Rutin']],
                            'nsaid'           => ['Konsumsi obat pereda nyeri (NSAID: ibuprofen, dsb.)', $freq],
                            'aktivitas_fisik' => ['Aktivitas fisik / olahraga', [0 => 'Tidak pernah', 1 => 'Jarang', 2 => 'Cukup', 3 => 'Rutin']],
                            'kualitas_tidur'  => ['Kualitas tidur', [0 => 'Buruk', 1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik']],
                            'posisi_tidur'    => ['Posisi tidur', [0 => 'Kepala ditinggikan', 1 => 'Datar biasa', 2 => 'Tanpa bantal / kepala rendah']],
                        ],
                    ],
                    'Faktor Risiko Lain' => [
                        'icon' => 'warning',
                        'fields' => [
                            'stres'            => ['Tingkat stres', [0 => 'Rendah', 1 => 'Ringan', 2 => 'Sedang', 3 => 'Tinggi']],
                            'riwayat_keluarga' => ['Riwayat keluarga penyakit lambung / GERD', $yesno],
                        ],
                    ],
                ];
            @endphp

            <form action="{{ route('analysis.store') }}" method="POST" id="analysisForm">
                @csrf

                <!-- Section: Data Diri -->
                <section class="bg-surface-container-lowest p-margin rounded-xl soft-shadow mb-8 border border-outline-variant/30">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="material-symbols-outlined text-primary">person</span>
                        <h2 class="font-headline-md text-headline-md">Data Diri &amp; Antropometri</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="usia" class="block font-label-sm text-on-surface-variant mb-2">Usia (tahun)</label>
                            <input id="usia" type="number" name="usia" value="{{ old('usia') }}" min="1" max="120"
                                   class="w-full bg-background border border-outline-variant rounded-lg p-4 focus:ring-2 focus:ring-primary-container focus:outline-none" placeholder="Contoh: 35" required>
                        </div>
                        <div>
                            <label for="jenis_kelamin" class="block font-label-sm text-on-surface-variant mb-2">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin"
                                    class="w-full bg-background border border-outline-variant rounded-lg p-4 focus:ring-2 focus:ring-primary-container focus:outline-none" required>
                                <option value="1" @selected(old('jenis_kelamin')==='1')>Laki-laki</option>
                                <option value="0" @selected(old('jenis_kelamin')==='0')>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label for="tinggi_badan" class="block font-label-sm text-on-surface-variant mb-2">Tinggi Badan (cm)</label>
                            <input id="tinggi_badan" type="number" step="0.1" name="tinggi_badan" value="{{ old('tinggi_badan') }}" min="80" max="250"
                                   class="w-full bg-background border border-outline-variant rounded-lg p-4 focus:ring-2 focus:ring-primary-container focus:outline-none" placeholder="Contoh: 165" required oninput="hitungBMI()">
                        </div>
                        <div>
                            <label for="berat_badan" class="block font-label-sm text-on-surface-variant mb-2">Berat Badan (kg)</label>
                            <input id="berat_badan" type="number" step="0.1" name="berat_badan" value="{{ old('berat_badan') }}" min="20" max="300"
                                   class="w-full bg-background border border-outline-variant rounded-lg p-4 focus:ring-2 focus:ring-primary-container focus:outline-none" placeholder="Contoh: 60" required oninput="hitungBMI()">
                        </div>
                    </div>
                    <p id="bmiInfo" class="mt-4 text-label-sm text-on-surface-variant">IMT akan dihitung otomatis dari tinggi &amp; berat badan.</p>
                </section>

                <!-- Section UTAMA: Gejala yang Dialami (INPUT MODEL BernoulliNB) -->
                @php
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
                @endphp
                <section class="bg-surface-container-lowest p-margin rounded-xl soft-shadow mb-8 border-2 border-primary/40">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-primary">checklist</span>
                        <h2 class="font-headline-md text-headline-md">Gejala yang Dialami</h2>
                        <span class="ml-auto text-label-sm text-on-primary bg-primary px-3 py-1 rounded-full">menentukan hasil AI</span>
                    </div>
                    <p class="text-label-sm text-on-surface-variant mb-6">
                        Centang semua gejala yang Anda rasakan. <strong>Bagian ini yang menentukan hasil prediksi AI.</strong>
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($symptomsList as $key => $label)
                        <label class="flex items-start gap-3 p-4 bg-background border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container-low transition-colors">
                            <input type="checkbox" name="symptoms[{{ $key }}]" value="1" @checked(old("symptoms.$key"))
                                   class="mt-1 w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary cursor-pointer">
                            <span class="font-body-md text-on-surface">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </section>

                <!-- Pembatas: informasi tambahan (TIDAK memengaruhi hasil AI) -->
                <div class="flex items-center gap-3 mb-6 mt-10">
                    <span class="material-symbols-outlined text-on-surface-variant">info</span>
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface-variant">Informasi Tambahan</h2>
                        <p class="text-label-sm text-on-surface-variant">Untuk catatan &amp; saran gaya hidup — <strong>tidak memengaruhi hasil prediksi AI</strong>.</p>
                    </div>
                </div>

                <!-- Sections informasi tambahan: pola makan, gaya hidup, faktor risiko -->
                @foreach ($sections as $title => $section)
                <section class="bg-surface-container-lowest p-margin rounded-xl soft-shadow mb-8 border border-outline-variant/30">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="material-symbols-outlined text-primary">{{ $section['icon'] }}</span>
                        <h2 class="font-headline-md text-headline-md">{{ $title }}</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($section['fields'] as $name => [$label, $options])
                        <div>
                            <label for="{{ $name }}" class="block font-label-sm text-on-surface-variant mb-2">{{ $label }}</label>
                            <select id="{{ $name }}" name="{{ $name }}"
                                    class="w-full bg-background border border-outline-variant rounded-lg p-4 focus:ring-2 focus:ring-primary-container focus:outline-none" required>
                                @foreach ($options as $val => $optLabel)
                                    <option value="{{ $val }}" @selected((string)old($name, '0') === (string)$val)>{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endforeach

                <div class="flex justify-end pt-4">
                    <button class="bg-primary text-on-primary px-12 py-5 rounded-full font-headline-md flex items-center gap-3 hover:opacity-90 active:scale-95 transition-all shadow-xl hover:shadow-primary/20" type="submit">
                        Mulai Analisis AI
                        <span class="material-symbols-outlined">analytics</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Guidance -->
        <aside class="lg:col-span-4 space-y-8">
            <div class="bg-secondary-container p-gutter rounded-2xl">
                <h3 class="font-headline-md text-on-secondary-container mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined">psychology</span>
                    Mesin AI BernoulliNB
                </h3>
                <p class="text-body-md text-on-secondary-container opacity-90 leading-relaxed">
                    Model probabilistik <strong>Bernoulli Naive Bayes</strong> menganalisis pola <strong>gejala biner</strong>
                    (dataset Kaggle, difokuskan pada penyakit lambung) untuk memperkirakan tipe gangguan dari 4 kelas:
                    <strong>GERD, Dispepsia, Gastritis, Tukak Lambung</strong>. Akurasi model ± 98%.
                    Keluhan di luar lambung berada di luar cakupan model dan tidak akan terdeteksi.
                </p>
            </div>

            <div class="bg-surface-container-lowest p-gutter rounded-2xl shadow-sm border border-outline-variant/30">
                <h3 class="font-headline-md text-on-surface mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">help</span>
                    Panduan Pengisian
                </h3>
                <ul class="space-y-6">
                    <li class="flex gap-4">
                        <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0"><span class="text-primary font-bold">1</span></div>
                        <p class="text-body-sm text-on-surface-variant">Isi data sejujur mungkin sesuai kondisi Anda dalam beberapa waktu terakhir.</p>
                    </li>
                    <li class="flex gap-4">
                        <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0"><span class="text-primary font-bold">2</span></div>
                        <p class="text-body-sm text-on-surface-variant">Centang gejala selengkap mungkin — bagian ini yang menentukan hasil AI. Data lain hanya untuk catatan.</p>
                    </li>
                    <li class="flex gap-4">
                        <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0"><span class="text-primary font-bold">3</span></div>
                        <p class="text-body-sm text-on-surface-variant">Hasil ini adalah <strong>pra-diagnosa</strong>, bukan pengganti pemeriksaan medis profesional.</p>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</main>

@push('scripts')
<script>
    function hitungBMI() {
        const t = parseFloat(document.getElementById('tinggi_badan').value);
        const b = parseFloat(document.getElementById('berat_badan').value);
        const info = document.getElementById('bmiInfo');
        if (t > 0 && b > 0) {
            const bmi = b / Math.pow(t / 100, 2);
            let kategori = 'Normal';
            if (bmi < 18.5) kategori = 'Kurus';
            else if (bmi < 25) kategori = 'Normal';
            else if (bmi < 30) kategori = 'Gemuk (Overweight)';
            else kategori = 'Obesitas';
            info.textContent = 'IMT Anda: ' + bmi.toFixed(1) + ' (' + kategori + ')';
        } else {
            info.textContent = 'IMT akan dihitung otomatis dari tinggi & berat badan.';
        }
    }
    document.addEventListener('DOMContentLoaded', hitungBMI);
</script>
@endpush
@endsection
