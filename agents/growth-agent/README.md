# Agent Growth — CompagnonNova

## Mission

Centraliser les données de toutes les plateformes, identifier les patterns cross-plateformes et produire une vision stratégique globale de la croissance CompagnonNova.

---

## Données d'entrée

L'Agent Growth agrège les sorties de tous les agents plateforme :

| Source | Fichier |
|--------|--------|
| Agent YouTube | `analytics/youtube/rapports/YYYY-MM.md` |
| Agent TikTok | `analytics/tiktok/rapports/YYYY-MM.md` |
| Agent Instagram | `analytics/instagram/rapports/YYYY-MM.md` |
| Agent Facebook | `analytics/facebook/rapports/YYYY-MM.md` |
| KPI global | `analytics/kpi-global.md` |
| Growth Lab | `analytics/growth-lab/` |

---

## Analyses à produire

### 1. Tableau de bord global
- [ ] KPI consolidés toutes plateformes
- [ ] Croissance globale (abonnés, vues, engagement)
- [ ] Comparaison objectifs vs réalisé
- [ ] Alertes et signaux forts

### 2. Meilleures vidéos cross-plateformes
- [ ] Top contenus par vues cumulées (toutes plateformes)
- [ ] Contenus qui performent simultanément partout
- [ ] Thèmes universellement forts
- [ ] Formats qui fonctionnent sur toutes les plateformes

### 3. Meilleurs sujets
- [ ] Sujets avec le plus fort engagement global
- [ ] Sujets avec la meilleure rétention
- [ ] Sujets avec le plus de partages
- [ ] Sujets à reproduire en priorité

### 4. Meilleurs hooks
- [ ] Agrégation des hooks les plus performants par plateforme
- [ ] Hooks universels (fonctionnent partout)
- [ ] Hooks spécifiques à chaque plateforme
- [ ] Nouvelles formules à tester

### 5. Meilleurs CTA
- [ ] CTA avec le plus de clics
- [ ] CTA qui convertissent en abonnés
- [ ] CTA qui génèrent des partages
- [ ] CTA à systématiser

### 6. Tendances
- [ ] Sujets en progression
- [ ] Sujets en déclin
- [ ] Nouvelles catégories à explorer
- [ ] Saisonnalité identifiée

---

## Sorties produites

| Sortie | Destination |
|--------|-------------|
| Tableau de bord global | `analytics/kpi-global.md` |
| Rapport growth mensuel | `analytics/growth-lab/roadmap/YYYY-MM.md` |
| Meilleurs sujets | `analytics/growth-lab/succes/sujets-top.md` |
| Meilleurs hooks | `analytics/growth-lab/succes/hooks-top.md` |
| Meilleurs CTA | `analytics/growth-lab/succes/cta-top.md` |
| Recommandations globales | `analytics/growth-lab/recommandations-globales/YYYY-MM.md` |

---

## Architecture cible (automatisation)

```
[YouTube API] → [Agent YouTube]
                              \
[TikTok API]  → [Agent TikTok] → [Agent Growth] → [Rapport global]
                              /                  → [Alertes]
[Meta API]    → [Agent Instagram]              → [Recommandations]
              → [Agent Facebook]
```

**Orchestration** : n8n ou workflow Claude Code
**Stockage** : Repository GitHub (ce dossier)
**Alertes** : Notifications sur vidéo virale détectée

---

## Prompts IA utilisés

Voir `prompts/claude/prompt-growth-global.md`

---

## Cadence

| Analyse | Fréquence |
|---------|----------|
| KPI rapide | Hebdomadaire |
| Rapport complet | Mensuel |
| Stratégie growth | Trimestriel |
