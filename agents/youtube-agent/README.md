# Agent YouTube — CompagnonNova

## Mission

Analyser automatiquement les performances de la chaîne YouTube CompagnonNova et produire des recommandations actionnables.

---

## Données d'entrée

L'agent analyse les exports disponibles dans `analytics/youtube/exports/` :

| Donnée | Source | Format |
|--------|--------|--------|
| Vues par vidéo | YouTube Studio | CSV / JSON |
| Abonnés | YouTube Studio | CSV |
| CTR miniatures | YouTube Studio | CSV |
| Rétention par vidéo | YouTube Studio | CSV |
| Watch time | YouTube Studio | CSV |
| Sources de trafic | YouTube Studio | CSV |
| Mots-clés | YouTube Search Console | CSV |

---

## Analyses à produire

### 1. Performance générale
- [ ] Vues totales vs période précédente
- [ ] Croissance des abonnés
- [ ] Watch time total et moyen
- [ ] Tendance algorithmique (impressions)

### 2. CTR & Miniatures
- [ ] CTR moyen global
- [ ] Top 3 CTR et bottom 3 CTR
- [ ] Patterns des miniatures performantes (texte, émotion, personnage)
- [ ] Recommandations miniatures

### 3. Rétention
- [ ] Taux de rétention moyen par type de contenu
- [ ] Points de décrochage communs
- [ ] Meilleures introductions (< 30 sec)
- [ ] Durée optimale par thème

### 4. Sources de trafic
- [ ] Répartition des sources
- [ ] Mots-clés qui amènent du trafic
- [ ] Opportunités SEO non exploitées
- [ ] Vidéos liées qui génèrent du trafic entrant

### 5. Mots-clés & SEO
- [ ] Mots-clés générant le plus de trafic
- [ ] Mots-clés à fort potentiel non couverts
- [ ] Analyse des titres performants
- [ ] Optimisations de descriptions

### 6. Vidéos performantes
- [ ] Top 10 toutes métriques confondues
- [ ] Top 10 en abonnés générés
- [ ] Top 10 en watch time
- [ ] Patterns communs

---

## Sorties produites

| Sortie | Destination |
|--------|-------------|
| Rapport mensuel | `analytics/youtube/rapports/YYYY-MM.md` |
| Top vidéos | `analytics/youtube/tops-videos/` |
| Opportunités SEO | `analytics/youtube/mots-cles/opportunites.md` |
| Recommandations | `analytics/youtube/recommandations/YYYY-MM.md` |
| Contribution KPI global | `analytics/kpi-global.md` |

---

## Prompts IA utilisés

Voir `prompts/claude/prompt-analyse-youtube.md`

---

## Automatisation

**Phase 1** (manuelle) : Export manuel → Analyse Claude → Rapport
**Phase 2** (semi-auto) : Export auto via API YouTube → Analyse Claude → Rapport
**Phase 3** (complète) : Pipeline n8n complet → Rapport auto + alertes

Voir `automatisations/n8n/` pour les workflows.
