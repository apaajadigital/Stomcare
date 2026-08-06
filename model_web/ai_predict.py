"""
StomaCare - Inference Engine (SINGLE model: BernoulliNB / gejala biner).
Reproduksi ASLAM_NaiveBayes_MultiClass_v2 (20 gejala Kaggle + 3 fitur interaksi = 23 fitur).
Dipanggil Laravel AnalysisController via Symfony Process (JSON via STDIN).

Input  : {"stomach_pain":1,"acidity":1, ...}   (20 gejala biner; 0/1)
Output : {"status":"success","prediction":"GERD",
          "probabilities":{"Dispepsia":..,"GERD":..,"Gastritis":..,"Normal":..,"Tukak Lambung":..}}

Catatan: fitur subjektif (usia/BMI/stres/diet/gaya hidup) TIDAK diproses di sini —
hanya gejala biner yang menentukan prediksi (sesuai desain single-model).
"""
import sys, os, json, pickle
import numpy as np

BASE_DIR = os.path.dirname(os.path.abspath(__file__))


def _p(name):
    return os.path.join(BASE_DIR, name)


def _load(name):
    with open(_p(name), "rb") as f:
        return pickle.load(f)


def main():
    try:
        raw = sys.stdin.read()
        if not raw:
            print(json.dumps({"status": "error", "message": "No input data provided"}))
            return
        data = json.loads(raw)

        with open(_p("symptom_metadata.json"), "r", encoding="utf-8") as f:
            meta = json.load(f)
        classes = meta["classes"]
        base = meta["base_features"]
        order = meta["feature_order"]
        threshold = float(meta.get("confidence_threshold", 0.60))
        single_symptom_threshold = float(meta.get("single_symptom_confidence_threshold", 0.85))
        model = _load("symptom_model.pkl")

        # bangun 20 gejala biner + hitung total
        vals, total = {}, 0
        for feat in base:
            v = data.get(feat, 0)
            try:
                v = 1 if int(round(float(v))) >= 1 else 0
            except (ValueError, TypeError):
                v = 0
            vals[feat] = v
            total += v
        # 3 fitur interaksi
        vals["GERD_Indicator"]      = vals.get("stomach_pain", 0)   * vals.get("acidity", 0)
        vals["Digestive_Distress"]  = vals.get("abdominal_pain", 0) * vals.get("passage_of_gases", 0)
        vals["Gastritis_Indicator"] = vals.get("diarrhoea", 0)      * vals.get("belly_pain", 0)

        X = np.array([[vals.get(f, 0) for f in order]], dtype=float)
        proba = model.predict_proba(X)[0]
        idx = int(np.argmax(proba))
        conf = float(proba[idx])
        prob_map = {classes[i]: round(float(proba[i]), 4) for i in range(len(classes))}

        if total == 0:
            # tanpa gejala -> Normal (perilaku ASLAM)
            prediction = "Normal"
        elif total == 1 and conf < single_symptom_threshold:
            # HANYA 1 gejala dicentang: informasi terlalu minim untuk model 20-fitur
            # ini, sehingga rawan salah (mis. gejala non-lambung yang kebetulan
            # cocok pola satu kelas). Perlu keyakinan lebih tinggi (>=85%) baru
            # berani menyimpulkan; selain itu netral ke "Tidak dapat mendiagnosis".
            prediction = "Tidak dapat mendiagnosis"
        elif conf < threshold:
            prediction = "Tidak dapat mendiagnosis"
        else:
            prediction = classes[idx]

        print(json.dumps({
            "status": "success",
            "prediction": prediction,
            "probabilities": prob_map,
        }))
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))


if __name__ == "__main__":
    main()
