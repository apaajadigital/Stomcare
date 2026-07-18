# =====================================================================
#  StomaCare - TRAINING: Model ASLAM Naive Bayes (Gejala Biner)
#  Reproduksi setia notebook ASLAM_NaiveBayes_MultiClass_v2_(2).ipynb
#  Model  : BernoulliNB tunggal (20 gejala + 3 fitur interaksi)
#  Output : Tipe gangguan (Dispepsia/GERD/Gastritis/Normal/Tukak Lambung)
#  Deploy : model_web/symptom_*  (untuk mode HYBRID di website)
# =====================================================================
import os, json, pickle, warnings
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split, StratifiedKFold, cross_val_score
from sklearn.preprocessing import LabelEncoder
from sklearn.naive_bayes import BernoulliNB
from sklearn.metrics import accuracy_score, f1_score, classification_report

warnings.filterwarnings("ignore")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
RS = 42

# --- TAHAP 1: DATA (Kaggle 132-symptom) ---
d = os.path.join(BASE_DIR, "disease-prediction-using-machine-learning")
df_tr = pd.read_csv(os.path.join(d, "Training.csv"))
df_te = pd.read_csv(os.path.join(d, "Testing.csv"))
for df in (df_tr, df_te):
    df.drop(columns=["Unnamed: 133"], errors="ignore", inplace=True)
df_all = pd.concat([df_tr, df_te], ignore_index=True)

# --- TAHAP 2: FITUR (persis notebook ASLAM) ---
SELECTED = ['stomach_pain', 'acidity', 'indigestion', 'abdominal_pain', 'belly_pain',
            'passage_of_gases', 'diarrhoea', 'bloody_stool', 'irritation_in_anus',
            'pain_in_anal_region', 'pain_during_bowel_movements', 'high_fever',
            'chills', 'toxic_look_(typhos)', 'fatigue', 'itching', 'internal_itching',
            'headache', 'ulcers_on_tongue', 'cough']
ENGINEERED = ['GERD_Indicator', 'Digestive_Distress', 'Gastritis_Indicator']
ALL_FEATURES = SELECTED + ENGINEERED

# --- TAHAP 3: MAPPING 5 KELAS (persis notebook) ---
disease_map = {
    'GERD': 'GERD',
    'Peptic ulcer diseae': 'Tukak Lambung', 'Peptic ulcer disease': 'Tukak Lambung',
    'Typhoid': 'Tukak Lambung',
    'Gastroenteritis': 'Gastritis', 'hepatitis A': 'Gastritis', 'Alcoholic hepatitis': 'Gastritis',
    'Chronic cholestasis': 'Dispepsia', 'Drug Reaction': 'Dispepsia', 'Jaundice': 'Dispepsia',
    'Dimorphic hemmorhoids(piles)': 'Dispepsia',
}
normal = [x for x in df_all['prognosis'].unique() if x not in disease_map]
df_gi = df_all[df_all['prognosis'].isin(disease_map)].copy(); df_gi['label'] = df_gi['prognosis'].map(disease_map)
df_no = df_all[df_all['prognosis'].isin(normal)].copy();      df_no['label'] = 'Normal'
df = pd.concat([df_gi, df_no], ignore_index=True)
cols = [c for c in SELECTED if c in df.columns]

le = LabelEncoder(); y = le.fit_transform(df['label'].values)
CLASS_NAMES = list(le.classes_)
X = df[cols].values.astype(float)

Xtr, Xte, ytr, yte = train_test_split(X, y, test_size=0.20, random_state=RS, stratify=y)

def add_eng(Xa, names):
    d = pd.DataFrame(Xa, columns=names)
    g = lambda c: d[c] if c in d.columns else pd.Series(0, index=d.index)
    d['GERD_Indicator']     = g('stomach_pain')   * g('acidity')
    d['Digestive_Distress'] = g('abdominal_pain') * g('passage_of_gases')
    d['Gastritis_Indicator']= g('diarrhoea')      * g('belly_pain')
    return d[ALL_FEATURES].values

Xtr_f, Xte_f = add_eng(Xtr, cols), add_eng(Xte, cols)

