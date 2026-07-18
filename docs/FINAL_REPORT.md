# StomaCare — Laporan Akhir Revisi (Fitur Subjektif + Naive Bayes Multiclass)

**Tanggal:** 2026-07-18 · **Status:** Selesai & terverifikasi di localhost · **Mode: HYBRID (2 model)**

---

## 0. MODE HYBRID — Dua Model Naive Bayes Berdampingan

Sesuai keputusan akhir klien, website menjalankan **DUA model Naive Bayes sekaligus** pada satu kali submit:

| | **Model 1 — SEVERITY** | **Model 2 — SYMPTOM (ASLAM)** |
|---|---|---|
| Algoritma | Mixed NB (GaussianNB + CategoricalNB) | **BernoulliNB** (reproduksi setia `ASLAM_NaiveBayes_MultiClass_v2_(2).ipynb`) |
| Input | 19 fitur subjektif (usia, BMI, stres, diet, gaya hidup) | 20 gejala biner (dicentang user) + 3 fitur interaksi |
| Output | Tingkat keparahan GERD (Normal→Komplikasi) | Tipe gangguan (Dispepsia/GERD/Gastritis/Normal/Tukak Lambung) |
| Akurasi | 0.858 (F1-macro 0.858) | **0.9587 (F1-macro 0.9461)** — identik dgn notebook |
| Artefak | `model_web/gnb_model.pkl`, `cnb_model.pkl` | `model_web/symptom_model.pkl` |
| Skrip latih | `train_clinical_nb.py` | `train_aslam_symptom.py` |

Keduanya dijalankan oleh satu skrip `model_web/ai_predict.py` (mengembalikan `{severity, symptom}`), disimpan di kolom `ai_prediction`/`ai_probabilities` (severity) dan `symptom_prediction`/`symptom_probabilities` (tipe), lalu **ditampilkan berdampingan** di halaman hasil. Contoh terverifikasi: profil risiko tinggi + gejala GERD → **Severity: GERD Berat (EMERGENCY)** & **Tipe: GERD (100%)**.

➡️ Dengan mode ini, permintaan klien **terpenuhi seluruhnya**: (a) model `ASLAM_NaiveBayes_MultiClass_v2_(2)` benar-benar dipakai (direproduksi & di-deploy), DAN (b) fitur subjektif ditambahkan lewat model keparahan.

---

## 1. Ringkasan Analisis

Revisi klien: *menambahkan gejala subjektif (faktor risiko/gaya hidup, pola makan, kebiasaan/BMI, usia, tingkat stres) ke dataset, model, dan website, serta menggunakan pendekatan ASLAM Naive Bayes multiclass.*

**Temuan kunci audit:**
- Website (Laravel) sebelumnya memakai model **gejala biner** (`model_web/` NB ensemble dari `train_revised_model.py`) dengan taksonomi tipe penyakit (GERD/Gastritis/Dispepsia/Tukak Lambung/Normal). **Usia & jenis kelamin dikumpulkan tetapi dibuang** sebelum prediksi. Tidak ada fitur subjektif sama sekali.
- Notebook target `ASLAM_NaiveBayes_MultiClass_v2_(2).ipynb` = BernoulliNB murni gejala biner — **juga tanpa fitur subjektif**.
- Fitur subjektif yang diminta klien **hanya tersedia** di `dataset_aslam_clinical (1).csv` (12.000 baris, taksonomi **keparahan GERD**). Model `Analysis.php` bahkan sudah menyiapkan `$fillable` klinis (bmi, merokok, stres, ...) — bukti desain awal memang klinis.

**Keputusan (disetujui):** Melatih **model Naive Bayes multiclass baru** pada dataset klinis agar fitur subjektif benar-benar menggerakkan prediksi. Output menjadi **5 tingkat keparahan GERD**: Normal, GERD Ringan, GERD Sedang, GERD Berat, Komplikasi. Runtime disiapkan **full-stack** (PHP 8.4 + SQLite + venv Python).

