# =====================================================================
#  StomaCare - TRAINING: Mixed Naive Bayes Multiclass (GERD Severity)
#  Dataset : dataset_aslam_clinical (1).csv  (fitur subjektif + klinis)
#  Model   : GaussianNB (fitur kontinu) + CategoricalNB (fitur diskret)
#  Deploy  : Tier-1 (19 fitur self-report) -> model_web/
#  Author  : Revisi klien - tambah gejala subjektif
# =====================================================================
import os, json, pickle, warnings
import numpy as np
import pandas as pd
from scipy.special import logsumexp
from sklearn.base import BaseEstimator, ClassifierMixin
from sklearn.model_selection import train_test_split, StratifiedKFold, cross_val_score
from sklearn.preprocessing import LabelEncoder, KBinsDiscretizer
from sklearn.naive_bayes import GaussianNB, CategoricalNB
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, f1_score, classification_report, confusion_matrix

warnings.filterwarnings("ignore")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
RANDOM_STATE = 42
DATA_PATH = os.path.join(BASE_DIR, "dataset_aslam_clinical (1).csv")

# ---------------------------------------------------------------------
# 1. DEFINISI FITUR
# ---------------------------------------------------------------------
CONTINUOUS_ALL = ["Usia", "BMI", "pH_Esofagus", "DeMeester_Score", "Tekanan_LES", "Kadar_Gastrin"]
# semua kolom diskret (kode integer non-negatif)
DISCRETE_ALL = ["Heartburn", "Regurgitasi", "Merokok", "Alkohol", "Waktu_Makan_Tidur",
                "NSAID", "Stres", "Riwayat_Keluarga", "Kafein", "Makanan_Pedas",
                "Makanan_Berlemak", "Posisi_Tidur", "Batuk_Kronis", "Jenis_Kelamin",
                "Aktivitas_Fisik", "Minuman_Soda", "Kualitas_Tidur",
                "Hernia_Hiatal", "Grade_Esofagitis", "H_Pylori"]

# Tier-2 = fitur instrumen klinis (endoskopi/manometri/lab) -> TIDAK bisa diisi user awam
TIER2 = ["pH_Esofagus", "DeMeester_Score", "Tekanan_LES", "Grade_Esofagitis",
         "Hernia_Hiatal", "H_Pylori", "Kadar_Gastrin"]

TARGET = "Diagnosis"
# urutan keparahan (untuk pelaporan, bukan urutan encoder)
SEVERITY_ORDER = ["Normal", "GERD Ringan", "GERD Sedang", "GERD Berat", "Komplikasi"]


# ---------------------------------------------------------------------
# 2. MIXED NAIVE BAYES (GaussianNB kontinu + CategoricalNB diskret)
#    Kombinasi pada level log-likelihood, API publik saja.
# ---------------------------------------------------------------------
class MixedNB(BaseEstimator, ClassifierMixin):
    def __init__(self, n_continuous, min_categories, var_smoothing=1e-9, cat_alpha=1.0):
        self.n_continuous = n_continuous          # kolom [0:n_continuous] = kontinu, sisanya diskret
        self.min_categories = min_categories      # jml kategori per fitur diskret
        self.var_smoothing = var_smoothing
        self.cat_alpha = cat_alpha

    def _split(self, X):
        X = np.asarray(X, dtype=float)
        Xc = X[:, :self.n_continuous]
        Xd = X[:, self.n_continuous:].astype(int)
        return Xc, Xd

    def fit(self, X, y):
        Xc, Xd = self._split(X)
        self.gnb_ = GaussianNB(var_smoothing=self.var_smoothing).fit(Xc, y)
        self.cnb_ = CategoricalNB(alpha=self.cat_alpha, min_categories=self.min_categories).fit(Xd, y)
        self.classes_ = self.gnb_.classes_
        self.class_log_prior_ = np.log(self.gnb_.class_prior_)
        return self

    def predict_log_proba(self, X):
        Xc, Xd = self._split(X)
        # setiap predict_log_proba = logP(c) + Sum logP(x|c) - logP(x_group)
        # jumlah -> double count logP(c), maka dikurangi satu prior.
        jll = (self.gnb_.predict_log_proba(Xc)
               + self.cnb_.predict_log_proba(Xd)
               - self.class_log_prior_)
        return jll - logsumexp(jll, axis=1, keepdims=True)

    def predict_proba(self, X):
        return np.exp(self.predict_log_proba(X))

    def predict(self, X):
        return self.classes_[np.argmax(self.predict_log_proba(X), axis=1)]


