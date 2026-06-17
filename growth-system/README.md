# Growth System — CompagnonNova

> Système de pilotage éditorial basé sur les données. CompagnonNova fonctionne comme un média piloté par la donnée.

---

## Principe fondamental

**Chaque décision éditoriale est justifiée par des données.**

Pas de sujets choisis au hasard. Pas de formats décidés à l'instinct. Chaque contenu produit répond à un signal mesuré : vues, partages, complétion, sauvegardes, commentaires.

---

## Cycle hebdomadaire — Processus standard

```
LUNDI — Collecte
│
├── 1. Déposer les exports dans analytics/[plateforme]/exports/
├── 2. Lancer les agents IA (Claude + prompts dans prompts/claude/)
└── 3. Générer les rapports dans analytics/[plateforme]/rapports/

MARDI — Analyse
│
├── 4. Identifier les sujets performants (top vidéos, hooks, CTA)
├── 5. Mettre à jour editorial-decisions/
└── 6. Compléter content-intelligence/ avec les patterns

MERCREDI — Décision
│
├── 7. Rédiger la weekly-review dans weekly-reviews/
├── 8. Alimenter le calendrier éditorial (calendrier-editorial/)
└── 9. Prioriser les sujets à produire cette semaine

JEUDI—VENDREDI — Production
│
├── 10. Produire les scripts selon les priorités validées
├── 11. Générer les vidéos (HeyGen)
└── 12. Préparer les packs de publication
```

---

## Cycle mensuel

```
1er du mois — Bilan
│
├── Lancer l'Agent Growth (prompts/claude/prompt-growth-global.md)
├── Générer le rapport growth global (templates/analytics/rapport-growth-global.md)
├── Mettre à jour analytics/kpi-global.md
├── Archiver la monthly-review dans monthly-reviews/archives/
└── Mettre à jour ROADMAP.md et docs/growth-lab.md
```

---

## Architecture du système

```
growth-system/
├── weekly-reviews/        → Bilans hebdomadaires
├── monthly-reviews/       → Bilans mensuels
├── editorial-decisions/   → Décisions éditoriales basées sur les données
├── content-intelligence/  → Base de connaissance des patterns performants
└── experiments/           → Tests A/B et expérimentations par plateforme
```

---

## Connexions avec le reste du repository

| Ce système utilise | Pour |
|--------------------|------|
| `analytics/` | Données brutes et rapports |
| `agents/` | Analyse automatique |
| `prompts/claude/` | Génération des rapports |
| `calendrier-editorial/` | Décisions de planification |
| `guides/priorites.md` | Priorisation des sujets |
| `knowledge-base/` | Signaux issus de la communauté |
| `templates/analytics/` | Formats de rapports |

---

## Règles du système

1. **Aucun sujet produit sans justification data** (sauf test expérimental documenté)
2. **Un pattern validé = immédiatement ajouté** à `content-intelligence/`
3. **Un sujet qui échoue 3 fois** → ajouté à `editorial-decisions/sujets-a-eviter/`
4. **Les commentaires communauté** sont systématiquement capturés dans `knowledge-base/`
5. **La weekly-review** est non négociable : elle alimente tout le reste
