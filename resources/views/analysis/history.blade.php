@extends('layouts.app')

@section('title', 'Riwayat Analisa - StomaCare')

@section('content')
<main class="pt-32 pb-section-gap px-gutter max-w-container-max mx-auto">
    <!-- Page Header -->
    <header class="mb-margin">
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Riwayat Analisa</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
            Pantau perkembangan kesehatan pencernaan Anda dari waktu ke waktu melalui arsip riwayat analisa medis yang terorganisir secara klinis.
        </p>
    </header>

    <!-- Summary Statistics Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-surface-container-lowest p-8 rounded-xl soft-shadow border border-surface-variant/20">
            <span class="material-symbols-outlined text-primary mb-4" style='font-variation-settings: "opsz" 32;'>monitoring</span>
            <p class="font-label-sm text-label-sm text-outline mb-1 uppercase tracking-wider">Total Analisa</p>
            <h3 class="font-headline-lg text-headline-lg text-on-surface">{{ $stats['total'] }} Sesi</h3>
        </div>
        <div class="bg-surface-container-lowest p-8 rounded-xl soft-shadow border border-surface-variant/20">
            <span class="material-symbols-outlined text-secondary mb-4" style='font-variation-settings: "opsz" 32;'>verified</span>
            <p class="font-label-sm text-label-sm text-outline mb-1 uppercase tracking-wider">Kondisi Stabil</p>
            <h3 class="font-headline-lg text-headline-lg text-on-surface">{{ $stats['stable'] }} Laporan</h3>
        </div>
        <div class="bg-surface-container-lowest p-8 rounded-xl soft-shadow border border-surface-variant/20">
            <span class="material-symbols-outlined text-error mb-4" style='font-variation-settings: "opsz" 32;'>warning</span>
            <p class="font-label-sm text-label-sm text-outline mb-1 uppercase tracking-wider">Perhatian Medis</p>
            <h3 class="font-headline-lg text-headline-lg text-on-surface">{{ $stats['attention'] }} Catatan</h3>
        </div>
    </div>

    <!-- Timeline Filters -->
    <div class="flex flex-wrap gap-3 mb-8">
        <a href="{{ route('analysis.history') }}" 
           class="px-5 py-2 rounded-full font-label-sm text-label-sm transition-all {{ request('filter', 'all') === 'all' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant' }}">
            Semua Waktu
        </a>
        <a href="{{ route('analysis.history', ['filter' => 'this_month']) }}" 
           class="px-5 py-2 rounded-full font-label-sm text-label-sm transition-all {{ request('filter') === 'this_month' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant' }}">
            Bulan Ini
        </a>
        <a href="{{ route('analysis.history', ['filter' => 'last_3_months']) }}" 
           class="px-5 py-2 rounded-full font-label-sm text-label-sm transition-all {{ request('filter') === 'last_3_months' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant' }}">
            3 Bulan Terakhir
        </a>
        <div class="ml-auto flex items-center gap-2 text-outline">
            <span class="material-symbols-outlined">filter_list</span>
            <span class="font-label-sm text-label-sm">Filter Lanjut</span>
        </div>
    </div>


    <!-- History Records List -->
    <div class="space-y-6">
        @forelse ($records as $record)
        <div class="group bg-surface-container-lowest p-6 md:p-8 rounded-xl shadow-[0px_16px_32px_rgba(0,123,255,0.03)] hover:shadow-[0px_16px_32px_rgba(0,123,255,0.08)] transition-all duration-300 flex flex-col md:flex-row md:items-center gap-6 border border-surface-variant/10">
            <div class="flex-shrink-0 w-16 h-16 bg-surface-container-low rounded-lg flex items-center justify-center {{ $record->result_status == 'NORMAL' ? 'text-primary' : 'text-error' }}">
                <span class="material-symbols-outlined text-3xl">{{ $record->result_status == 'NORMAL' ? 'medical_services' : 'emergency' }}</span>
            </div>
            <div class="flex-grow">
                <div class="flex items-center gap-3 mb-2">
                    <span class="font-label-sm text-label-sm text-outline">{{ $record->created_at->format('d F Y • H:i') }}</span>
                    <span class="px-3 py-1 rounded-full {{ $record->result_status == 'NORMAL' ? 'bg-secondary-container/30 text-on-secondary-container' : 'bg-error-container text-on-error-container' }} font-label-sm text-[12px] font-semibold uppercase">{{ $record->result_status }}</span>
                    @if($record->ai_prediction)
                    <span class="px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-[12px] font-semibold uppercase">{{ $record->ai_prediction }}</span>
                    @endif
                </div>
                <h4 class="font-headline-md text-headline-md text-on-surface mb-2">Analisa Gejala #{{ $record->id }}</h4>
                <p class="font-body-md text-body-md text-on-surface-variant line-clamp-1">{{ $record->recommendation }}</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('analysis.result', $record->id) }}" class="flex items-center gap-2 border border-primary text-primary px-6 py-2.5 rounded-full font-label-sm text-label-sm hover:bg-primary-fixed transition-colors">
                    Lihat Detail <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-20 bg-surface-container-low rounded-xl">
            <span class="material-symbols-outlined text-outline text-5xl mb-4">history</span>
            <p class="text-on-surface-variant font-body-lg">Belum ada riwayat analisa.</p>
            <a href="{{ route('analysis.index') }}" class="text-primary font-bold mt-4 inline-block">Mulai Analisa Sekarang</a>
        </div>
        @endforelse
    </div>

    <!-- Health Visualization Section -->
    <div class="mt-section-gap">
        <div class="bg-surface-container-lowest p-10 rounded-2xl soft-shadow overflow-hidden relative">
            <div class="relative z-10 grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-6">Analisis Tren Kesehatan</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-8">
                        Algoritma kami melacak fluktuasi kesehatan Anda selama 30 hari terakhir. Berdasarkan data, terdapat peningkatan stabilitas pencernaan sebesar 15% sejak bulan lalu.
                    </p>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-label-sm text-label-sm text-on-surface">Kesehatan Pencernaan</span>
                            <span class="font-label-sm text-label-sm text-primary">88% Optimal</span>
                        </div>
                        <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full" style="width: 88%"></div>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl overflow-hidden bg-background p-4 aspect-video flex items-end justify-between gap-2 border border-surface-variant/20">
                    <div class="w-full bg-primary/20 rounded-t-lg" style="height: 40%"></div>
                    <div class="w-full bg-primary/40 rounded-t-lg" style="height: 65%"></div>
                    <div class="w-full bg-primary/30 rounded-t-lg" style="height: 50%"></div>
                    <div class="w-full bg-primary/60 rounded-t-lg" style="height: 75%"></div>
                    <div class="w-full bg-primary rounded-t-lg" style="height: 90%"></div>
                    <div class="w-full bg-secondary-fixed-dim rounded-t-lg" style="height: 85%"></div>
                    <div class="w-full bg-primary rounded-t-lg" style="height: 95%"></div>
                </div>
            </div>
            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
        </div>
    </div>

    <!-- Featured Resource -->
    <div class="mt-section-gap grid md:grid-cols-2 gap-8">
        <div class="relative group overflow-hidden rounded-2xl h-[400px]">
            <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCpAQvm6yhROT4VFHqIut5m_0rd56sCxowVMmZ3-TId8_jkmvzPCvZzraR5j33rR4tJOaWssdjMkH-OoUO0pvB4Oym-Oi5cvCZQxzPMhMdV9Lc2lsSv24iW55eQLAOd0Ll_7rJuT5sO4rYaPk_s5R3uJKdT7pf_ttXE9nbHZ8MWhkiDaXbcqCuEbZ-SUGAluLbZQCkjzA2lWJIjyphPtBbTMT5a0BcK74eA-VRoay2YPjl5nsxO5SGcUT_A-P0guIbJ_0JiKhs6mgbJ"/>
            <div class="absolute inset-0 bg-gradient-to-t from-on-background/90 via-on-background/40 to-transparent p-10 flex flex-col justify-end">
                <h3 class="text-white font-headline-md text-headline-md mb-3">Pentingnya Konsultasi Medis</h3>
                <p class="text-white/90 font-body-md text-body-md leading-relaxed">
                    Hasil analisa StomaCare berbasis kecerdasan buatan bersifat edukatif dan deteksi awal. Selalu bawa dan diskusikan laporan riwayat kesehatan pencernaan Anda kepada dokter atau tenaga medis profesional untuk penanganan klinis yang tepat.
                </p>
            </div>
        </div>
        <div class="bg-secondary-container/20 p-10 rounded-2xl flex flex-col justify-center border border-secondary/10">
            <span class="material-symbols-outlined text-secondary text-5xl mb-6">menu_book</span>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Panduan Diet Personal</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mb-8">
                Berdasarkan riwayat Anda, sistem kami telah menyusun panduan nutrisi yang disesuaikan untuk mempercepat pemulihan mukosa lambung Anda.
            </p>
            <a class="text-secondary font-label-sm text-label-sm font-bold flex items-center gap-2 hover:underline" href="{{ route('analysis.diet-guide') }}">
                Unduh Panduan PDF <span class="material-symbols-outlined">download</span>
            </a>
        </div>
    </div>

</main>

<!-- FAB for Quick Analysis -->
<div class="fixed bottom-10 right-10">
    <a href="{{ url('/analysis') }}" class="bg-primary text-on-primary w-16 h-16 rounded-full shadow-2xl flex items-center justify-center hover:scale-105 active:scale-95 transition-transform">
        <span class="material-symbols-outlined text-3xl">add</span>
    </a>
</div>
@endsection
