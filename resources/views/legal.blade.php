@extends('layouts.app')

@section('title', 'Informasi Layanan - StomaCare')

@section('content')
<main class="pt-20">
    <!-- Header Section -->
    <section class="bg-surface-container-low py-16">
        <div class="max-w-container-max mx-auto px-gutter text-center">
            <h1 class="font-headline-xl text-headline-xl mb-4 text-on-surface">Informasi & Bantuan</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">
                Temukan informasi mengenai kebijakan privasi kami, syarat penggunaan layanan, dan cara untuk menghubungi tim kami.
            </p>
        </div>
    </section>

    <!-- Navigation Tabs (Desktop only or Sticky) -->
    <div class="sticky top-16 bg-white/80 backdrop-blur-md border-b border-outline-variant z-20 shadow-sm">
        <div class="max-w-container-max mx-auto px-gutter">
            <div class="flex gap-8 overflow-x-auto no-scrollbar py-4 justify-center">
                <a href="#privacy" class="font-label-sm text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap px-2 py-1">Kebijakan Privasi</a>
                <a href="#terms" class="font-label-sm text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap px-2 py-1">Syarat & Ketentuan</a>
                <a href="#contact" class="font-label-sm text-on-surface-variant hover:text-primary transition-colors whitespace-nowrap px-2 py-1">Hubungi Kami</a>
            </div>
        </div>
    </div>

    <div class="max-w-container-max mx-auto px-gutter py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-8 space-y-24">
                
                <!-- Privacy Policy Section -->
                <section id="privacy" class="scroll-mt-32">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined">security</span>
                        </div>
                        <h2 class="font-headline-lg text-headline-lg">Kebijakan Privasi</h2>
                    </div>
                    <div class="prose prose-slate max-w-none text-on-surface-variant space-y-6 font-body-md">
                        <p>Di StomaCare, privasi Anda adalah prioritas utama kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda saat Anda menggunakan aplikasi kami.</p>
                        
                        <div>
                            <h3 class="text-headline-md font-headline-md text-on-surface mb-3">1. Informasi yang Kami Kumpulkan</h3>
                            <p>Kami mengumpulkan data yang Anda berikan secara sukarela, seperti nama, informasi kesehatan (gejala pencernaan), dan data teknis perangkat untuk meningkatkan akurasi analisa AI kami.</p>
                        </div>

                        <div>
                            <h3 class="text-headline-md font-headline-md text-on-surface mb-3">2. Penggunaan Data</h3>
                            <p>Data kesehatan Anda digunakan semata-mata untuk memberikan analisa gejala melalui algoritma Naive Bayes kami. Kami tidak pernah menjual data pribadi Anda kepada pihak ketiga.</p>
                        </div>

                        <div>
                            <h3 class="text-headline-md font-headline-md text-on-surface mb-3">3. Keamanan Data</h3>
                            <p>Kami menerapkan standar keamanan industri untuk melindungi data Anda dari akses yang tidak sah. Seluruh data medis dienkripsi dengan protokol terkini.</p>
                        </div>
                    </div>
                </section>

                <hr class="border-outline-variant">

                <!-- Terms & Conditions Section -->
                <section id="terms" class="scroll-mt-32">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-secondary/10 text-secondary rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined">gavel</span>
                        </div>
                        <h2 class="font-headline-lg text-headline-lg">Syarat & Ketentuan</h2>
                    </div>
                    <div class="prose prose-slate max-w-none text-on-surface-variant space-y-6 font-body-md">
                        <p>Dengan mengakses StomaCare, Anda setuju untuk terikat oleh syarat dan ketentuan berikut:</p>
                        
                        <div>
                            <h3 class="text-headline-md font-headline-md text-on-surface mb-3">1. Bukan Merupakan Saran Medis</h3>
                            <p class="bg-error-container/20 p-4 rounded-lg border-l-4 border-error text-on-surface">
                                <strong>PENTING:</strong> Analisa yang diberikan oleh StomaCare bersifat informatif dan didasarkan pada AI. Hasil ini bukan merupakan diagnosis medis final. Selalu konsultasikan dengan dokter profesional untuk penanganan medis serius.
                            </p>
                        </div>

                        <div>
                            <h3 class="text-headline-md font-headline-md text-on-surface mb-3">2. Penggunaan Layanan</h3>
                            <p>Anda setuju untuk menggunakan aplikasi ini dengan memberikan data yang jujur agar hasil analisa tetap akurat. Penggunaan aplikasi untuk tujuan ilegal sangat dilarang.</p>
                        </div>

                        <div>
                            <h3 class="text-headline-md font-headline-md text-on-surface mb-3">3. Batasan Tanggung Jawab</h3>
                            <p>StomaCare tidak bertanggung jawab atas keputusan medis yang diambil pengguna tanpa konsultasi dokter profesional setelah menggunakan aplikasi ini.</p>
                        </div>
                    </div>
                </section>

                <hr class="border-outline-variant">

                <!-- Contact Us Section -->
                <section id="contact" class="scroll-mt-32 pb-16">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-tertiary/10 text-tertiary rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined">contact_support</span>
                        </div>
                        <h2 class="font-headline-lg text-headline-lg">Hubungi Kami</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-outline-variant">
                            <h3 class="font-headline-md text-headline-md mb-6">Kirim Pesan</h3>
                            <form action="#" class="space-y-4">
                                <div>
                                    <label class="block font-label-sm mb-2">Nama Lengkap</label>
                                    <input type="text" placeholder="Masukkan nama Anda" class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block font-label-sm mb-2">Email</label>
                                    <input type="email" placeholder="email@contoh.com" class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block font-label-sm mb-2">Pesan</label>
                                    <textarea rows="4" placeholder="Apa yang bisa kami bantu?" class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all resize-none"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-primary text-on-primary py-4 rounded-xl font-headline-md hover:shadow-lg transition-all active:scale-95">
                                    Kirim Sekarang
                                </button>
                            </form>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="bg-surface-container-low p-6 rounded-2xl flex items-start gap-4">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                    <span class="material-symbols-outlined text-primary">mail</span>
                                </div>
                                <div>
                                    <h4 class="font-headline-md text-body-md font-bold mb-1">Email</h4>
                                    <p class="text-on-surface-variant font-body-md">support@stomacare.id</p>
                                </div>
                            </div>
                            
                            <div class="bg-surface-container-low p-6 rounded-2xl flex items-start gap-4">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                    <span class="material-symbols-outlined text-secondary">location_on</span>
                                </div>
                                <div>
                                    <h4 class="font-headline-md text-body-md font-bold mb-1">Lokasi</h4>
                                    <p class="text-on-surface-variant font-body-md">Gedung Inovasi Kesehatan, Lt. 5<br>Jl. Sehat No. 123, Jakarta</p>
                                </div>
                            </div>

                            <div class="bg-surface-container-low p-6 rounded-2xl flex items-start gap-4">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                    <span class="material-symbols-outlined text-tertiary">schedule</span>
                                </div>
                                <div>
                                    <h4 class="font-headline-md text-body-md font-bold mb-1">Jam Operasional</h4>
                                    <p class="text-on-surface-variant font-body-md">Senin - Jumat: 09:00 - 17:00 WIB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Sidebar / Quick Info -->
            <div class="lg:col-span-4 lg:sticky lg:top-32 h-fit">
                <div class="bg-primary text-on-primary p-8 rounded-[2rem] relative overflow-hidden shadow-xl">
                    <div class="absolute -right-4 -bottom-4 opacity-10">
                        <span class="material-symbols-outlined text-[120px]">verified_user</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md mb-4 relative z-10">Punya Pertanyaan Mendesak?</h3>
                    <p class="font-body-md mb-8 relative z-10 opacity-90">
                        Tim bantuan kami siap merespon pertanyaan Anda dalam waktu kurang dari 24 jam di hari kerja.
                    </p>
                    <a href="mailto:support@stomacare.id" class="inline-block bg-white text-primary px-6 py-3 rounded-full font-label-sm relative z-10 hover:bg-surface-bright transition-all">
                        Hubungi via Email
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    html {
        scroll-behavior: smooth;
    }
</style>
@endpush
