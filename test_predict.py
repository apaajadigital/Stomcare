"""
Uji integrasi engine HYBRID StomaCare (severity Mixed NB + symptom BernoulliNB/ASLAM).
Menjalankan model_web/ai_predict.py sebagaimana dipanggil Laravel (JSON via STDIN).
Jalankan: venv\\Scripts\\python.exe test_predict.py
"""
import subprocess, json, sys, os

SCRIPT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "model_web", "ai_predict.py")
SEV = {"Normal", "GERD Ringan", "GERD Sedang", "GERD Berat", "Komplikasi", "Tidak dapat mendiagnosis"}
SYM = {"Dispepsia", "GERD", "Gastritis", "Normal", "Tukak Lambung", "Tidak dapat mendiagnosis"}

CASES = {
    "Sehat + tanpa gejala": {
        "Usia": 24, "BMI": 21.0, "Heartburn": 0, "Regurgitasi": 0, "Merokok": 0, "Alkohol": 0,
        "Waktu_Makan_Tidur": 0, "NSAID": 0, "Stres": 0, "Riwayat_Keluarga": 0, "Kafein": 0,
        "Makanan_Pedas": 0, "Makanan_Berlemak": 0, "Posisi_Tidur": 0, "Batuk_Kronis": 0,
        "Jenis_Kelamin": 0, "Aktivitas_Fisik": 3, "Minuman_Soda": 0, "Kualitas_Tidur": 3,
        # gejala biner (ASLAM): kosong
    },
    "Risiko tinggi + gejala GERD (acidity+stomach_pain)": {
        "Usia": 58, "BMI": 33.5, "Heartburn": 3, "Regurgitasi": 3, "Merokok": 3, "Alkohol": 2,
        "Waktu_Makan_Tidur": 3, "NSAID": 2, "Stres": 3, "Riwayat_Keluarga": 1, "Kafein": 3,
        "Makanan_Pedas": 3, "Makanan_Berlemak": 3, "Posisi_Tidur": 2, "Batuk_Kronis": 1,
        "Jenis_Kelamin": 1, "Aktivitas_Fisik": 0, "Minuman_Soda": 3, "Kualitas_Tidur": 0,
        "stomach_pain": 1, "acidity": 1, "indigestion": 1, "cough": 1,
    },
    "Gejala tukak (bloody_stool + abdominal_pain)": {
        "Usia": 45, "BMI": 24, "Stres": 2,
        "abdominal_pain": 1, "bloody_stool": 1, "high_fever": 1, "chills": 1, "fatigue": 1,
    },
    "Input kosong (fallback default)": {},
}

failed = 0
for name, payload in CASES.items():
    try:
        r = subprocess.run([sys.executable, SCRIPT], input=json.dumps(payload),
                           text=True, capture_output=True, check=True)
        out = json.loads(r.stdout)
        assert out["status"] == "success", out
        sev, sym = out["severity"], out["symptom"]
        assert sev["prediction"] in SEV, f"severity invalid: {sev['prediction']}"
        assert sym["prediction"] in SYM, f"symptom invalid: {sym['prediction']}"
        print(f"[OK] {name}")
        print(f"     Keparahan GERD : {sev['prediction']} ({sev['confidence']})")
        print(f"     Tipe Gangguan  : {sym['prediction']} ({sym['confidence']})")
    except Exception as e:
        failed += 1
        print(f"[FAIL] {name}: {e}")
        if 'r' in dir():
            print("  STDOUT:", r.stdout[:300]); print("  STDERR:", r.stderr[:300])

print("\n" + ("SEMUA UJI LULUS [OK]" if failed == 0 else f"{failed} UJI GAGAL [FAIL]"))
sys.exit(1 if failed else 0)
