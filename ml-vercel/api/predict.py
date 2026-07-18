"""
StomaCare - Vercel Python Serverless Function (opsi HYBRID).
Endpoint POST /api/predict : menerima JSON fitur, mengembalikan {severity, symptom}.
Sama persis logikanya dengan model_web/ai_predict.py, tapi via HTTP (bukan subprocess).

Set env di Laravel: ML_API_URL=https://<proyek>.vercel.app/api/predict
"""
from http.server import BaseHTTPRequestHandler
import os, json, pickle
import numpy as np

MODEL_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "model_web")
DEFAULTS = {"Usia": 40, "BMI": 24.0}


def _p(name):
    return os.path.join(MODEL_DIR, name)


def _load(name):
    with open(_p(name), "rb") as f:
        return pickle.load(f)


# --- muat model sekali saat cold start (di-cache antar invocation hangat) ---
with open(_p("metadata.json"), "r", encoding="utf-8") as f:
    SEV_META = json.load(f)
with open(_p("symptom_metadata.json"), "r", encoding="utf-8") as f:
    SYM_META = json.load(f)
GNB = _load("gnb_model.pkl")
CNB = _load("cnb_model.pkl")
SYM_MODEL = _load("symptom_model.pkl")
SEV_PRIOR = np.array(SEV_META["class_log_prior"], dtype=float)


def predict_severity(data):
    classes = SEV_META["classes"]
    cont, cat = SEV_META["continuous_features"], SEV_META["categorical_features"]
    min_cats = SEV_META["min_categories"]
    thr = float(SEV_META.get("confidence_threshold", 0.45))

    Xc = []
    for f in cont:
        v = data.get(f, DEFAULTS.get(f, 0))
        if v is None or v == "":
            v = DEFAULTS.get(f, 0)
        Xc.append(float(v))
    Xc = np.array([Xc], dtype=float)

    Xd = []
    for f in cat:
        v = data.get(f, 0)
        if v is None or v == "":
            v = 0
        try:
            v = int(round(float(v)))
        except (ValueError, TypeError):
            v = 0
        Xd.append(max(0, min(v, int(min_cats.get(f, 1)) - 1)))
    Xd = np.array([Xd], dtype=int)

    jll = GNB.predict_log_proba(Xc) + CNB.predict_log_proba(Xd) - SEV_PRIOR
    jll = jll - jll.max(axis=1, keepdims=True)
    pr = np.exp(jll); pr = (pr / pr.sum(axis=1, keepdims=True))[0]
    i = int(np.argmax(pr)); conf = float(pr[i])
    return {"prediction": classes[i] if conf >= thr else "Tidak dapat mendiagnosis",
            "confidence": round(conf, 4),
            "probabilities": {classes[k]: round(float(pr[k]), 4) for k in range(len(classes))}}


def predict_symptom(data):
    classes = SYM_META["classes"]
    base, order = SYM_META["base_features"], SYM_META["feature_order"]
    thr = float(SYM_META.get("confidence_threshold", 0.60))

    vals, total = {}, 0
    for feat in base:
        v = data.get(feat, 0)
        try:
            v = 1 if int(round(float(v))) >= 1 else 0
        except (ValueError, TypeError):
            v = 0
        vals[feat] = v; total += v
    vals["GERD_Indicator"]      = vals.get("stomach_pain", 0)   * vals.get("acidity", 0)
    vals["Digestive_Distress"]  = vals.get("abdominal_pain", 0) * vals.get("passage_of_gases", 0)
    vals["Gastritis_Indicator"] = vals.get("diarrhoea", 0)      * vals.get("belly_pain", 0)

    X = np.array([[vals.get(f, 0) for f in order]], dtype=float)
    pr = SYM_MODEL.predict_proba(X)[0]
    i = int(np.argmax(pr)); conf = float(pr[i])
    pmap = {classes[k]: round(float(pr[k]), 4) for k in range(len(classes))}
    if total == 0:
        prediction, conf = "Normal", float(pmap.get("Normal", conf))
    elif conf < thr:
        prediction = "Tidak dapat mendiagnosis"
    else:
        prediction = classes[i]
    return {"prediction": prediction, "confidence": round(conf, 4), "probabilities": pmap}


def run(data):
    return {"status": "success", "severity": predict_severity(data), "symptom": predict_symptom(data)}


class handler(BaseHTTPRequestHandler):
    def _send(self, code, payload):
        body = json.dumps(payload).encode()
        self.send_response(code)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_POST(self):
        try:
            n = int(self.headers.get("content-length", 0))
            data = json.loads(self.rfile.read(n) or b"{}")
            self._send(200, run(data))
        except Exception as e:
            self._send(200, {"status": "error", "message": str(e)})

    def do_GET(self):
        self._send(200, {"status": "ok", "service": "StomaCare ML", "usage": "POST JSON fitur ke endpoint ini"})