---

## 2. Arsitektur Sesudah Revisi

```
Form gaya hidup (20 input: usia, JK, tinggi, berat + 16 faktor subjektif)
  → POST /analysis → AnalysisController@store
  → hitung BMI; susun 19 fitur (Usia,BMI + 17 kategorikal)
  → Process → model_web/ai_predict.py
  → Mixed Naive Bayes: GaussianNB(kontinu) + CategoricalNB(diskret)
    gabung log-likelihood → softmax → threshold 0.45
  → {prediction, confidence, probabilities}
  → simpan analyses (+ semua fitur subjektif) → halaman hasil (5 kelas keparahan + rekomendasi personal)
```

**Model deploy:** Mixed NB, 19 fitur Tier-1 (self-report). Fitur instrumen klinis Tier-2 (pH esofagus, DeMeester, dst.) sengaja **tidak** diwajibkan ke user; dilaporkan sebagai model pembanding.

---

## 3. Metrik Performa Model

Dataset klinis 12.000 baris, split 80/20 stratified, seimbang 2.400/kelas.

| Model | Accuracy | F1-macro | F1-weighted |
|---|---|---|---|
| **MixedNB Tier-1 (DIDEPLOY)** | **0.8579** | **0.8578** | **0.8578** |
| GaussianNB-all (Tier-1) | 0.8488 | 0.8488 | 0.8488 |
| CategoricalNB-binned (Tier-1) | 0.8538 | 0.8534 | 0.8534 |
| RandomForest (Tier-1, ref) | 0.8296 | 0.8292 | 0.8292 |
| XGBoost (Tier-1, ref) | 0.8337 | 0.8334 | 0.8334 |
| MixedNB Tier-Full (26 fitur, pembanding) | 0.9567 | 0.9565 | 0.9565 |

➡️ **Mixed Naive Bayes mengungguli semua baseline termasuk RandomForest & XGBoost** pada fitur self-report — argumen kuat pemilihan Naive Bayes. Per kelas: Normal F1 0.97, GERD Ringan 0.91, Sedang 0.86, Komplikasi 0.82, GERD Berat 0.73 (kebingungan hanya antar tingkat keparahan berdekatan — wajar secara klinis). Detail: [model_metrics.md](model_metrics.md).

---

## 4. Daftar File yang Dimodifikasi / Ditambahkan

### Machine Learning
| File | Aksi | Alasan |
|---|---|---|
| `train_clinical_nb.py` | **BARU** | Skrip training Mixed NB (grid-search, perbandingan, ekspor, metrik) |
| `model_web/ai_predict.py` | Ditulis ulang | Inferensi Mixed NB (gabung GaussianNB+CategoricalNB, threshold) |
| `model_web/gnb_model.pkl`, `cnb_model.pkl`, `label_encoder.pkl`, `metadata.json` | Diganti | Artefak model keparahan GERD (kontrak fitur v3) |
| `model_web/_backup_ensemble/` | BARU | Backup model ensemble lama |
| `test_predict.py` | Ditulis ulang | Uji integrasi 4 profil (sehat→Normal, risiko tinggi→Komplikasi) |
| `requirements.txt` | Diperbarui | numpy, scipy, scikit-learn, pandas, xgboost, joblib, matplotlib |

### Database
| File | Aksi | Alasan |
|---|---|---|
| `..._add_ai_features_to_analyses_table.php` | Diisi (dulu KOSONG) | Menambah 27 kolom subjektif/klinis + ai_prediction/ai_probabilities |
| `..._create_analyses_table.php` | Diedit | `pain_level` dibuat nullable (legacy) |

### Backend
| File | Aksi | Alasan |
|---|---|---|
| `app/Http/Controllers/AnalysisController.php` | Ditulis ulang | Validasi 20 input subjektif, hitung BMI, mapping fitur, panggil model, mapping 5 keparahan → status + rekomendasi personal, null-check output |

