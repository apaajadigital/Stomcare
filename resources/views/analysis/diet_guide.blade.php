<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Diet Personal - StomaCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 py-12">
    <div class="max-w-4xl mx-auto px-6">
        
        <!-- Controls -->
        <div class="no-print flex justify-between items-center mb-8">
            <a href="{{ route('analysis.history') }}" class="flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
                Kembali ke Riwayat
            </a>
            <button id="download-btn" class="bg-blue-600 text-white px-6 py-3 rounded-full font-bold flex items-center gap-2 shadow-lg hover:bg-blue-700 active:scale-95 transition-all">
                <span class="material-symbols-outlined">download</span>
                Unduh PDF
            </button>
        </div>

        <!-- PDF Content -->
        <div id="pdf-content" class="bg-white p-12 rounded-3xl shadow-xl border border-slate-200">
            <!-- Header -->
            <div class="flex justify-between items-start border-b-2 border-blue-100 pb-8 mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-blue-900 mb-2">Panduan Diet Personal</h1>
                    <p class="text-slate-500">Dibuat khusus berdasarkan analisa AI StomaCare</p>
                </div>
                <div class="text-right text-sm text-slate-400">
                    <p>Tanggal: {{ date('d F Y') }}</p>
                    <p>ID Analisa: #{{ $latest->id }}</p>
                </div>
            </div>

            <!-- User Info & Prediction -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                    <h2 class="text-blue-900 font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined">person</span>
                        Data Pengguna
                    </h2>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between border-b border-blue-200/50 pb-1">
                            <span class="text-slate-500">Usia</span>
                            <span class="font-bold">{{ $latest->usia }} Tahun</span>
                        </li>
                        <li class="flex justify-between border-b border-blue-200/50 pb-1">
                            <span class="text-slate-500">Jenis Kelamin</span>
                            <span class="font-bold">{{ $latest->jenis_kelamin ? 'Laki-laki' : 'Perempuan' }}</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-amber-50 p-6 rounded-2xl border border-amber-100">
                    <h2 class="text-amber-900 font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined">analytics</span>
                        Hasil Analisa Terakhir
                    </h2>
                    <div class="flex items-center gap-4">
                        <div class="bg-white p-3 rounded-xl shadow-sm">
                            <span class="text-2xl font-bold text-amber-600">{{ $latest->ai_prediction }}</span>
                        </div>
                        <p class="text-xs text-amber-800 leading-tight">
                            Berdasarkan data gaya hidup, pola makan, dan faktor risiko yang diinput, sistem memperkirakan tingkat keparahan ini.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Diet Recommendations -->
            <div class="space-y-10">
                <section>
                    <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                        <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-lg">1</span>
                        Makanan yang Direkomendasikan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            $allowed = [
                                'Normal' => ['Serat tinggi', 'Biji-bijian', 'Sayuran hijau', 'Buah-buahan segar', 'Air mineral 2L/hari', 'Protein seimbang'],
                                'GERD Ringan' => ['Jahe hangat', 'Melon', 'Oatmeal', 'Kentang rebus', 'Sayuran hijau', 'Ikan/ayam kukus'],
                                'GERD Sedang' => ['Pisang mas/ambon', 'Oatmeal', 'Kembang kol', 'Putih telur', 'Kentang rebus', 'Daging tanpa lemak'],
                                'GERD Berat' => ['Bubur nasi', 'Pisang matang', 'Oatmeal halus', 'Ikan kukus', 'Labu kuning', 'Putih telur rebus'],
                                'Komplikasi' => ['Bubur lembut', 'Pisang', 'Kentang rebus', 'Ikan kukus', 'Sup bening', 'Air putih cukup'],
                            ][$latest->ai_prediction] ?? ['Sayuran hijau', 'Protein rendah lemak', 'Buah non-asam', 'Air putih cukup'];
                        @endphp
                        @foreach($allowed as $food)
                        <div class="flex items-center gap-3 p-4 bg-green-50 rounded-xl border border-green-100">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            <span class="font-medium text-green-800">{{ $food }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                        <span class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-lg">2</span>
                        Makanan yang Harus Dihindari
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            $avoid = [
                                'Normal' => ['Junk food berlebih', 'Garam tinggi', 'Gula tambahan', 'Lemak trans', 'Pewarna buatan', 'Pengawet'],
                                'GERD Ringan' => ['Cabai & sambal', 'Kopi hitam', 'Minuman bersoda', 'Cokelat', 'Gorengan', 'Buah asam'],
                                'GERD Sedang' => ['Cabai & sambal', 'Kopi/kafein', 'Soda', 'Cokelat', 'Santan kental', 'Alkohol'],
                                'GERD Berat' => ['Semua makanan pedas', 'Kopi & soda', 'Gorengan/berlemak', 'Cokelat & peppermint', 'Buah asam', 'Alkohol'],
                                'Komplikasi' => ['Makanan pedas', 'Kopi, soda, alkohol', 'Gorengan & santan', 'Buah/minuman asam', 'Cokelat', 'Makanan keras/kasar'],
                            ][$latest->ai_prediction] ?? ['Makanan pedas', 'Asam', 'Kafein', 'Gorengan'];
                        @endphp
                        @foreach($avoid as $food)
                        <div class="flex items-center gap-3 p-4 bg-red-50 rounded-xl border border-red-100">
                            <span class="material-symbols-outlined text-red-500">cancel</span>
                            <span class="font-medium text-red-800">{{ $food }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>

                <section class="bg-slate-50 p-8 rounded-3xl border border-slate-200 border-dashed">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">tips_and_updates</span>
                        Tips Gaya Hidup untuk Anda
                    </h2>
                    <ul class="space-y-3 text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="text-blue-500 mt-1">•</span>
                            <span>Makan dengan porsi kecil namun lebih sering (5-6 kali sehari).</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-500 mt-1">•</span>
                            <span>Kunyah makanan hingga benar-benar halus sebelum menelan.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-500 mt-1">•</span>
                            <span>Hindari makan berat 3 jam sebelum waktu tidur.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-500 mt-1">•</span>
                            <span>Kelola stres dengan meditasi atau latihan pernapasan.</span>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- Footer PDF -->
            <div class="mt-16 pt-8 border-t border-slate-100 text-center">
                <p class="text-blue-900 font-bold text-xl mb-1">StomaCare</p>
                <p class="text-slate-400 text-xs uppercase tracking-widest">Clinical Serenity for your Digestive Health</p>
                <p class="mt-6 text-[10px] text-slate-400 max-w-lg mx-auto">
                    Dokumen ini adalah panduan informasi umum dan bukan merupakan pengganti saran medis profesional, diagnosis, atau perawatan. Selalu cari saran dari dokter Anda.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('download-btn').addEventListener('click', function () {
            const element = document.getElementById('pdf-content');
            const opt = {
                margin:       0.5,
                filename:     'Panduan_Diet_StomaCare_{{ date("Ymd") }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            // Temporarily adjust for cleaner capture
            const originalShadow = element.style.boxShadow;
            element.style.boxShadow = 'none';
            
            html2pdf().set(opt).from(element).save().then(() => {
                element.style.boxShadow = originalShadow;
            });
        });
    </script>
</body>
</html>
