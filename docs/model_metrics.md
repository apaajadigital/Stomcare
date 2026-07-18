# StomaCare - Metrik Model (Mixed Naive Bayes, Keparahan GERD)

Dataset: `dataset_aslam_clinical (1).csv` (12000 baris). Kelas: GERD Berat, GERD Ringan, GERD Sedang, Komplikasi, Normal. Split 80/20 stratified.

## Perbandingan Model (test set, fitur Tier-1 kecuali disebut)

| Model | Accuracy | F1-macro | F1-weighted |
|---|---|---|---|
| MixedNB (Tier-1) | 0.8579 | 0.8578 | 0.8578 |
| MixedNB (Tier-Full) | 0.9567 | 0.9565 | 0.9565 |
| GaussianNB-all (Tier-1) | 0.8488 | 0.8488 | 0.8488 |
| CategoricalNB-binned (Tier-1) | 0.8538 | 0.8534 | 0.8534 |
| RandomForest (Tier-1, ref) | 0.8296 | 0.8292 | 0.8292 |
| XGBoost (Tier-1, ref) | 0.8337 | 0.8334 | 0.8334 |

**Model dideploy:** MixedNB Tier-1 (19 fitur self-report), var_smoothing=1e-09, cat_alpha=0.1, CV F1-macro=0.8604.

## Classification Report (model deploy)

```
              precision    recall  f1-score   support

  GERD Berat     0.7462    0.7229    0.7344       480
 GERD Ringan     0.9079    0.9042    0.9061       480
 GERD Sedang     0.8723    0.8542    0.8632       480
  Komplikasi     0.7949    0.8396    0.8166       480
      Normal     0.9688    0.9688    0.9688       480

    accuracy                         0.8579      2400
   macro avg     0.8580    0.8579    0.8578      2400
weighted avg     0.8580    0.8579    0.8578      2400

```

## Confusion Matrix (model deploy)

```
         GERD B   GERD R   GERD S   Kompli   Normal
GERD Be     347        0       29      104        0
GERD Ri       0      434       31        0       15
GERD Se      41       29      410        0        0
Komplik      77        0        0      403        0
 Normal       0       15        0        0      465
```
