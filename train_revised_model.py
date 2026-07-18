# STANDALONE TRAINING SCRIPT FOR STOMACARE REVISED MODEL
# BernoulliNB + GaussianNB + ComplementNB Ensemble

import pandas as pd
import numpy as np
import warnings
from sklearn.model_selection import train_test_split, StratifiedKFold, cross_val_score
from sklearn.preprocessing import LabelEncoder, StandardScaler, MinMaxScaler
from sklearn.naive_bayes import GaussianNB, ComplementNB, BernoulliNB
from sklearn.metrics import accuracy_score, f1_score
import pickle
import json as js
import os

warnings.filterwarnings('ignore')

# === TAHAP 1: DATA ACQUISITION & PREPARATION ===
data_dir = 'disease-prediction-using-machine-learning'
df_train = pd.read_csv(f'{data_dir}/Training.csv')
df_test  = pd.read_csv(f'{data_dir}/Testing.csv')
df_train.drop(columns=['Unnamed: 133'], errors='ignore', inplace=True)
df_test.drop(columns=['Unnamed: 133'],  errors='ignore', inplace=True)
df_all = pd.concat([df_train, df_test], ignore_index=True)

# === TAHAP 2: DEFINISI FITUR ===
SELECTED_FEATURES = [
    'stomach_pain', 'acidity', 'indigestion', 'abdominal_pain', 'belly_pain',
    'passage_of_gases', 'diarrhoea', 'bloody_stool', 'irritation_in_anus',
    'pain_in_anal_region', 'pain_during_bowel_movements', 'high_fever',
    'chills', 'toxic_look_(typhos)', 'fatigue', 'itching', 'internal_itching',
    'headache', 'ulcers_on_tongue', 'cough'
]

ENGINEERED_FEATURES = [
    'GERD_Indicator', 'Infection_Indicator', 'Digestive_Distress', 'Gastritis_Indicator'
]

ALL_FEATURES = SELECTED_FEATURES + ENGINEERED_FEATURES

# === TAHAP 3: MAPPING KE 5 KELAS TARGET ===
disease_map = {
    'GERD': 'GERD',
    'Peptic ulcer diseae': 'Tukak Lambung',
    'Peptic ulcer disease': 'Tukak Lambung',
    'Gastroenteritis': 'Gastritis',
    'Chronic cholestasis': 'Dispepsia',
    'Drug Reaction': 'Dispepsia',
    'Jaundice': 'Dispepsia',
    'hepatitis A': 'Gastritis',
    'Alcoholic hepatitis': 'Gastritis',
    'Typhoid': 'Tukak Lambung',
    'Dimorphic hemmorhoids(piles)': 'Dispepsia',
}

normal_diseases = [d for d in df_all['prognosis'].unique() if d not in disease_map]
df_gi   = df_all[df_all['prognosis'].isin(disease_map.keys())].copy()
df_gi['label'] = df_gi['prognosis'].map(disease_map)
df_norm = df_all[df_all['prognosis'].isin(normal_diseases)].copy()
df_norm['label'] = 'Normal'
df = pd.concat([df_gi, df_norm], ignore_index=True)

symptom_cols = [f for f in SELECTED_FEATURES if f in df.columns]

# === TAHAP 3.1: STRATIFIED SPLIT DARI DATA ASLI ===
le = LabelEncoder()
y_all = le.fit_transform(df['label'].values)
CLASS_NAMES = list(le.classes_)
X_all = df[symptom_cols].values.astype(float)

X_train_raw, X_test_raw, y_train_raw, y_test_raw = train_test_split(
    X_all, y_all,
    test_size=0.20,
    random_state=42,
    stratify=y_all
)

# === TAHAP 3.5: FEATURE ENGINEERING ===
def add_engineered_features(X_array, base_feature_names):
    X_df = pd.DataFrame(X_array, columns=base_feature_names)
    def get(col):
        return X_df[col] if col in X_df.columns else pd.Series(0, index=X_df.index)
    X_df['GERD_Indicator']      = get('stomach_pain')   * get('acidity')
    X_df['Infection_Indicator'] = get('high_fever')     * get('chills')
    X_df['Digestive_Distress']  = get('abdominal_pain') * get('passage_of_gases')
    X_df['Gastritis_Indicator'] = get('diarrhoea')      * get('belly_pain')
    return X_df[ALL_FEATURES].values

X_train_feat = add_engineered_features(X_train_raw, symptom_cols)
X_test_feat  = add_engineered_features(X_test_raw,  symptom_cols)

