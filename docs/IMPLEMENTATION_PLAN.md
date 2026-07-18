# StomaCare — Implementation Plan (Revisi: Fitur Subjektif + Naive Bayes Multiclass)

**Tanggal:** 2026-07-18
**Revisi klien:** Menambahkan gejala subjektif (faktor risiko/gaya hidup, pola makan, kebiasaan/BMI, usia, tingkat stres) ke dataset, model, dan website.
**Arah yang disetujui:** Opsi A (model) + Opsi B (runtime full-stack).

---

## 1. Ringkasan Keputusan Arsitektur

| Aspek | Sebelum (deployed) | Sesudah (revisi) |
|---|---|---|
| Dataset | Kaggle 132 gejala biner | `dataset_aslam_clinical (1).csv` (12.000 baris, 26 fitur klinis) |
| Fitur input | 20 gejala biner 0/1 | 19 fitur subjektif Tier-1 (usia, BMI, stres, diet, gaya hidup, gejala GERD) |
| Fitur subjektif memengaruhi model | ❌ Tidak | ✅ Ya |
| Algoritma | NB Ensemble (BNB+GNB+CNB) | **Mixed Naive Bayes** = GaussianNB (kontinu) + CategoricalNB (diskret) |
| Taksonomi output | Dispepsia/GERD/Gastritis/Normal/Tukak Lambung | **Normal / GERD Ringan / GERD Sedang / GERD Berat / Komplikasi** |
| DB | MySQL (server tidak ada) | SQLite (`database/database.sqlite`) |

**Alasan Mixed NB:** dataset klinis bercampur fitur kontinu (Usia, BMI, pH, DeMeester, Tekanan_LES, Kadar_Gastrin) dan diskret/ordinal (Stres, Merokok, dst.). GaussianNB optimal untuk kontinu; CategoricalNB optimal untuk kategori berkode integer. Keduanya digabung pada tingkat log-likelihood → tetap "Naive Bayes multiclass" yang benar secara metodologis dan interpretatif untuk skripsi.

**Tier-1 vs Tier-2:** 7 fitur klinis (pH_Esofagus, DeMeester_Score, Tekanan_LES, Grade_Esofagitis, Hernia_Hiatal, H_Pylori, Kadar_Gastrin) memerlukan endoskopi/manometri/lab — TIDAK bisa diisi user awam. Maka **model yang dideploy dilatih pada 19 fitur Tier-1 (self-report)**. Model 26-fitur (Tier-1+Tier-2) dilatih sebagai pembanding di laporan evaluasi (menunjukkan batas atas performa dengan instrumen klinis).

---

## 2. Roadmap Milestone

### M0 — Environment & Safety Net  · Kompleksitas: Rendah · Status: ✅ hampir selesai
- Install Python ML deps ke venv proyek; PHP 8.3.32 + Composer 2.10 aktif; enable `pdo_sqlite`/`sqlite3`.
- `.env`: DB → sqlite, `PYTHON_PATH` → venv proyek.
- `git init` untuk titik rollback (opsional tapi disarankan). Arsipkan kode mati ke `archive/` (jangan hapus dulu).
- **Dependency:** tidak ada. **Risiko:** rendah.

### M1 — Model Machine Learning Baru  · Kompleksitas: Sedang-Tinggi · Prioritas: TERTINGGI
- EDA & validasi `dataset_aslam_clinical` (dtypes, range, missing, balance) — selesai sebagian.
- Preprocessing: label-encode target; stratified split 80/20 (`random_state=42`, tanpa leakage); tanpa augmentasi (data sudah seimbang 2.400/kelas); NB tidak butuh scaling.
- Bangun **Mixed NB** + grid-search (`var_smoothing` untuk GaussianNB, `alpha` untuk CategoricalNB) via `StratifiedKFold(5)` skor `f1_macro`.
- Bandingkan varian: GaussianNB-all, CategoricalNB-binned, Mixed NB, (XGBoost/RandomForest sebagai referensi). Pilih terbaik = model yang dideploy.
- Evaluasi: accuracy, F1 macro/weighted, confusion matrix, precision/recall per kelas — untuk Tier-1 dan Tier-full.
- Ekspor ke `model_web/`: `gnb_model.pkl`, `cnb_model.pkl`, `label_encoder.pkl`, `metadata.json` (kontrak fitur v3). Backup artefak lama dulu.
- Tulis `ai_predict.py` baru (mixed combine + confidence threshold + fallback).
- Unit test Python: sampel input per kelas.
- **Dependency:** M0 (venv). **Risiko:** akurasi Tier-1 mungkin turun tanpa fitur klinis → dilaporkan jujur; data sintetis → disclaimer.

