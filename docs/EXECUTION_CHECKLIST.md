# StomaCare — Execution Checklist (langkah demi langkah)

Checklist berurutan agar setiap task dijalankan tanpa konflik. Tandai `[x]` bila selesai.

## M0 — Environment
- [x] Install Python ML deps ke `venv/` proyek (numpy, pandas, scikit-learn, xgboost, joblib, matplotlib)
- [x] Verifikasi PHP 8.3.32 + Composer 2.10
- [x] Enable `pdo_sqlite` & `sqlite3` di `php/php.ini`
- [x] `.env`: `DB_CONNECTION=sqlite`; `PYTHON_PATH` → `venv/Scripts/python.exe`
- [ ] (Opsional) `git init` + commit baseline sebagai titik rollback
- [ ] Buat folder `archive/`; pindahkan kode mati di akhir (root `ai_predict.py`, `model_export/`, notebook)

## M1 — Model ML (Mixed Naive Bayes)
- [ ] Backup `model_web/*` lama → `model_web/_backup_ensemble/`
- [ ] Skrip training `train_clinical_nb.py`: load dataset, split stratified 80/20
- [ ] Definisikan grup fitur: kontinu (Usia, BMI, pH_Esofagus, DeMeester_Score, Tekanan_LES, Kadar_Gastrin) & diskret (sisanya); set Tier-1 & Tier-full
- [ ] Grid-search GaussianNB `var_smoothing` + CategoricalNB `alpha` (CV f1_macro)
- [ ] Latih Mixed NB + varian pembanding; pilih terbaik
- [ ] Evaluasi (accuracy, F1 macro/weighted, confusion matrix, per-kelas) Tier-1 & Tier-full → simpan `docs/model_metrics.md`
- [ ] Ekspor `gnb_model.pkl`, `cnb_model.pkl`, `label_encoder.pkl`, `metadata.json` ke `model_web/`
- [ ] Tulis `model_web/ai_predict.py` baru (mixed combine + threshold + fallback)
- [ ] Unit test `test_predict.py`: 1 sampel per kelas → status success
- [ ] Update `requirements.txt`

## M2 — Database
- [ ] Isi migrasi `2026_05_07_053530_add_ai_features_to_analyses_table.php` (kolom subjektif + ai_prediction + ai_probabilities)
- [ ] Selaraskan `Analysis.php` `$fillable` & `$casts`
- [ ] `php artisan migrate:fresh` → sukses tanpa error
- [ ] Verifikasi skema tabel `analyses`

## M3 — Backend
- [ ] `AnalysisController@store`: validasi input subjektif; hitung BMI; bangun JSON fitur; panggil python; null-check
- [ ] Mapping 5 kelas keparahan → status + rekomendasi berbasis keparahan & gaya hidup
- [ ] Hapus referensi kode mati; robust error handling
- [ ] `AnalysisController@dietGuide`/`history`: sesuaikan status baru

## M4 — Frontend
- [ ] Backup blade lama → `resources/views/_backup/`
- [ ] Rombak `analysis/index.blade.php` (4 seksi input subjektif, kontrol ordinal berlabel)
- [ ] `result.blade.php`: 5 kelas keparahan + probabilitas + rekomendasi
- [ ] `history.blade.php`: status baru; perbaiki/hapus tren palsu
- [ ] `diet_guide.blade.php`: personalisasi keparahan+gaya hidup
- [ ] Perbaiki teks "26 parameter" & duplikasi label symptom

## M5 — Testing
- [ ] `venv/Scripts/python.exe test_predict.py` → sukses
- [ ] Feature test Laravel (`php artisan test`) untuk auth + analysis store
- [ ] E2E: `php artisan serve` → daftar/login → isi form → hasil tampil
- [ ] Regresi: landing/history/legal render; `migrate:fresh` bersih
- [ ] Validasi lintas profil risiko (low vs high risk → keparahan berbeda)

## M6 — Dokumentasi & Laporan
- [ ] Update `SETUP_GUIDE.md`
- [ ] `docs/model_metrics.md` final
- [ ] Laporan akhir (ringkasan, file diubah + alasan, metrik, testing, rekomendasi)
- [ ] Bersihkan artefak mati ke `archive/`

## Perintah kunci (Windows PowerShell, dari `stomacare/stomacare`)
```powershell
$php  = "..\..\php\php.exe"        # relatif dari stomacare/stomacare
$py   = ".\venv\Scripts\python.exe"
# ML
& $py train_clinical_nb.py
& $py test_predict.py
# Laravel
& $php ..\..\composer\composer.phar install
& $php artisan migrate:fresh
& $php artisan serve      # http://127.0.0.1:8000
npm run build             # atau: npm run dev (Vite) — app pakai Tailwind CDN, build opsional
```
