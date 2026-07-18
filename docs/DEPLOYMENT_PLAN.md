# StomaCare — Rencana Go-Live (Deploy UTUH via Docker)

**Tujuan:** menjalankan seluruh aplikasi (Laravel + Python ML + model) secara **utuh** di internet.
**Kesimpulan teknis:** Vercel **tidak bisa** menjalankan app ini utuh (serverless tak bisa spawn subprocess Python + filesystem read-only). Solusi utuh = **satu container Docker** di **Railway** (rekomendasi) / Render / Fly.io. Arsitektur & kode **tidak berubah** — Laravel tetap memanggil `ai_predict.py`.

---

## 1. Arsitektur Deploy

```
[ 1 Container Docker ]
  ├─ PHP 8.4  (Laravel, php artisan serve :PORT)
  ├─ Python venv /opt/venv  (numpy, scipy, scikit-learn)
  ├─ model_web/*.pkl  (Mixed NB + ASLAM BernoulliNB)
  └─ SQLite (volume persisten)  ATAU  Postgres terkelola
```

## 2. File yang sudah disiapkan (di `stomacare/stomacare/`)
- `Dockerfile` — image PHP 8.4 + Python + composer + pip
- `docker-entrypoint.sh` — migrate + serve pada `$PORT`
- `requirements-deploy.txt` — dependency inferensi (di-pin)
- `.dockerignore` — kecualikan venv/vendor/dataset besar
- `.gitignore` — sudah menambah `/venv`, `/database/*.sqlite`

---

## 3. Prasyarat
1. Akun **GitHub** (untuk menyimpan repo).
2. Akun **Railway** (https://railway.app) — daftar via GitHub. Ada kredit gratis untuk memulai.
   (Alternatif gratis: **Render** https://render.com, atau **Fly.io**.)

---

## 4. LANGKAH — Railway (jalur utama)

### Langkah 1 — Jadikan `stomacare/stomacare` sebuah repo Git & push ke GitHub
Jalankan di dalam folder `stomacare/stomacare` (bukan folder induk):
```bash
git init
git add .
git commit -m "StomaCare: hybrid Naive Bayes + Docker deploy"
git branch -M main
# buat repo kosong di github.com dulu, lalu:
git remote add origin https://github.com/USERNAME/stomacare.git
git push -u origin main
```
> Pastikan `Dockerfile` berada di **root repo** (yaitu di `stomacare/stomacare/`). Folder lokal `php/`, `php84/`, `composer/` ada di folder INDUK sehingga TIDAK ikut ter-push — itu benar.

### Langkah 2 — Buat project di Railway
1. Railway → **New Project** → **Deploy from GitHub repo** → pilih repo `stomacare`.
2. Railway mendeteksi `Dockerfile` otomatis dan mulai build.

### Langkah 3 — Set Environment Variables (Railway → Variables)
| Key | Value |
|---|---|
| `APP_KEY` | `base64:7bzHcXFvpfuhpQmYfeTDAqD8g9QBp8fogUCNtUX6xII=` (atau generate baru) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://<nama-app>.up.railway.app` (isi setelah domain didapat) |
| `PYTHON_PATH` | `/opt/venv/bin/python` |
| `DB_CONNECTION` | `sqlite` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |

### Langkah 4 — Database persisten (pilih salah satu)
**Opsi 4a — SQLite + Volume (paling mudah, tanpa ubah kode):**
1. Railway → service → **Settings → Volumes → Add Volume**, mount path: `/app/database`.
2. Biarkan `DB_CONNECTION=sqlite`. Data tersimpan permanen di volume.

**Opsi 4b — Postgres terkelola (lebih “production”):**
1. Railway → **New → Database → PostgreSQL**.
2. Tambah variabel referensi ke service web:
   `DB_CONNECTION=pgsql`, `DB_HOST=${{Postgres.PGHOST}}`, `DB_PORT=${{Postgres.PGPORT}}`,
   `DB_DATABASE=${{Postgres.PGDATABASE}}`, `DB_USERNAME=${{Postgres.PGUSER}}`, `DB_PASSWORD=${{Postgres.PGPASSWORD}}`.
3. Tambahkan ekstensi `pdo_pgsql` di `Dockerfile` (baris `docker-php-ext-install` → tambah `pdo_pgsql`), commit & push.

### Langkah 5 — Generate Domain
Railway → service → **Settings → Networking → Generate Domain**. Salin URL → isikan ke `APP_URL`. Redeploy.

### Langkah 6 — Verifikasi
- Buka `https://<domain>` → landing tampil.
- Daftar akun → menu **Analisa** → isi form → submit → hasil (2 kartu: keparahan + tipe) muncul.
- Cek log Railway bila ada error (`AI Analysis Failed` = cek `PYTHON_PATH` & build pip).

---

## 5. Alternatif — Render (free tier)
1. Render → **New → Web Service** → connect repo → **Runtime: Docker**.
2. Instance: Free. Set Environment Variables sama seperti tabel Langkah 3.
3. DB: tambah **PostgreSQL** (free 90 hari) → set var `DB_*` (perlu `pdo_pgsql`). Atau **Disk** (mount `/app/database`) untuk SQLite (disk = paket berbayar di Render).
4. Deploy. Catatan: free tier “tidur” saat idle → cold start ~30–50 dtk pada akses pertama.

---

## 6. (Opsional) Tetap pakai Vercel untuk bagian ML
Jika Anda ingin nama “Vercel” tetap terlibat: pindahkan HANYA inferensi ML ke **Vercel Python Function**:
- Buat `api/predict.py` (logika `ai_predict.py`) + `requirements.txt` (numpy, scipy, scikit-learn) + sertakan `model_web/*.pkl`.
- Ganti `Process::run(...)` di `AnalysisController` menjadi `Illuminate\Support\Facades\Http::post(env('ML_API_URL'), $aiFeatures)`.
- Web (Laravel) tetap di Railway/Render. Ini “Jalur A (Hybrid)”. Butuh refactor ~1 file + set `ML_API_URL`.
> Untuk **utuh & paling stabil**, Docker-container (bagian 4) lebih disarankan.

---

## 7. Checklist Go-Live (keamanan & kualitas)
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `APP_KEY` di-set (jangan kosong)
- [ ] HTTPS aktif (otomatis di Railway/Render)
- [ ] DB persisten (volume atau Postgres) — jangan andalkan SQLite ephemeral
- [ ] Uji end-to-end di domain publik (register → analisa → hasil)
- [ ] Pantau log platform saat submit pertama (memastikan Python & pickle termuat)
- [ ] Disclaimer medis tampil (sudah tertanam di UI/metadata)
- [ ] (Opsional) upgrade `php artisan serve` → FrankenPHP/nginx+fpm untuk konkurensi lebih baik

---

## 8. Catatan penting
- `php artisan serve` cukup untuk demo/sidang, tapi single-worker (saat menunggu Python, request lain antre). Untuk trafik nyata, ganti ke **FrankenPHP** (1 binary) atau **nginx + php-fpm** + `php-fpm` memanggil Python. Bisa saya siapkan bila diperlukan.
- Versi Python di-pin (`requirements-deploy.txt`) agar `*.pkl` termuat tanpa error versi scikit-learn.
- Model & inferensi TIDAK berubah — hasil di produksi identik dengan lokal.
