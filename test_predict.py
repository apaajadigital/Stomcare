"""
Uji integrasi engine SINGLE model StomaCare (BernoulliNB / tipe gangguan lambung).
Menjalankan model_web/ai_predict.py sebagaimana dipanggil Laravel (JSON via STDIN).
Jalankan: python test_predict.py

Model kini 4 kelas penyakit lambung (kelas 'Normal' dibuang sesuai revisi dataset).
'Normal' dan 'Tidak terindikasi gangguan lambung' kini berasal dari ATURAN di
ai_predict.py, bukan dari kelas model.
"""
import subprocess, json, sys, os

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SCRIPT = os.path.join(BASE_DIR, "model_web", "ai_predict.py")
META   = os.path.join(BASE_DIR, "model_web", "symptom_metadata.json")

with open(META, encoding="utf-8") as f:
    meta = json.load(f)

MODEL_CLASSES = set(meta["classes"])                       # 4 kelas penyakit lambung
RULE_LABELS   = {"Normal", "Tidak terindikasi gangguan lambung", "Tidak dapat mendiagnosis"}
VALID         = MODEL_CLASSES | RULE_LABELS
N_CLASSES     = len(meta["classes"])

# (nama, payload, prediksi yang diharapkan atau None bila bebas asal valid)
CASES = [
    ("Gejala GERD (stomach_pain+acidity+indigestion)",
     {"stomach_pain": 1, "acidity": 1, "indigestion": 1}, "GERD"),

    ("Gejala tukak/berat (bloody_stool+demam)",
     {"abdominal_pain": 1, "bloody_stool": 1, "high_fever": 1, "chills": 1}, None),

    ("Tanpa gejala sama sekali -> Normal",
     {}, "Normal"),

    # Jaring pengaman: gejala ada, tapi tidak satu pun mengarah ke lambung.
    # Tanpa aturan ini model akan memaksakan vonis penyakit lambung.
    ("Gejala non-lambung (gatal+sakit kepala)",
     {"itching": 1, "headache": 1}, "Tidak terindikasi gangguan lambung"),

    ("Gejala non-lambung (batuk+lelah)",
     {"cough": 1, "fatigue": 1}, "Tidak terindikasi gangguan lambung"),

    # Satu gejala inti saja: informasi minim, harus ditolak kecuali sangat yakin.
    ("Satu gejala inti (acidity saja)",
     {"acidity": 1}, "Tidak dapat mendiagnosis"),
]

failed = 0
for name, payload, expected in CASES:
    r = None
    try:
        r = subprocess.run([sys.executable, SCRIPT], input=json.dumps(payload),
                           text=True, capture_output=True, check=True)
        out = json.loads(r.stdout)
        assert out["status"] == "success", out
        assert out["prediction"] in VALID, f"prediksi tidak dikenal: {out['prediction']}"
        assert isinstance(out["probabilities"], dict), "probabilities bukan dict"
        assert len(out["probabilities"]) == N_CLASSES, \
            f"jumlah kelas {len(out['probabilities'])}, harusnya {N_CLASSES}"
        assert set(out["probabilities"]) == MODEL_CLASSES, "nama kelas tidak cocok metadata"
        s = sum(out["probabilities"].values())
        assert abs(s - 1.0) < 0.02, f"probabilitas tidak berjumlah ~1: {s}"
        if expected is not None:
            assert out["prediction"] == expected, \
                f"harusnya '{expected}', dapat '{out['prediction']}'"
        print(f"[OK] {name}")
        print(f"     -> {out['prediction']} | " +
              ", ".join(f"{k}={v:.2f}" for k, v in out["probabilities"].items()))
    except Exception as e:
        failed += 1
        print(f"[FAIL] {name}: {e}")
        if r is not None:
            print("  STDOUT:", r.stdout[:300])
            print("  STDERR:", r.stderr[:300])

print(f"\nModel: {N_CLASSES} kelas {sorted(MODEL_CLASSES)}")
print("SEMUA UJI LULUS [OK]" if failed == 0 else f"{failed} UJI GAGAL [FAIL]")
sys.exit(1 if failed else 0)
