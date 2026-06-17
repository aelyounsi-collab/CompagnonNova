# CompagnonNova — HyperFrames

Moteur de création vidéo HTML → MP4 pour la chaîne CompagnonNova (chiens & chats).

## Rôle dans le projet CompagnonNova

| Outil | Rôle |
|---|---|
| `C:\xampp\htdocs\compagnonnova-dashboard` | Dashboard analytics (PHP/XAMPP) |
| `C:\CompagnonNova\CompagnonNova-HyperFrames` | Création vidéo (HyperFrames/Node.js) |

Les deux projets sont **complètement indépendants** — ne jamais mélanger leurs fichiers.

## Commandes principales

```bash
# Lancer le serveur de prévisualisation
npx hyperframes dev

# Prévisualiser un template spécifique
npx hyperframes dev templates/sante/gencives-chien.html

# Exporter une vidéo MP4
npx hyperframes render templates/sante/gencives-chien.html --output exports/gencives-chien.mp4

# Exporter en 9:16 (Reels / TikTok / Shorts)
npx hyperframes render templates/sante/gencives-chien.html \
  --width 1080 --height 1920 \
  --output exports/gencives-chien-916.mp4
```

## Workflow vidéo

```
1. Créer le template HTML dans templates/<categorie>/
2. Prévisualiser avec : npx hyperframes dev
3. Ajuster animations, textes, couleurs
4. Exporter : npx hyperframes render ... --output exports/
5. Publier sur YouTube Shorts / Instagram Reels / TikTok
```

## Structure du projet

```
CompagnonNova-HyperFrames/
├── assets/
│   ├── images/        # Visuels, photos d'animaux
│   ├── logos/         # Logo CompagnonNova
│   └── audio/         # Musiques de fond, effets sonores
├── templates/
│   ├── sante/         # Santé animale (gencives, symptômes...)
│   ├── prevention/    # Prévention (vaccins, parasites...)
│   ├── urgences/      # Urgences vétérinaires
│   └── alimentation/  # Nutrition chiens & chats
├── videos/            # Vidéos sources / B-roll
├── exports/           # MP4 finaux prêts à publier
├── docs/              # Documentation interne
├── index.html         # Composition principale HyperFrames
├── hyperframes.json   # Config du projet
└── meta.json          # Métadonnées
```

## Identité visuelle CompagnonNova

| Couleur | Code | Usage |
|---|---|---|
| Navy | `#0B0F1A` | Fond principal |
| Doré | `#C9A84C` | Accents, titres, CTA |
| Blanc | `#FFFFFF` | Texte principal |
| Rouge alerte | `#E63946` | Urgences, dangers |

- Format vidéo : **1080×1920px (9:16)** pour Shorts/Reels/TikTok
- Durée cible : **30 à 60 secondes**
- Police : Segoe UI / system-ui

## Templates disponibles

| Fichier | Sujet | Catégorie |
|---|---|---|
| `templates/sante/gencives-chien.html` | Les gencives révèlent une maladie grave | Santé |

## Prochaines étapes recommandées

1. Ajouter le logo CompagnonNova dans `assets/logos/`
2. Créer des templates pour chaque catégorie
3. Configurer une musique de fond dans `assets/audio/`
4. Exporter les vidéos et les uploader sur les plateformes
