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

### Langkah 1 — Push ke GitHub
Repo **sudah di-`init`, di-`commit`, dan remote sudah diarahkan** ke `https://github.com/apaajadigital/Stomcare.git`
(branch `main`, 136 file, `vendor/`/`venv/` sudah dikecualikan). Anda tinggal **push** — jalankan di dalam `stomacare/stomacare`:
```bash
git push -u origin main
```
> Push butuh autentикasi GitHub Anda (login/Personal Access Token). Bila diminta, gunakan username GitHub + **PAT** (Settings → Developer settings → Personal access tokens) sebagai password. Jika ada perubahan baru: `git add -A && git commit -m "update" && git push`.
>
> `Dockerfile` sudah di **root repo** (`stomacare/stomacare/`). Folder lokal `php/`, `php84/`, `composer/` ada di folder INDUK → tidak ikut ter-push (benar).

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

## 6. (Opsional) Vercel untuk bagian ML — SUDAH DISIAPKAN
Folder **`ml-vercel/`** sudah berisi fungsi Vercel siap deploy:
- `ml-vercel/api/predict.py` — endpoint `POST /api/predict` (logika hybrid sama persis, sudah diuji lokal)
- `ml-vercel/requirements.txt` — numpy/scipy/scikit-learn (pinned)
- `ml-vercel/vercel.json` — konfigurasi + `includeFiles` model
- `ml-vercel/model_web/` — salinan model (kecil, ~16 KB)

**Deploy fungsi ML ke Vercel:**
1. Vercel → New Project → import repo → **Root Directory: `ml-vercel`** → Deploy.
2. Salin URL fungsi, mis. `https://stomacare-ml.vercel.app/api/predict`.

**Sambungkan ke Laravel (dual-mode sudah ada di controller):**
- Set env di Railway/Render: `ML_API_URL=https://stomacare-ml.vercel.app/api/predict`.
- Controller otomatis memakai HTTP (bukan subprocess) bila `ML_API_URL` diset; bila kosong → subprocess lokal.
> Dengan ini bagian ML **benar-benar di Vercel**, web di Railway/Render. Untuk **utuh & paling sederhana**, biarkan `ML_API_URL` kosong (subprocess di container).

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
- `php artisan serve` (Dockerfile default) cukup untuk demo/sidang, tapi single-worker. **Upgrade FrankenPHP sudah disiapkan**: `Dockerfile.frankenphp` + `docker-entrypoint-frankenphp.sh`. Cara pakai: rename `Dockerfile.frankenphp` menjadi `Dockerfile` lalu commit & push. (Belum diuji-build lokal karena mesin ini tanpa Docker.)
- Versi Python di-pin (`requirements-deploy.txt`) agar `*.pkl` termuat tanpa error versi scikit-learn.
- Model & inferensi TIDAK berubah — hasil di produksi identik dengan lokal.
