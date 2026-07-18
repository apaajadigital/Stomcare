"""
StomaCare - HYBRID Inference Engine
Dipanggil oleh Laravel AnalysisController via Symfony Process (JSON via STDIN).

Menjalankan DUA model Naive Bayes berdampingan:
  1) SEVERITY  : Mixed NB (GaussianNB + CategoricalNB) pada fitur subjektif klinis
                 -> tingkat keparahan GERD (Normal/Ringan/Sedang/Berat/Komplikasi)
  2) SYMPTOM   : BernoulliNB (reproduksi ASLAM_NaiveBayes_MultiClass_v2_(2))
                 -> tipe gangguan (Dispepsia/GERD/Gastritis/Normal/Tukak Lambung)

Input JSON menggabungkan kedua set fitur, contoh:
  {"Usia":52,"BMI":26.8,"Heartburn":2,...,               # fitur subjektif (severity)
   "stomach_pain":1,"acidity":1,"heartburn":0,...}        # gejala biner (symptom)

Output JSON:
  {"status":"success",
   "severity":{"prediction":..,"confidence":..,"probabilities":{..}},
   "symptom":{"prediction":..,"confidence":..,"probabilities":{..}}}
"""
import sys, os, json, pickle
import numpy as np

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DEFAULTS = {"Usia": 40, "BMI": 24.0}


def p(rel):
    return os.path.join(BASE_DIR, rel)


def load_pickle(name):
    with open(p(name), "rb") as f:
        return pickle.load(f)


def predict_severity(data):
    """Mixed NB (GaussianNB kontinu + CategoricalNB diskret)."""
    with open(p("metadata.json"), "r", encoding="utf-8") as f:
        meta = json.load(f)
    classes = meta["classes"]
    cont_feats, cat_feats = meta["continuous_features"], meta["categorical_features"]
    min_cats = meta["min_categories"]
    class_log_prior = np.array(meta["class_log_prior"], dtype=float)
    threshold = float(meta.get("confidence_threshold", 0.45))
    gnb, cnb = load_pickle("gnb_model.pkl"), load_pickle("cnb_model.pkl")

    Xc = []
    for f in cont_feats:
        v = data.get(f, DEFAULTS.get(f, 0))
        if v is None or v == "":
            v = DEFAULTS.get(f, 0)
        Xc.append(float(v))
    Xc = np.array([Xc], dtype=float)

    Xd = []
    for f in cat_feats:
        v = data.get(f, 0)
        if v is None or v == "":
            v = 0
        try:
            v = int(round(float(v)))
        except (ValueError, TypeError):
            v = 0
        hi = int(min_cats.get(f, 1)) - 1
        Xd.append(max(0, min(v, hi)))
    Xd = np.array([Xd], dtype=int)

    jll = gnb.predict_log_proba(Xc) + cnb.predict_log_proba(Xd) - class_log_prior
    jll = jll - jll.max(axis=1, keepdims=True)
    proba = np.exp(jll); proba = (proba / proba.sum(axis=1, keepdims=True))[0]
    idx = int(np.argmax(proba)); conf = float(proba[idx])
    return {
        "prediction": classes[idx] if conf >= threshold else "Tidak dapat mendiagnosis",
        "confidence": round(conf, 4),
        "probabilities": {classes[i]: round(float(proba[i]), 4) for i in range(len(classes))},
    }


def predict_symptom(data):
    """BernoulliNB gejala biner (model ASLAM)."""
    with open(p("symptom_metadata.json"), "r", encoding="utf-8") as f:
        meta = json.load(f)
    classes = meta["classes"]
    base = meta["base_features"]
    order = meta["feature_order"]
    threshold = float(meta.get("confidence_threshold", 0.60))
    model, le = load_pickle("symptom_model.pkl"), load_pickle("symptom_label_encoder.pkl")

    vals = {}
    total = 0
    for feat in base:
        v = data.get(feat, 0)
        try:
            v = 1 if int(round(float(v))) >= 1 else 0
        except (ValueError, TypeError):
            v = 0
        vals[feat] = v
        total += v
    vals["GERD_Indicator"]      = vals.get("stomach_pain", 0)   * vals.get("acidity", 0)
    vals["Digestive_Distress"]  = vals.get("abdominal_pain", 0) * vals.get("passage_of_gases", 0)
    vals["Gastritis_Indicator"] = vals.get("diarrhoea", 0)      * vals.get("belly_pain", 0)

    X = np.array([[vals.get(f, 0) for f in order]], dtype=float)
    proba = model.predict_proba(X)[0]
    idx = int(np.argmax(proba)); conf = float(proba[idx])
    prob_map = {classes[i]: round(float(proba[i]), 4) for i in range(len(classes))}

    if total == 0:
        # tanpa gejala -> Normal (perilaku ASLAM)
        prediction = "Normal"
        conf = float(prob_map.get("Normal", conf))
    elif conf < threshold:
        prediction = "Tidak dapat mendiagnosis"
    else:
        prediction = classes[idx]
    return {"prediction": prediction, "confidence": round(conf, 4), "probabilities": prob_map}


def main():
    try:
        raw = sys.stdin.read()
        if not raw:
            print(json.dumps({"status": "error", "message": "No input data provided"}))
            return
        data = json.loads(raw)
        result = {"status": "success",
                  "severity": predict_severity(data),
                  "symptom": predict_symptom(data)}
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))


if __name__ == "__main__":
    main()