# ---------------------------------------------------------------------
# 3. UTIL
# ---------------------------------------------------------------------
def build_matrix(df, continuous, discrete):
    """Susun X: kolom kontinu dulu (untuk GaussianNB), lalu diskret (CategoricalNB)."""
    cols = continuous + discrete
    return df[cols].values.astype(float), cols

def min_cats(df, discrete):
    """Jumlah kategori per fitur diskret dari domain penuh (max+1)."""
    return [int(df[c].max()) + 1 for c in discrete]

def evaluate(name, y_true, y_pred, classes):
    acc = accuracy_score(y_true, y_pred)
    f1m = f1_score(y_true, y_pred, average="macro")
    f1w = f1_score(y_true, y_pred, average="weighted")
    print(f"  {name:34} acc={acc:.4f}  f1_macro={f1m:.4f}  f1_weighted={f1w:.4f}")
    return {"model": name, "accuracy": round(acc, 4),
            "f1_macro": round(f1m, 4), "f1_weighted": round(f1w, 4)}


# ---------------------------------------------------------------------
# 4. LOAD & SPLIT
# ---------------------------------------------------------------------
print("=" * 68)
print("  STOMACARE - MIXED NAIVE BAYES (Klinis, Keparahan GERD)")
print("=" * 68)
df = pd.read_csv(DATA_PATH, encoding="utf-8-sig")
df.columns = [c.strip() for c in df.columns]
print(f"Dataset : {df.shape[0]} baris x {df.shape[1]} kolom | missing={int(df.isna().sum().sum())}")

le = LabelEncoder()
y = le.fit_transform(df[TARGET].values)
CLASS_NAMES = list(le.classes_)          # urutan encoder (alfabetis)
print(f"Kelas   : {CLASS_NAMES}")
print(f"Distribusi: {np.bincount(y).tolist()}")

# min_categories dihitung dari domain penuh (robust utk inference)
MINCAT_ALL = {c: int(df[c].max()) + 1 for c in DISCRETE_ALL}

# stratified split SEBELUM apapun (tanpa leakage)
idx_train, idx_test = train_test_split(
    np.arange(len(df)), test_size=0.20, random_state=RANDOM_STATE, stratify=y)
df_tr, df_te = df.iloc[idx_train], df.iloc[idx_test]
y_tr, y_te = y[idx_train], y[idx_test]
print(f"Split   : train={len(df_tr)}  test={len(df_te)}")

results = []

# ---------------------------------------------------------------------
# 5. FUNGSI TRAIN+TUNE MIXED NB UNTUK SATU TIER
# ---------------------------------------------------------------------
def run_mixed_nb(tag, continuous, discrete):
    Xtr, cols = build_matrix(df_tr, continuous, discrete)
    Xte, _    = build_matrix(df_te, continuous, discrete)
    n_cont = len(continuous)
    mincat = [MINCAT_ALL[c] for c in discrete]

    # grid search gabungan (var_smoothing GaussianNB x alpha CategoricalNB)
    vs_grid = [1e-9, 1e-8, 1e-7, 1e-6, 1e-5]
    alpha_grid = [0.01, 0.1, 0.5, 1.0, 2.0]
    skf = StratifiedKFold(n_splits=5, shuffle=True, random_state=RANDOM_STATE)
    best = (-1, 1e-9, 1.0)
    for vs in vs_grid:
        for a in alpha_grid:
            m = MixedNB(n_cont, mincat, var_smoothing=vs, cat_alpha=a)
            sc = cross_val_score(m, Xtr, y_tr, cv=skf, scoring="f1_macro").mean()
            if sc > best[0]:
                best = (sc, vs, a)
    _, best_vs, best_a = best
    model = MixedNB(n_cont, mincat, var_smoothing=best_vs, cat_alpha=best_a).fit(Xtr, y_tr)
    y_pred = model.predict(Xte)
    print(f"\n[{tag}] MixedNB best: var_smoothing={best_vs}, cat_alpha={best_a}, CV_f1_macro={best[0]:.4f}")
    res = evaluate(f"MixedNB ({tag})", y_te, y_pred, CLASS_NAMES)
    res.update({"cv_f1_macro": round(best[0], 4), "var_smoothing": best_vs,
                "cat_alpha": best_a, "n_features": len(cols)})
    return model, cols, res, y_pred