# === TAHAP 9: NOISE AUGMENTATION HANYA PADA TRAINING SET ===
def noise_augment_class(X_class, n_target, flip_prob=0.07, seed=42):
    rng = np.random.default_rng(seed)
    needed = n_target - len(X_class)
    if needed <= 0:
        return X_class
    indices = rng.integers(0, len(X_class), size=needed)
    X_new = X_class[indices].copy().astype(float)
    mask = rng.random(X_new.shape) < flip_prob
    X_new[mask] = 1.0 - X_new[mask]
    return np.vstack([X_class, X_new])

TARGET_PER_CLASS = 2500
X_parts, y_parts = [], []
for cls_idx, cls_name in enumerate(CLASS_NAMES):
    mask_cls = (y_train_raw == cls_idx)
    X_cls = X_train_feat[mask_cls]
    n_orig = len(X_cls)
    if n_orig < TARGET_PER_CLASS:
        X_aug = noise_augment_class(X_cls, TARGET_PER_CLASS,
                                    flip_prob=0.07, seed=42 + cls_idx)
    else:
        X_aug = X_cls
    X_parts.append(X_aug)
    y_parts.append(np.full(len(X_aug), cls_idx))

X_train_bal = np.vstack(X_parts).astype(float)
y_train_bal = np.concatenate(y_parts)

# Scalers
scaler     = StandardScaler()
X_train_sc = scaler.fit_transform(X_train_bal)
X_test_sc  = scaler.transform(X_test_feat)

mm_scaler  = MinMaxScaler()
X_train_mm = mm_scaler.fit_transform(X_train_bal)
X_test_mm  = mm_scaler.transform(X_test_feat)

# === TAHAP 10: BERNOULLI NAIVE BAYES (BASELINE) ===
alpha_grid_bnb = [0.001, 0.01, 0.05, 0.1, 0.2, 0.3, 0.5, 0.7, 1.0, 1.5, 2.0, 5.0]
best_f1_bnb, best_alpha_bnb = 0, 0.1
skf_bnb = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)

for alpha in alpha_grid_bnb:
    bnb = BernoulliNB(alpha=alpha, binarize=0.0)
    scores = cross_val_score(bnb, X_train_bal, y_train_bal,
                             cv=skf_bnb, scoring='f1_macro')
    if scores.mean() > best_f1_bnb:
        best_f1_bnb, best_alpha_bnb = scores.mean(), alpha

bnb_model = BernoulliNB(alpha=best_alpha_bnb, binarize=0.0)
bnb_model.fit(X_train_bal, y_train_bal)
y_pred_bnb = bnb_model.predict(X_test_feat)


# === TAHAP 11: GAUSSIAN NAIVE BAYES ===
vs_grid = [1e-12,1e-11,1e-10,1e-9,5e-9,1e-8,5e-8,1e-7,5e-7,
           1e-6,5e-6,1e-5,5e-5,1e-4,5e-4,1e-3,5e-3,1e-2,5e-2,0.1,0.5,1.0]
best_f1_gnb, best_vs = 0, 1e-9
skf_gnb = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)

for vs in vs_grid:
    gnb = GaussianNB(var_smoothing=vs)
    scores = cross_val_score(gnb, X_train_sc, y_train_bal,
                             cv=skf_gnb, scoring='f1_macro')
    if scores.mean() > best_f1_gnb:
        best_f1_gnb, best_vs = scores.mean(), vs

gnb_model = GaussianNB(var_smoothing=best_vs)
gnb_model.fit(X_train_sc, y_train_bal)

# === TAHAP 12: COMPLEMENT NAIVE BAYES ===
alpha_grid_cnb = [0.001,0.005,0.01,0.05,0.1,0.2,0.3,0.5,0.7,
                  1.0,1.5,2.0,3.0,5.0,7.0,10.0]
best_f1_cnb, best_alpha_cnb = 0, 1.0
skf_cnb = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)

for alpha in alpha_grid_cnb:
    cnb = ComplementNB(alpha=alpha)
    scores = cross_val_score(cnb, X_train_mm, y_train_bal,
                             cv=skf_cnb, scoring='f1_macro')
    if scores.mean() > best_f1_cnb:
        best_f1_cnb, best_alpha_cnb = scores.mean(), alpha

cnb_model = ComplementNB(alpha=best_alpha_cnb)
cnb_model.fit(X_train_mm, y_train_bal)

# === TAHAP 13: OPTIMIZED 3-MODEL ENSEMBLE WEIGHTS ===
skf_ens = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)
best_w1, best_w2, best_w3 = 0.4, 0.4, 0.2
best_f1_ens = 0

