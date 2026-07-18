@extends('layouts.app')

@section('title', 'StomaCare - Serenity for Your Digestive Health')

@section('content')
<main class="pt-20">
    <!-- Hero Section -->
    <section class="relative py-section-gap overflow-hidden" id="vision">
        <div class="max-w-container-max mx-auto px-gutter grid grid-cols-1 lg:grid-cols-2 gap-margin items-center">
            <div class="z-10">
                <span class="inline-block bg-secondary-container text-on-secondary-container px-4 py-1 rounded-full text-label-sm font-label-sm mb-6">
                    Inovasi Kesehatan Pencernaan
                </span>
                <h1 class="font-headline-xl text-headline-xl mb-6 text-on-surface leading-tight">
                    Ketenangan Klinis untuk Kesehatan <span class="text-primary">Perut Anda.</span>
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant mb-10 max-w-lg">
                    StomaCare menggabungkan teknologi AI canggih dengan empati medis untuk membantu Anda memahami kesehatan sistem pencernaan dengan cara yang tenang dan terstruktur.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ url('/analysis') }}" class="bg-primary text-on-primary px-8 py-4 rounded-full font-headline-md text-body-md hover:shadow-lg transition-all active:scale-95 text-center">
                        Mulai Analisa Sekarang
                    </a>
                    <a href="#features" class="border-2 border-secondary text-secondary px-8 py-4 rounded-full font-headline-md text-body-md hover:bg-secondary/5 transition-all text-center">
                        Pelajari Lebih Lanjut
                    </a>

                </div>
            </div>
            <div class="relative">
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-secondary/5 rounded-full blur-3xl"></div>
                <div class="relative bg-white p-6 rounded-[2rem] shadow-[0px_32px_64px_rgba(0,123,255,0.08)] transform lg:rotate-3">
                    <img alt="Digital Health Analysis" class="rounded-2xl w-full h-auto object-cover aspect-[4/3]" src="https://lh3.googleusercontent.com/aida-public/AB6AXuATXei16Y6TpU8VW41ITBHr49nxmScG_G4piMKzpNeki4DGvpEAcvc1IYXUngVyTTEgQtJ1fh9m6ocTJnIutwByVpkJGc_BpUbCla5f2uYL5dtQtvRHt9RLpexYyofwN3QQWhfk-2SGOg0JSeHdEqRxtIIFXYkcBh-6iFNdvAg-TFzL4kT3rvg4hGtGbfZblc1bei2yoGJS9BtsLeAz-cxub21NiB7ZrHi3ub4cnmAz7rphiFL6QEoASo5mK3Yja5c7WZi0vfVI4wCt"/>
                </div>
            </div>
        </div>
    </section>

    <!-- Brief Education Section (Bento Grid) -->
    <section class="py-section-gap bg-surface-container-low" id="education">
        <div class="max-w-container-max mx-auto px-gutter">
            <div class="text-center mb-16">
                <h2 class="font-headline-lg text-headline-lg mb-4">Memahami Gejala Anda</h2>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">
                    Edukasi adalah langkah pertama menuju penyembuhan. Pelajari kondisi pencernaan yang umum terjadi agar Anda dapat mengambil tindakan yang tepat.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- GERD Card -->
                <div class="md:col-span-2 bg-white p-8 rounded-xl shadow-[0px_16px_32px_rgba(0,123,255,0.05)] border border-outline-variant/30 flex flex-col md:flex-row gap-8 items-center">
                    <div class="w-full md:w-1/2">
                        <img alt="GERD Overview" class="rounded-lg object-cover h-48 w-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDJjiujc5hVpGtG6tbAV2tW-clFDo1Nlv1Gwd8Pr0H39VB70piz7z2L80wBkjBzw-q4E7VCCCv0MbBa_9QiSILIenJjP7oM8kfgAGFcXh3WyN9lIkkYB1hWSDFg7CjTAchdtJG0_3c0aYj3mf2mUkXpbmZaHGNMNOH7GYnwXkR5Or3GtyRkYfgGhTPwOPV0LzPKDJJEio-eIwdkR9FJ3wr2N8cs4zGgH_eG_bTSpPxhafLp2qeQ0Mr7DqhBQN_QMBC_IoYax7YyPKo_"/>
                    </div>
                    <div class="w-full md:w-1/2">
                        <h3 class="font-headline-md text-headline-md text-primary mb-3">GERD</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant mb-4">
                            Gastroesophageal Reflux Disease terjadi ketika asam lambung kembali ke kerongkongan, menyebabkan sensasi terbakar di dada.
                        </p>
                        <button onclick="openGerdModal()" class="text-secondary font-label-sm flex items-center gap-2 hover:underline">
                            Baca Selengkapnya <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button>
                    </div>
                </div>
                <!-- Gastritis Card -->
                <div class="bg-white p-8 rounded-xl shadow-[0px_16px_32px_rgba(0,123,255,0.05)] border border-outline-variant/30 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 bg-secondary/10 text-secondary rounded-full flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined">ulna_radius</span>
                        </div>
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-3">Gastritis</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Peradangan pada lapisan pelindung lambung. Kenali gejalanya sejak dini untuk mencegah komplikasi.
                        </p>
                    </div>
                    <button onclick="openGastritisModal()" class="mt-8 text-secondary font-label-sm flex items-center gap-2 hover:underline text-left">
                        Detail Penyakit <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </div>
                <!-- Dyspepsia Card -->
                <div class="bg-white p-8 rounded-xl shadow-[0px_16px_32px_rgba(0,123,255,0.05)] border border-outline-variant/30 flex flex-col justify-between">
                    <div>
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-3">Dispepsia</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant mb-6">
                            Kumpulan gejala tidak nyaman di perut bagian atas seperti begah, mual, dan rasa penuh.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="bg-secondary-container/20 text-on-secondary-container px-3 py-1 rounded-full text-[12px] font-medium">Begah</span>
                            <span class="bg-secondary-container/20 text-on-secondary-container px-3 py-1 rounded-full text-[12px] font-medium">Mual</span>
                            <span class="bg-secondary-container/20 text-on-secondary-container px-3 py-1 rounded-full text-[12px] font-medium">Nyeri</span>
                        </div>
                    </div>
                    <button onclick="openDyspepsiaModal()" class="mt-8 text-secondary font-label-sm flex items-center gap-2 hover:underline text-left">
                        Info Selengkapnya <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </div>

                <!-- Healthy Diet Card -->
                <div class="md:col-span-2 bg-primary text-on-primary p-8 rounded-xl shadow-[0px_16px_32px_rgba(0,123,255,0.05)] flex items-center justify-between overflow-hidden relative">
                    <div class="relative z-10 w-full md:w-3/5">
                        <h3 class="font-headline-md text-headline-md mb-3">Diet Sehat Lambung</h3>
                        <p class="font-body-md opacity-90 mb-6">
                            Temukan daftar makanan yang menenangkan lambung dan bantu proses pemulihan Anda lebih cepat.
                        </p>
                        <button onclick="openDietModal()" class="bg-white text-primary px-6 py-2 rounded-full font-label-sm hover:bg-surface-bright transition-all">
                            Lihat Daftar Makanan
                        </button>

                    </div>
                    <div class="absolute right-0 top-0 h-full w-1/3 opacity-20 pointer-events-none">
                        <span class="material-symbols-outlined text-[200px] absolute -right-10 -top-10">nutrition</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-section-gap" id="features">
        <div class="max-w-container-max mx-auto px-gutter">
            <div class="flex flex-col lg:flex-row gap-margin items-start">
                <div class="lg:w-1/3 lg:sticky lg:top-32">
                    <h2 class="font-headline-lg text-headline-lg mb-6 leading-tight">Fitur Analisa Cerdas StomaCare</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-8">
                        Platform kami dirancang untuk memberikan jawaban yang akurat dan terpercaya dengan antarmuka yang menenangkan.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 text-on-surface">
                            <span class="material-symbols-outlined text-secondary" style='font-variation-settings: "FILL" 1;'>check_circle</span>
                            <span class="font-medium">Teknologi AI Berbasis Medis</span>
                        </div>
                        <div class="flex items-center gap-4 text-on-surface">
                            <span class="material-symbols-outlined text-secondary" style='font-variation-settings: "FILL" 1;'>check_circle</span>
                            <span class="font-medium">Data Keamanan Terjamin</span>
                        </div>
                        <div class="flex items-center gap-4 text-on-surface">
                            <span class="material-symbols-outlined text-secondary" style='font-variation-settings: "FILL" 1;'>check_circle</span>
                            <span class="font-medium">Laporan Hasil yang Mudah Dibaca</span>
                        </div>
                    </div>
                </div>
                <div class="lg:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white p-8 rounded-xl shadow-[0px_8px_24px_rgba(0,123,255,0.03)] border border-surface-container transition-transform hover:-translate-y-1">
                        <div class="w-14 h-14 bg-primary-fixed text-primary rounded-2xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-[32px]">biotech</span>
                        </div>
                        <h4 class="font-headline-md text-headline-md mb-4">Analisa Gejala</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Masukkan gejala yang Anda rasakan, dan algoritma kami akan memetakan kemungkinan penyebab secara akurat.
                        </p>
                    </div>
                    <!-- Feature 2 -->
                    <div class="bg-white p-8 rounded-xl shadow-[0px_8px_24px_rgba(0,123,255,0.03)] border border-surface-container transition-transform hover:-translate-y-1">
                        <div class="w-14 h-14 bg-secondary-fixed text-secondary rounded-2xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-[32px]">history</span>
                        </div>
                        <h4 class="font-headline-md text-headline-md mb-4">Pelacakan Riwayat</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Pantau perkembangan kesehatan pencernaan Anda dari waktu ke waktu dengan grafik yang mudah dipahami.
                        </p>
                    </div>
                    <!-- Feature 3 -->
                    <div class="bg-white p-8 rounded-xl shadow-[0px_8px_24px_rgba(0,123,255,0.03)] border border-surface-container transition-transform hover:-translate-y-1">
                        <div class="w-14 h-14 bg-tertiary-fixed text-tertiary rounded-2xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-[32px]">description</span>
                        </div>
                        <h4 class="font-headline-md text-headline-md mb-4">Rekomendasi Ahli</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Dapatkan saran langkah selanjutnya, termasuk kapan waktu yang tepat untuk berkonsultasi dengan dokter.
                        </p>
                    </div>
                    <!-- Feature 4 -->
                    <div class="bg-white p-8 rounded-xl shadow-[0px_8px_24px_rgba(0,123,255,0.03)] border border-surface-container transition-transform hover:-translate-y-1">
                        <div class="w-14 h-14 bg-error-container text-error rounded-2xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-[32px]">emergency</span>
                        </div>
                        <h4 class="font-headline-md text-headline-md mb-4">Deteksi Dini Bahaya</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Mengenali 'Red Flags' atau tanda bahaya yang memerlukan penanganan medis segera secara otomatis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-section-gap">
        <div class="max-w-container-max mx-auto px-gutter">
            <div class="bg-surface-container-high rounded-[3rem] p-12 md:p-24 text-center relative overflow-hidden shadow-[0px_32px_128px_rgba(0,89,187,0.12)] border border-white">
                <!-- Professional Background Elements -->
                <div class="absolute inset-0 z-0 overflow-hidden">
                    <img src="{{ asset('images/cta_bg.png') }}" class="absolute inset-0 w-full h-full object-cover opacity-15 mix-blend-multiply" alt="">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-secondary/5"></div>
                    <div class="absolute -top-1/2 -left-1/4 w-full h-full bg-primary/5 rounded-full blur-[120px]"></div>
                    <div class="absolute -bottom-1/2 -right-1/4 w-full h-full bg-secondary/5 rounded-full blur-[120px]"></div>
                </div>

                <div class="relative z-10 max-w-2xl mx-auto">
                    <span class="inline-block bg-white/80 backdrop-blur-md text-primary px-5 py-1.5 rounded-full text-label-sm font-bold mb-8 shadow-sm border border-primary/10">
                        Ambil Langkah Pertama
                    </span>
                    <h2 class="font-headline-xl text-headline-xl mb-6 text-on-surface leading-tight">Siap Untuk Hidup Lebih <span class="text-primary">Nyaman?</span></h2>
                    <p class="font-body-lg text-body-lg text-on-surface-variant mb-12 max-w-xl mx-auto">
                        Hanya butuh 2 menit untuk melakukan analisa awal. Tenangkan pikiran Anda dengan informasi kesehatan yang berbasis fakta dan teknologi AI.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ url('/analysis') }}" class="bg-primary text-on-primary px-10 py-5 rounded-full font-headline-md text-body-md shadow-[0px_16px_32px_rgba(0,89,187,0.3)] hover:shadow-[0px_24px_48px_rgba(0,89,187,0.4)] hover:-translate-y-1 transition-all active:scale-95 text-center flex items-center justify-center gap-2">
                            Mulai Analisa Sekarang
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- Diet Modal -->
    <div id="dietModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-on-background/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeDietModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-surface-variant/20">
                <div class="bg-primary px-8 py-6 flex justify-between items-center">
                    <h3 class="text-headline-md font-headline-md text-white" id="modal-title">Panduan Diet Sehat Lambung</h3>
                    <button onclick="closeDietModal()" class="text-white hover:bg-white/10 p-2 rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="px-8 py-8 space-y-8">
                    <!-- Recommended Section -->
                    <section>
                        <h4 class="text-secondary font-bold flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined">check_circle</span>
                            Makanan yang Sangat Dianjurkan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-secondary/5 p-3 rounded-xl border border-secondary/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-secondary text-[20px]">restaurant</span>
                                <span class="text-body-md">Pisang Mas / Ambon</span>
                            </div>
                            <div class="bg-secondary/5 p-3 rounded-xl border border-secondary/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-secondary text-[20px]">restaurant</span>
                                <span class="text-body-md">Oatmeal & Gandum</span>
                            </div>
                            <div class="bg-secondary/5 p-3 rounded-xl border border-secondary/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-secondary text-[20px]">restaurant</span>
                                <span class="text-body-md">Sayur Bayam & Labu</span>
                            </div>
                            <div class="bg-secondary/5 p-3 rounded-xl border border-secondary/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-secondary text-[20px]">restaurant</span>
                                <span class="text-body-md">Putih Telur Rebus</span>
                            </div>
                            <div class="bg-secondary/5 p-3 rounded-xl border border-secondary/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-secondary text-[20px]">restaurant</span>
                                <span class="text-body-md">Ikan Kukus / Rebus</span>
                            </div>
                            <div class="bg-secondary/5 p-3 rounded-xl border border-secondary/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-secondary text-[20px]">restaurant</span>
                                <span class="text-body-md">Madu Asli</span>
                            </div>
                        </div>
                    </section>

                    <!-- Avoid Section -->
                    <section>
                        <h4 class="text-error font-bold flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined">cancel</span>
                            Makanan yang Harus Dihindari
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-error/5 p-3 rounded-xl border border-error/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-error text-[20px]">block</span>
                                <span class="text-body-md">Sambal & Makanan Pedas</span>
                            </div>
                            <div class="bg-error/5 p-3 rounded-xl border border-error/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-error text-[20px]">block</span>
                                <span class="text-body-md">Kopi & Teh Pekat</span>
                            </div>
                            <div class="bg-error/5 p-3 rounded-xl border border-error/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-error text-[20px]">block</span>
                                <span class="text-body-md">Minuman Bersoda</span>
                            </div>
                            <div class="bg-error/5 p-3 rounded-xl border border-error/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-error text-[20px]">block</span>
                                <span class="text-body-md">Buah Asam (Jeruk, Nanas)</span>
                            </div>
                            <div class="bg-error/5 p-3 rounded-xl border border-error/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-error text-[20px]">block</span>
                                <span class="text-body-md">Gorengan Berlemak</span>
                            </div>
                            <div class="bg-error/5 p-3 rounded-xl border border-error/10 flex items-center gap-3">
                                <span class="material-symbols-outlined text-error text-[20px]">block</span>
                                <span class="text-body-md">Santan Kental</span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="bg-surface-container-low px-8 py-6 text-center">
                    <p class="text-body-md text-on-surface-variant mb-4">Ingin analisa yang lebih personal?</p>
                    <a href="{{ url('/analysis') }}" class="bg-primary text-on-primary px-8 py-3 rounded-full font-bold inline-block hover:shadow-lg transition-all active:scale-95">
                        Mulai Analisa AI
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- GERD Modal -->
    <div id="gerdModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-on-background/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeGerdModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-surface-variant/20">
                <div class="bg-primary px-8 py-6 flex justify-between items-center">
                    <h3 class="text-headline-md font-headline-md text-white">Detail Penyakit: GERD</h3>
                    <button onclick="closeGerdModal()" class="text-white hover:bg-white/10 p-2 rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="px-8 py-8 space-y-6">
                    <section>
                        <h4 class="text-primary font-bold mb-2">Apa itu GERD?</h4>
                        <p class="text-body-md text-on-surface-variant italic">Gastroesophageal Reflux Disease (GERD)</p>
                        <p class="text-body-md text-on-surface-variant mt-2">Kondisi kronis di mana asam lambung naik kembali ke kerongkongan, menyebabkan iritasi pada lapisan kerongkongan.</p>
                    </section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">warning</span> Gejala Utama
                            </h4>
                            <ul class="list-disc list-inside text-body-md text-on-surface-variant space-y-1">
                                <li>Rasa terbakar di dada (Heartburn)</li>
                                <li>Rasa asam atau pahit di mulut</li>
                                <li>Nyeri saat menelan</li>
                                <li>Batuk kering kronis</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">psychology</span> Penyebab Umum
                            </h4>
                            <ul class="list-disc list-inside text-body-md text-on-surface-variant space-y-1">
                                <li>Kelemahan katup esofagus bawah</li>
                                <li>Obesitas atau kehamilan</li>
                                <li>Kebiasaan merokok</li>
                                <li>Berbaring segera setelah makan</li>
                            </ul>
                        </div>
                    </div>
                    <div class="bg-primary/5 p-6 rounded-2xl border border-primary/10">
                        <h4 class="font-bold text-primary mb-2">Saran Pencegahan</h4>
                        <p class="text-body-md text-on-surface-variant">Hindari makanan pemicu (pedas, asam, lemak), makan dalam porsi kecil, dan tinggikan bantal saat tidur.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gastritis Modal -->
    <div id="gastritisModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-on-background/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeGastritisModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-surface-variant/20">
                <div class="bg-secondary px-8 py-6 flex justify-between items-center">
                    <h3 class="text-headline-md font-headline-md text-white">Detail Penyakit: Gastritis</h3>
                    <button onclick="closeGastritisModal()" class="text-white hover:bg-white/10 p-2 rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="px-8 py-8 space-y-6">
                    <section>
                        <h4 class="text-secondary font-bold mb-2">Apa itu Gastritis?</h4>
                        <p class="text-body-md text-on-surface-variant italic">Umumnya dikenal sebagai Maag</p>
                        <p class="text-body-md text-on-surface-variant mt-2">Peradangan atau pembengkakan pada lapisan pelindung lambung yang bisa terjadi secara tiba-tiba (akut) atau bertahap (kronis).</p>
                    </section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">warning</span> Gejala Utama
                            </h4>
                            <ul class="list-disc list-inside text-body-md text-on-surface-variant space-y-1">
                                <li>Nyeri perih di ulu hati</li>
                                <li>Mual dan muntah</li>
                                <li>Perut kembung atau penuh</li>
                                <li>Kehilangan nafsu makan</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary">biotech</span> Penyebab Umum
                            </h4>
                            <ul class="list-disc list-inside text-body-md text-on-surface-variant space-y-1">
                                <li>Infeksi bakteri H. pylori</li>
                                <li>Penggunaan obat NSAID berlebih</li>
                                <li>Konsumsi alkohol berlebih</li>
                                <li>Stres fisik yang berat</li>
                            </ul>
                        </div>
                    </div>
                    <div class="bg-secondary/5 p-6 rounded-2xl border border-secondary/10">
                        <h4 class="font-bold text-secondary mb-2">Saran Pencegahan</h4>
                        <p class="text-body-md text-on-surface-variant">Makan teratur tepat waktu, kurangi konsumsi kopi dan alkohol, serta hindari makanan yang terlalu asam atau pedas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dyspepsia Modal -->
    <div id="dyspepsiaModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-on-background/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeDyspepsiaModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-surface-variant/20">
                <div class="bg-tertiary px-8 py-6 flex justify-between items-center">
                    <h3 class="text-headline-md font-headline-md text-white">Detail Penyakit: Dispepsia</h3>
                    <button onclick="closeDyspepsiaModal()" class="text-white hover:bg-white/10 p-2 rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="px-8 py-8 space-y-6">
                    <section>
                        <h4 class="text-tertiary font-bold mb-2">Apa itu Dispepsia?</h4>
                        <p class="text-body-md text-on-surface-variant italic">Indigestion / Gangguan Pencernaan</p>
                        <p class="text-body-md text-on-surface-variant mt-2">Kumpulan gejala ketidaknyamanan pada perut bagian atas, biasanya terkait dengan proses pencernaan makanan.</p>
                    </section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-tertiary">warning</span> Gejala Utama
                            </h4>
                            <ul class="list-disc list-inside text-body-md text-on-surface-variant space-y-1">
                                <li>Perut cepat terasa kenyang</li>
                                <li>Rasa begah/penuh setelah makan</li>
                                <li>Nyeri atau panas di ulu hati</li>
                                <li>Sering bersendawa</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-tertiary">mood_bad</span> Faktor Pemicu
                            </h4>
                            <ul class="list-disc list-inside text-body-md text-on-surface-variant space-y-1">
                                <li>Makan terlalu cepat</li>
                                <li>Konsumsi makanan berminyak</li>
                                <li>Faktor psikis (cemas/stres)</li>
                                <li>Intoleransi makanan tertentu</li>
                            </ul>
                        </div>
                    </div>
                    <div class="bg-tertiary/5 p-6 rounded-2xl border border-tertiary/10">
                        <h4 class="font-bold text-tertiary mb-2">Saran Pencegahan</h4>
                        <p class="text-body-md text-on-surface-variant">Kunyah makanan perlahan, hindari bicara saat makan, dan kelola stres dengan baik untuk melancarkan pencernaan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

@push('scripts')
<script>
    function openDietModal() {
        document.getElementById('dietModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeDietModal() {
        document.getElementById('dietModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openGerdModal() {
        document.getElementById('gerdModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeGerdModal() {
        document.getElementById('gerdModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openGastritisModal() {
        document.getElementById('gastritisModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeGastritisModal() {
        document.getElementById('gastritisModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openDyspepsiaModal() {
        document.getElementById('dyspepsiaModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeDyspepsiaModal() {
        document.getElementById('dyspepsiaModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
@endpush