# ---------------------------------------------------------------------
# 6. TIER-1 (DEPLOYED) & TIER-FULL (PEMBANDING)
# ---------------------------------------------------------------------
cont_t1 = [c for c in CONTINUOUS_ALL if c not in TIER2]     # Usia, BMI
disc_t1 = [c for c in DISCRETE_ALL   if c not in TIER2]     # 17 fitur
print("\n" + "-" * 68); print("TIER-1 (self-report, DIDEPLOY):", cont_t1 + disc_t1)
model_t1, cols_t1, res_t1, ypred_t1 = run_mixed_nb("Tier-1", cont_t1, disc_t1)
results.append(res_t1)

print("\n" + "-" * 68); print("TIER-FULL (26 fitur, pembanding):")
model_tf, cols_tf, res_tf, _ = run_mixed_nb("Tier-Full", CONTINUOUS_ALL, DISCRETE_ALL)
results.append(res_tf)

# ---------------------------------------------------------------------
# 7. MODEL PEMBANDING (pada set fitur Tier-1)
# ---------------------------------------------------------------------
print("\n" + "-" * 68); print("PEMBANDING (fitur Tier-1):")
Xtr_t1, _ = build_matrix(df_tr, cont_t1, disc_t1)
Xte_t1, _ = build_matrix(df_te, cont_t1, disc_t1)

# (a) GaussianNB-all: perlakukan semua fitur sebagai kontinu
gnb_all = GaussianNB().fit(Xtr_t1, y_tr)
results.append(evaluate("GaussianNB-all (Tier-1)", y_te, gnb_all.predict(Xte_t1), CLASS_NAMES))

# (b) CategoricalNB-binned: diskretisasi kontinu -> semua kategori
kbin = KBinsDiscretizer(n_bins=5, encode="ordinal", strategy="quantile", subsample=None)
Xtr_cont_bin = kbin.fit_transform(df_tr[cont_t1].values)
Xte_cont_bin = kbin.transform(df_te[cont_t1].values)
Xtr_catall = np.hstack([Xtr_cont_bin, df_tr[disc_t1].values]).astype(int)
Xte_catall = np.hstack([Xte_cont_bin, df_te[disc_t1].values]).astype(int)
mincat_all = [5] * len(cont_t1) + [MINCAT_ALL[c] for c in disc_t1]
cnb_all = CategoricalNB(alpha=1.0, min_categories=mincat_all).fit(Xtr_catall, y_tr)
results.append(evaluate("CategoricalNB-binned (Tier-1)", y_te, cnb_all.predict(Xte_catall), CLASS_NAMES))

# (c) RandomForest (ceiling non-NB)
rf = RandomForestClassifier(n_estimators=200, random_state=RANDOM_STATE, n_jobs=-1).fit(Xtr_t1, y_tr)
results.append(evaluate("RandomForest (Tier-1, ref)", y_te, rf.predict(Xte_t1), CLASS_NAMES))

# (d) XGBoost (ceiling non-NB) - opsional
try:
    from xgboost import XGBClassifier
    xgb = XGBClassifier(n_estimators=300, max_depth=5, learning_rate=0.1,
                        subsample=0.9, eval_metric="mlogloss", random_state=RANDOM_STATE,
                        tree_method="hist", verbosity=0).fit(Xtr_t1, y_tr)
    results.append(evaluate("XGBoost (Tier-1, ref)", y_te, xgb.predict(Xte_t1), CLASS_NAMES))
except Exception as e:
    print("  XGBoost dilewati:", e)

# ---------------------------------------------------------------------
# 8. LAPORAN RINCI MODEL DEPLOY (Tier-1)
# ---------------------------------------------------------------------
print("\n" + "=" * 68); print("  LAPORAN MODEL DEPLOY (Tier-1 MixedNB)"); print("=" * 68)
report = classification_report(y_te, ypred_t1, target_names=CLASS_NAMES, digits=4)
print(report)
cm = confusion_matrix(y_te, ypred_t1)
print("Confusion Matrix (baris=aktual, kolom=prediksi):")
print("        " + "  ".join(f"{c[:6]:>7}" for c in CLASS_NAMES))
for i, row in enumerate(cm):
    print(f"{CLASS_NAMES[i][:7]:>7} " + "  ".join(f"{v:>7}" for v in row))

# ---------------------------------------------------------------------
# 9. EKSPOR MODEL DEPLOY (Tier-1) -> model_web/
# ---------------------------------------------------------------------
OUT = os.path.join(BASE_DIR, "model_web")
os.makedirs(OUT, exist_ok=True)
with open(os.path.join(OUT, "gnb_model.pkl"), "wb") as f: pickle.dump(model_t1.gnb_, f)
with open(os.path.join(OUT, "cnb_model.pkl"), "wb") as f: pickle.dump(model_t1.cnb_, f)
with open(os.path.join(OUT, "label_encoder.pkl"), "wb") as f: pickle.dump(le, f)