weight_options = [0.1, 0.2, 0.3, 0.4, 0.5]
for w1 in weight_options:
    for w2 in weight_options:
        w3 = round(1.0 - w1 - w2, 1)
        if w3 < 0.1 or w3 > 0.8:
            continue
        fold_f1s = []
        for tr_idx, val_idx in skf_ens.split(X_train_bal, y_train_bal):
            ens_p = w1 * bnb_model.predict_proba(X_train_bal[val_idx]) + \
                    w2 * gnb_model.predict_proba(X_train_sc[val_idx]) + \
                    w3 * cnb_model.predict_proba(X_train_mm[val_idx])
            fold_f1s.append(f1_score(y_train_bal[val_idx],
                                     ens_p.argmax(axis=1), average='macro'))
        avg = np.mean(fold_f1s)
        if avg > best_f1_ens:
            best_f1_ens, best_w1, best_w2, best_w3 = avg, w1, w2, w3

# Evaluate on test set
bnb_proba      = bnb_model.predict_proba(X_test_feat)
gnb_proba      = gnb_model.predict_proba(X_test_sc)
cnb_proba      = cnb_model.predict_proba(X_test_mm)
ensemble_proba = best_w1 * bnb_proba + best_w2 * gnb_proba + best_w3 * cnb_proba
y_pred_ensemble = ensemble_proba.argmax(axis=1)

print("="*65)
print("  TRAINING & TUNING COMPLETED")
print("="*65)
print(f"Optimal Weights:")
print(f"  w1 (BernoulliNB)  : {best_w1:.2f}")
print(f"  w2 (GaussianNB)   : {best_w2:.2f}")
print(f"  w3 (ComplementNB) : {best_w3:.2f}")
print(f"Ensemble Test Accuracy    : {accuracy_score(y_test_raw, y_pred_ensemble):.4f}")
print(f"Ensemble Test F1(weighted): {f1_score(y_test_raw, y_pred_ensemble, average='weighted'):.4f}")
print(f"Ensemble Test F1(macro)   : {f1_score(y_test_raw, y_pred_ensemble, average='macro'):.4f}")

# === TAHAP 19: EKSPOR MODEL ===
os.makedirs('model_web', exist_ok=True)
with open('model_web/bnb_model.pkl',      'wb') as f: pickle.dump(bnb_model,   f)
with open('model_web/gnb_model.pkl',      'wb') as f: pickle.dump(gnb_model,   f)
with open('model_web/cnb_model.pkl',      'wb') as f: pickle.dump(cnb_model,   f)
with open('model_web/scaler.pkl',         'wb') as f: pickle.dump(scaler,      f)
with open('model_web/mm_scaler.pkl',      'wb') as f: pickle.dump(mm_scaler,   f)
with open('model_web/label_encoder.pkl',  'wb') as f: pickle.dump(le,          f)

meta = {
    'feature_names'        : ALL_FEATURES,
    'base_features'        : SELECTED_FEATURES,
    'engineered_features'  : ENGINEERED_FEATURES,
    'feature_count'        : len(ALL_FEATURES),
    'model'                : 'Naive Bayes Ensemble (BernoulliNB + GaussianNB + ComplementNB)',
    'augmentation'         : 'Noise Augmentation (bit-flip 7%, bukan SMOTE)',
    'output'               : 'Multi-Class: 5 Kelas Diagnosis Asam Lambung',
    'classes'              : CLASS_NAMES,
    'gnb_var_smoothing'    : float(best_vs),
    'cnb_alpha'            : float(best_alpha_cnb),
    'bnb_alpha'            : float(best_alpha_bnb),
    'ensemble_weight_bnb'  : float(best_w1),
    'ensemble_weight_gnb'  : float(best_w2),
    'ensemble_weight_cnb'  : float(best_w3),
    'confidence_threshold' : 0.60,
    'accuracy_ensemble'    : float(accuracy_score(y_test_raw, y_pred_ensemble)),
    'f1_macro_ensemble'    : float(f1_score(y_test_raw, y_pred_ensemble, average='macro')),
    'f1_weighted_ensemble' : float(f1_score(y_test_raw, y_pred_ensemble, average='weighted')),
    'accuracy_bnb_baseline': float(accuracy_score(y_test_raw, y_pred_bnb)),
    'feature_engineering'  : {
        'GERD_Indicator'     : 'stomach_pain * acidity',
        'Infection_Indicator': 'high_fever * chills',
        'Digestive_Distress' : 'abdominal_pain * passage_of_gases',
        'Gastritis_Indicator': 'diarrhoea * belly_pain',
    },
    'disclaimer': (
        'Model ini adalah demonstrasi konseptual Naive Bayes multi-class. '
        'Validasi klinis dengan data rekam medis nyata diperlukan '
        'sebelum aplikasi diagnosis sesungguhnya.'
    )
}

with open('model_web/metadata.json', 'w') as f: js.dump(meta, f, indent=2)
print("\nModel successfully exported to model_web/")
