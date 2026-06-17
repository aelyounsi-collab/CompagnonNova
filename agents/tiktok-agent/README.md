# Agent TikTok — CompagnonNova

## Mission

Analyser automatiquement les performances du compte TikTok CompagnonNova et identifier les patterns viraux reproductibles.

---

## Données d'entrée

| Donnée | Source | Format |
|--------|--------|--------|
| Vues par vidéo | TikTok Studio | CSV / JSON |
| Taux de complétion | TikTok Studio | CSV |
| Favoris | TikTok Studio | CSV |
| Partages | TikTok Studio | CSV |
| Commentaires | TikTok Studio | CSV |
| Abonnés générés | TikTok Studio | CSV |

---

## Analyses à produire

### 1. Hooks performants
- [ ] Extraire les 3 premières secondes de chaque vidéo
- [ ] Corréler avec le taux de complétion
- [ ] Classer les hooks par performance
- [ ] Identifier les formules gagnantes

**Formules de hooks à évaluer :**
- "STOP ! Si ton [animal] fait ça..."
- "Tu savais que [FAIT CHOC] ?"
- "Ce que 90% des propriétaires ne savent pas"
- "[NOMBRE] signes que ton [animal] est..."
- "Ne jamais donner [ALIMENT] à ton [animal]"

### 2. Formats performants
- [ ] Liste vs révélation vs urgence vs tutoriel
- [ ] Durée optimale (15s / 30s / 60s / 90s)
- [ ] Maely seule vs avec Peanut
- [ ] Sous-titres couleur vs couleur

### 3. Sujets viraux
- [ ] Top 10 sujets par vues
- [ ] Top 10 sujets par partages
- [ ] Top 10 sujets par favoris
- [ ] Sujets performants sur plusieurs plateformes

### 4. Engagement & abonnements
- [ ] Taux d'abonnement par vidéo
- [ ] Commentaires types (questions fréquentes = futurs sujets)
- [ ] Ratio favoris/vues par thème

---

## Sorties produites

| Sortie | Destination |
|--------|-------------|
| Rapport mensuel | `analytics/tiktok/rapports/YYYY-MM.md` |
| Hooks performants | `analytics/tiktok/tops-videos/hooks-performants.md` |
| Sujets viraux | `analytics/tiktok/tops-videos/sujets-viraux.md` |
| Recommandations | `analytics/tiktok/recommandations/YYYY-MM.md` |

---

## Automatisation

**Phase 1** : Export manuel → Analyse Claude → Rapport
**Phase 2** : Pipeline n8n (API TikTok) → Rapport auto