# --- TAHAP 9: NOISE AUGMENTATION (training saja, 7% flip, 2500/kelas) ---
def augment(Xc, n, p=0.07, seed=RS):
    rng = np.random.default_rng(seed); need = n - len(Xc)
    if need <= 0: return Xc
    idx = rng.integers(0, len(Xc), size=need); Xn = Xc[idx].copy().astype(float)
    m = rng.random(Xn.shape) < p; Xn[m] = 1.0 - Xn[m]
    return np.vstack([Xc, Xn])

TARGET = 2500; Xp, yp = [], []
for ci, _ in enumerate(CLASS_NAMES):
    Xc = Xtr_f[ytr == ci]
    Xa = augment(Xc, TARGET, seed=RS + ci) if len(Xc) < TARGET else Xc
    Xp.append(Xa); yp.append(np.full(len(Xa), ci))
Xtr_bal = np.vstack(Xp).astype(float); ytr_bal = np.concatenate(yp)

# --- TAHAP 10: BernoulliNB + grid alpha (via CV f1_macro) ---
skf = StratifiedKFold(5, shuffle=True, random_state=RS)
best = (-1, 1.0)
for a in [0.001, 0.01, 0.05, 0.1, 0.2, 0.5, 1.0, 1.5, 2.0, 5.0]:
    s = cross_val_score(BernoulliNB(alpha=a, binarize=0.0), Xtr_bal, ytr_bal, cv=skf, scoring='f1_macro').mean()
    if s > best[0]: best = (s, a)
best_cv, best_alpha = best
model = BernoulliNB(alpha=best_alpha, binarize=0.0).fit(Xtr_bal, ytr_bal)

ypred = model.predict(Xte_f)
acc = accuracy_score(yte, ypred); f1m = f1_score(yte, ypred, average='macro'); f1w = f1_score(yte, ypred, average='weighted')
print("=" * 60)
print("  MODEL ASLAM (BernoulliNB gejala biner)")
print("=" * 60)
print(f"alpha terbaik={best_alpha}  CV_f1_macro={best_cv:.4f}")
print(f"Test: acc={acc:.4f}  f1_macro={f1m:.4f}  f1_weighted={f1w:.4f}")
print(classification_report(yte, ypred, target_names=CLASS_NAMES, digits=4))

# --- EKSPOR -> model_web/symptom_* ---
OUT = os.path.join(BASE_DIR, "model_web")
with open(os.path.join(OUT, "symptom_model.pkl"), "wb") as f: pickle.dump(model, f)
with open(os.path.join(OUT, "symptom_label_encoder.pkl"), "wb") as f: pickle.dump(le, f)
meta = {
    "version": "aslam-symptom-v2",
    "model": "BernoulliNB (ASLAM, gejala biner)",
    "source": "ASLAM_NaiveBayes_MultiClass_v2_(2).ipynb (direproduksi)",
    "task": "Klasifikasi Tipe Gangguan Pencernaan",
    "classes": CLASS_NAMES,
    "base_features": SELECTED,
    "engineered_features": ENGINEERED,
    "feature_order": ALL_FEATURES,
    "engineering": {
        "GERD_Indicator": "stomach_pain*acidity",
        "Digestive_Distress": "abdominal_pain*passage_of_gases",
        "Gastritis_Indicator": "diarrhoea*belly_pain",
    },
    "alpha": float(best_alpha),
    "confidence_threshold": 0.60,
    "metrics": {"accuracy": round(acc, 4), "f1_macro": round(f1m, 4), "f1_weighted": round(f1w, 4),
                "cv_f1_macro": round(best_cv, 4)},
    "disclaimer": "Model gejala biner (dataset Kaggle sintetis). Pra-diagnosa, bukan diagnosis medis.",
}
with open(os.path.join(OUT, "symptom_metadata.json"), "w", encoding="utf-8") as f:
    json.dump(meta, f, indent=2, ensure_ascii=False)
print(f"\nDiekspor: model_web/symptom_model.pkl, symptom_label_encoder.pkl, symptom_metadata.json")
