"""
Uji integrasi engine SINGLE model StomaCare (BernoulliNB / tipe gangguan).
Menjalankan model_web/ai_predict.py sebagaimana dipanggil Laravel (JSON via STDIN).
Jalankan: venv\\Scripts\\python.exe test_predict.py
"""
import subprocess, json, sys, os

SCRIPT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "model_web", "ai_predict.py")
CLASSES = {"Dispepsia", "GERD", "Gastritis", "Normal", "Tukak Lambung", "Tidak dapat mendiagnosis"}

CASES = {
    "Gejala GERD (acidity+stomach_pain)": {"stomach_pain": 1, "acidity": 1, "indigestion": 1},
    "Gejala tukak/berat (bloody_stool)":  {"abdominal_pain": 1, "bloody_stool": 1, "high_fever": 1, "chills": 1},
    "Tanpa gejala (fallback Normal)":     {},
}

failed = 0
for name, payload in CASES.items():
    try:
        r = subprocess.run([sys.executable, SCRIPT], input=json.dumps(payload),
                           text=True, capture_output=True, check=True)
        out = json.loads(r.stdout)
        assert out["status"] == "success", out
        assert out["prediction"] in CLASSES, f"prediksi invalid: {out['prediction']}"
        assert isinstance(out["probabilities"], dict) and len(out["probabilities"]) == 5
        s = sum(out["probabilities"].values())
        assert abs(s - 1.0) < 0.02, f"probabilitas tidak berjumlah ~1: {s}"
        print(f"[OK] {name}")
        print(f"     -> {out['prediction']} | " +
              ", ".join(f"{k}={v:.2f}" for k, v in out["probabilities"].items()))
    except Exception as e:
        failed += 1
        print(f"[FAIL] {name}: {e}")
        if 'r' in dir():
            print("  STDOUT:", r.stdout[:300]); print("  STDERR:", r.stderr[:300])

print("\n" + ("SEMUA UJI LULUS [OK]" if failed == 0 else f"{failed} UJI GAGAL [FAIL]"))
sys.exit(1 if failed else 0)