metadata = {
    "version": "v3-clinical-tier1",
    "model": "Mixed Naive Bayes (GaussianNB kontinu + CategoricalNB diskret)",
    "task": "Klasifikasi Multiclass Tingkat Keparahan GERD",
    "dataset": "dataset_aslam_clinical (1).csv (12000 baris, sintetis)",
    "classes": CLASS_NAMES,                       # urutan index model (label encoder)
    "severity_order": SEVERITY_ORDER,             # urutan keparahan utk UI
    "continuous_features": cont_t1,               # dikirim ke GaussianNB (urutan wajib)
    "categorical_features": disc_t1,              # dikirim ke CategoricalNB (urutan wajib)
    "feature_order": cont_t1 + disc_t1,           # urutan penuh vektor input (19)
    "min_categories": {c: MINCAT_ALL[c] for c in disc_t1},
    "class_log_prior": model_t1.class_log_prior_.tolist(),
    "var_smoothing": model_t1.gnb_.var_smoothing,
    "cat_alpha": float(model_t1.cnb_.alpha),
    "confidence_threshold": 0.45,
    "feature_encoding": {
        "Usia": "integer tahun (18-75)",
        "BMI": "float; = berat_kg / (tinggi_m^2)",
        "ordinal_0_3": "0=Tidak pernah/Rendah, 1=Kadang/Ringan, 2=Sering/Sedang, 3=Selalu/Tinggi",
        "Stres": "0..3 (Rendah..Sangat tinggi)",
        "Jenis_Kelamin": "0=Perempuan, 1=Laki-laki",
        "Riwayat_Keluarga/Batuk_Kronis": "0=Tidak, 1=Ya",
        "Posisi_Tidur": "0..2",
    },
    "metrics_deploy": res_t1,
    "metrics_comparison": results,
    "disclaimer": ("Model demonstrasi berbasis dataset sintetis. Bukan alat diagnosis "
                   "medis definitif; 'Normal' berarti tidak terindikasi GERD, bukan sehat "
                   "total. Wajib validasi klinis dengan data rekam medis nyata."),
}
with open(os.path.join(OUT, "metadata.json"), "w", encoding="utf-8") as f:
    json.dump(metadata, f, indent=2, ensure_ascii=False)

# hapus artefak ensemble lama yang tak dipakai lagi (sudah dibackup)
for old in ["bnb_model.pkl", "scaler.pkl", "mm_scaler.pkl"]:
    p = os.path.join(OUT, old)
    if os.path.exists(p): os.remove(p)

print("\n" + "=" * 68)
print(f"Model deploy diekspor ke: {OUT}")
print(f"  gnb_model.pkl, cnb_model.pkl, label_encoder.pkl, metadata.json")
print("=" * 68)

# tulis ringkasan metrik ke docs
docs_dir = os.path.join(BASE_DIR, "docs"); os.makedirs(docs_dir, exist_ok=True)
with open(os.path.join(docs_dir, "model_metrics.md"), "w", encoding="utf-8") as f:
    f.write("# StomaCare - Metrik Model (Mixed Naive Bayes, Keparahan GERD)\n\n")
    f.write(f"Dataset: `dataset_aslam_clinical (1).csv` ({df.shape[0]} baris). ")
    f.write(f"Kelas: {', '.join(CLASS_NAMES)}. Split 80/20 stratified.\n\n")
    f.write("## Perbandingan Model (test set, fitur Tier-1 kecuali disebut)\n\n")
    f.write("| Model | Accuracy | F1-macro | F1-weighted |\n|---|---|---|---|\n")
    for r in results:
        f.write(f"| {r['model']} | {r['accuracy']} | {r['f1_macro']} | {r['f1_weighted']} |\n")
    f.write(f"\n**Model dideploy:** MixedNB Tier-1 (19 fitur self-report), "
            f"var_smoothing={metadata['var_smoothing']}, cat_alpha={metadata['cat_alpha']}, "
            f"CV F1-macro={res_t1['cv_f1_macro']}.\n\n")
    f.write("## Classification Report (model deploy)\n\n```\n" + report + "\n```\n\n")
    f.write("## Confusion Matrix (model deploy)\n\n```\n")
    f.write("        " + "  ".join(f"{c[:6]:>7}" for c in CLASS_NAMES) + "\n")
    for i, row in enumerate(cm):
        f.write(f"{CLASS_NAMES[i][:7]:>7} " + "  ".join(f"{v:>7}" for v in row) + "\n")
    f.write("```\n")
print("Ringkasan metrik -> docs/model_metrics.md")
