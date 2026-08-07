"""
StomaCare - Inference Engine (SINGLE model: BernoulliNB / gejala biner).
Reproduksi ASLAM_NaiveBayes_FINAL_Revisi (20 gejala Kaggle + 4 fitur interaksi = 24 fitur).
Dipanggil Laravel AnalysisController via Symfony Process (JSON via STDIN).

Input  : {"stomach_pain":1,"acidity":1, ...}   (20 gejala biner; 0/1)
Output : {"status":"success","prediction":"GERD",
          "probabilities":{"Dispepsia":..,"GERD":..,"Gastritis":..,"Tukak Lambung":..}}

REVISI: dataset difokuskan ke penyakit lambung saja -> kelas 'Normal' (yang dulu
menampung 31 penyakit non-lambung) DIBUANG. Model kini 4 kelas.

Konsekuensi penting: tanpa kelas 'Normal', model tidak punya opsi untuk menyatakan
"bukan penyakit lambung" -- ia akan selalu memaksakan salah satu dari 4 kelas.
Pengujian menunjukkan 86.8% kasus gejala non-lambung tetap divonis penyakit lambung
dengan keyakinan >= 0.60. Karena itu ambang kepercayaan saja TIDAK cukup, dan
ditambahkan jaring pengaman berbasis aturan: minimal satu 'gejala inti lambung'
harus dicentang sebelum model diizinkan memberi vonis.

Catatan: fitur subjektif (usia/BMI/stres/diet/gaya hidup) TIDAK diproses di sini --
hanya gejala biner yang menentukan prediksi (sesuai desain single-model).
"""
import sys, os, json, pickle
import numpy as np

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# Fallback bila metadata lama belum memuat kunci core_gastric_symptoms.
DEFAULT_CORE_SYMPTOMS = [
    "stomach_pain", "acidity", "indigestion",
    "abdominal_pain", "belly_pain", "passage_of_gases",
]


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
        core_symptoms = meta.get("core_gastric_symptoms", DEFAULT_CORE_SYMPTOMS)
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

        # 4 fitur interaksi (HARUS sama persis dengan notebook: 24 fitur)
        vals["GERD_Indicator"]      = vals.get("stomach_pain", 0)   * vals.get("acidity", 0)
        vals["Infection_Indicator"] = vals.get("high_fever", 0)     * vals.get("chills", 0)
        vals["Digestive_Distress"]  = vals.get("abdominal_pain", 0) * vals.get("passage_of_gases", 0)
        vals["Gastritis_Indicator"] = vals.get("diarrhoea", 0)      * vals.get("belly_pain", 0)

        # jumlah gejala INTI lambung yang dicentang (jaring pengaman)
        n_inti = sum(vals.get(f, 0) for f in core_symptoms)

        X = np.array([[vals.get(f, 0) for f in order]], dtype=float)
        proba = model.predict_proba(X)[0]
        idx = int(np.argmax(proba))
        conf = float(proba[idx])
        prob_map = {classes[i]: round(float(proba[i]), 4) for i in range(len(classes))}

        if total == 0:
            # tanpa gejala sama sekali
            prediction = "Normal"
        elif n_inti == 0:
            # Ada gejala, tapi tidak satu pun mengarah ke lambung (mis. gatal-gatal,
            # sakit kepala, batuk). Model 4-kelas ini TIDAK punya kelas 'Normal',
            # sehingga tanpa aturan ini ia akan memaksakan vonis penyakit lambung
            # dengan keyakinan tinggi. Lihat catatan di docstring.
            prediction = "Tidak terindikasi gangguan lambung"
        elif n_inti == 1 and conf < single_symptom_threshold:
            # HANYA 1 gejala inti dicentang: informasi terlalu minim untuk model
            # 24-fitur ini, sehingga rawan salah. Perlu keyakinan lebih tinggi
            # (>=85%) baru berani menyimpulkan.
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
