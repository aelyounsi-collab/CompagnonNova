# Analytics & Growth Intelligence — CompagnonNova

Ce dossier centralise toutes les données de performance, rapports et recommandations de CompagnonNova.

## Architecture

```
analytics/
├── youtube/          → Analyse YouTube (vues, CTR, rétention, mots-clés...)
├── tiktok/           → Analyse TikTok (complétion, favoris, partages...)
├── instagram/        → Analyse Instagram (Reels, sauvegardes, portée...)
├── facebook/         → Analyse Facebook (portée, engagement, vidéos...)
├── growth-lab/       → Hypothèses, tests, conclusions, roadmap
└── kpi-global.md     → Tableau de bord KPI toutes plateformes
```

## Principe de fonctionnement

1. **Exports** : Les données brutes sont déposées dans `exports/`
2. **Rapports** : Les analyses mensuelles sont dans `rapports/`
3. **Tops** : Les meilleures performances sont documentées dans `tops-videos/`
4. **Recommandations** : Les actions concrètes sont dans `recommandations/`

## Agents IA associés

Chaque plateforme dispose d'un agent IA dédié dans `agents/` capable d'analyser automatiquement les données exportées.

Voir `/agents/` pour les spécifications complètes.

## Cadence d'analyse

| Fréquence | Action |
|-----------|--------|
| Hebdomadaire | Mise à jour KPI globaux |
| Mensuelle | Rapport complet par plateforme |
| Trimestrielle | Bilan growth-lab + ajustement stratégie |