### Frontend
| File | Aksi | Alasan |
|---|---|---|
| `resources/views/analysis/index.blade.php` | Ditulis ulang | Form 4 seksi input subjektif (dropdown ordinal berlabel), auto-BMI |
| `resources/views/analysis/result.blade.php` | Ditulis ulang | 5 kelas keparahan (urut), probabilitas + kelas terpilih ditandai, ringkasan profil nyata |
| `resources/views/analysis/diet_guide.blade.php` | Diedit | Rekomendasi makanan di-key ulang ke 5 kelas keparahan |

### Environment & Docs
| File | Aksi | Alasan |
|---|---|---|
| `.env` | Diedit | DB→sqlite; PYTHON_PATH→venv (di-quote, forward-slash) |
| `../../php84/` | BARU | PHP 8.4.23 (Laravel 13 butuh ≥8.4); `php/php.ini` di-enable sqlite |
| `venv/` | BARU | Virtualenv Python berisi stack ML |
| `docs/IMPLEMENTATION_PLAN.md`, `EXECUTION_CHECKLIST.md`, `model_metrics.md`, `FINAL_REPORT.md` | BARU | Dokumentasi |
| `tests/Feature/AnalysisTest.php` | BARU | 5 feature test alur analisa |
| `archive/ai_predict.py.dead`, `archive/model_export/` | Dipindah | Kode/artefak mati (XGBoost root broken, model_export stale) |

---

## 5. Hasil Testing

| Uji | Hasil |
|---|---|
| `python test_predict.py` (4 profil) | ✅ LULUS — sehat→Normal, risiko tinggi→Komplikasi, sedang→GERD Ringan, kosong→Normal |
| `php artisan test --filter=AnalysisTest` (5 test, 20 assertion) | ✅ LULUS 7 dtk — form load, store+model+DB, high-risk>healthy, validasi, auth guard |
| `php artisan migrate:fresh` | ✅ 8 migrasi sukses dari DB kosong |
| E2E localhost (register→form→submit→result) | ✅ Redirect ke `/analysis/result/1`; profil risiko tinggi → **Komplikasi (98.7%)** EMERGENCY |
| Regresi (GET /, /login, /register, guest /analysis→302) | ✅ Semua sesuai |

---

## 6. Cara Menjalankan (mesin ini)

```powershell
# dari stomacare/stomacare
$php = "..\..\php84\php.exe"
$py  = ".\venv\Scripts\python.exe"

& $py train_clinical_nb.py     # (opsional) latih ulang model
& $py test_predict.py          # uji engine ML
& $php artisan migrate:fresh    # siapkan DB
& $php artisan serve            # http://127.0.0.1:8000
```

---

## 7. Rekomendasi Pengembangan Berikutnya

1. **Validitas data:** `dataset_aslam_clinical` bersifat **sintetis**. Sebelum produksi, validasi/latih ulang dengan **data rekam medis nyata** (mis. kuesioner GERD-Q + diagnosis dokter).
2. **Model Tier-2 opsional:** sediakan mode lanjutan yang menerima input klinis (pH, DeMeester) untuk akurasi 95%+ bagi pengguna yang memilikinya.
3. **Kalibrasi & threshold:** kalibrasi probabilitas (mis. isotonic) dan evaluasi threshold 0.45 pada data nyata.
4. **UI/Build:** rapikan konflik Tailwind (CDN vs Vite yang tidak terpakai), hidupkan dark mode yang mati, ganti gambar hotlink Google Stitch dengan aset lokal, dan ganti "tren kesehatan" palsu di halaman riwayat dengan data nyata.
5. **Keamanan:** rotasi `APP_KEY`, matikan `APP_DEBUG` di produksi, aktifkan alur reset password.
6. **CI:** jalankan `php artisan test` + `python test_predict.py` di pipeline agar tidak ada regresi.

> ⚠️ **Disclaimer:** Hasil model adalah pra-diagnosa berbasis data sintetis, **bukan** pengganti pemeriksaan medis profesional.