### M2 — Skema Database  · Kompleksitas: Rendah-Sedang
- Perbaiki migrasi `add_ai_features` yang KOSONG → migrasi baru menambah kolom subjektif ke `analyses` (usia, jenis_kelamin, tinggi_badan, berat_badan, bmi, merokok, alkohol, stres, kafein, makanan_pedas, makanan_berlemak, minuman_soda, nsaid, aktivitas_fisik, kualitas_tidur, posisi_tidur, waktu_makan_tidur, heartburn, regurgitasi, batuk_kronis, riwayat_keluarga, ai_prediction, ai_probabilities).
- Pastikan `php artisan migrate:fresh` sukses dari DB kosong.
- Selaraskan `$fillable` & `$casts` di `Analysis.php`.
- **Dependency:** M0. **Risiko:** kolom lama (`pain_level` non-null) → beri default/nullable.

### M3 — Backend Integration  · Kompleksitas: Sedang
- `AnalysisController@store`: validasi input subjektif; hitung BMI dari tinggi/berat (atau input langsung); bangun JSON fitur; panggil `ai_predict.py`; null-check output.
- Peta 5 kelas keparahan → `result_status` (Normal→NORMAL; Ringan/Sedang→PERHATIAN; Berat/Komplikasi→EMERGENCY) + rekomendasi berbasis keparahan & gaya hidup (mis. saran berhenti merokok jika Merokok tinggi).
- Robust error handling; hapus/arsipkan kode mati (`ai_predict.py` root, `model_export/`).
- **Dependency:** M1 (kontrak fitur), M2 (kolom). **Risiko:** regresi flow lama.

### M4 — Frontend  · Kompleksitas: Sedang-Tinggi
- Rombak `analysis/index.blade.php` jadi form bertahap: **Data Diri** (usia, jenis kelamin, tinggi, berat→BMI otomatis), **Pola Makan** (pedas, berlemak, kafein, soda, waktu makan-tidur), **Gaya Hidup & Kebiasaan** (merokok, alkohol, NSAID, aktivitas fisik, kualitas tidur, posisi tidur), **Gejala & Faktor Risiko** (heartburn, regurgitasi, batuk kronis, riwayat keluarga, stres). Kontrol: dropdown/slider berlabel ordinal (0=Tidak pernah … 3=Selalu).
- `result.blade.php`: 5 kelas keparahan, warna sesuai severity, probabilitas, rekomendasi baru.
- `history.blade.php`: sesuaikan status; hapus/perbaiki tren palsu.
- `diet_guide.blade.php`: personalisasi berbasis keparahan + gaya hidup.
- Perbaiki teks jumlah parameter & duplikasi label.
- **Dependency:** M3. **Risiko:** rework besar → uji regresi visual.

### M5 — Testing & Validasi  · Kompleksitas: Sedang
- Unit test Python (inference per kelas).
- Feature test Laravel (auth, analysis store) — butuh PHP (tersedia).
- E2E localhost: `php artisan serve` + submit form → verifikasi prediksi tersimpan & tampil.
- Regresi: halaman lama render tanpa error; `migrate:fresh` bersih.
- Validasi prediksi lintas profil (mis. perokok obesitas stres tinggi → keparahan lebih tinggi).
- **Dependency:** M1–M4.

### M6 — Dokumentasi & Laporan Akhir  · Kompleksitas: Rendah
- Update `SETUP_GUIDE.md` (deps baru, PYTHON_PATH venv, kontrak model, sqlite).
- Laporan akhir: ringkasan analisis, daftar file dimodifikasi + alasan, metrik model, hasil testing, rekomendasi lanjutan.
- Bersihkan artefak mati.

---

## 3. Urutan Eksekusi & Ketergantungan

```
M0 ─┬─> M1 (ML) ──────────────┐
    └─> M2 (DB) ──> M3 (Backend) ──> M4 (Frontend) ──> M5 (Test) ──> M6 (Docs)
                         ^────────────────┘ (kontrak fitur dari M1)
```
M1 dan M2 dapat berjalan paralel. M3 butuh output M1+M2. M4 butuh M3.

## 4. Risiko Utama & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Akurasi Tier-1 turun tanpa fitur klinis | Sedang | Latih & laporkan Tier-1 vs Tier-full; tampilkan disclaimer; opsi tambah fitur klinis lanjutan |
| Dataset sintetis (bukan rekam medis nyata) | Tinggi (validitas) | Disclaimer eksplisit; rekomendasi validasi klinis; jelaskan di laporan |
| Versi sklearn train≠serve | Rendah | venv tunggal untuk train & serve (parity) |
| Rework UI menimbulkan regresi | Sedang | Backup blade lama; uji tiap halaman; commit bertahap |
| Perubahan taksonomi membingungkan user | Sedang | Copy UI & edukasi disesuaikan; jelaskan makna "Normal" ≠ sehat total |

## 5. Definisi Selesai (Definition of Done)
- Model Mixed NB terlatih, dievaluasi, terekspor; `python test_predict.py` sukses per kelas.
- `migrate:fresh` bersih; form subjektif tersimpan lengkap.
- E2E: submit form → prediksi keparahan GERD tampil dengan probabilitas & rekomendasi.
- Tidak ada regresi pada halaman lama; dokumentasi & laporan akhir lengkap.
