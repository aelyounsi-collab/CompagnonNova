# 📊 CompagnonNova Dashboard — XAMPP Local

Dashboard analytics local pour suivre les performances CompagnonNova sur toutes les plateformes.

## 🚀 Installation avec XAMPP

### Prérequis
- [XAMPP](https://www.apachefriends.org/) installé (Windows, Mac ou Linux)
- PHP 8.1+

### Étapes d'installation

1. **Télécharger / cloner** ce dossier :
   ```
   git clone https://github.com/aelyounsi-collab/compagnonnova.git
   ```

2. **Copier le dossier dashboard** dans htdocs XAMPP :
   ```
   C:/xampp/htdocs/compagnonnova-dashboard/
   ```
   *(Mac/Linux : `/opt/lampp/htdocs/compagnonnova-dashboard/`)*

3. **Démarrer Apache** dans le panneau XAMPP.

4. **Ouvrir le dashboard** dans votre navigateur :
   ```
   http://localhost/compagnonnova-dashboard/
   ```

## 📁 Structure du dossier

```
compagnonnova-dashboard/
├── index.php                  ← Vue globale (page d'accueil)
├── config.php                 ← Configuration & fonctions PHP partagées
├── pages/
│   ├── youtube.php            ← Analytics YouTube
│   ├── tiktok.php             ← Analytics TikTok
│   ├── instagram.php          ← Analytics Instagram
│   ├── facebook.php           ← Analytics Facebook
│   └── growth.php             ← Growth Lab & expérimentations
├── assets/
│   ├── css/style.css          ← Thème dark CompagnonNova
│   └── js/charts.js           ← Helpers Chart.js
├── data/
│   ├── youtube.json           ← Données YouTube
│   ├── tiktok.json            ← Données TikTok
│   ├── instagram.json         ← Données Instagram
│   ├── facebook.json          ← Données Facebook
│   └── growth-global.json     ← Vue consolidée cross-platform
└── README.md
```

## 📝 Mise à jour des données

Modifiez les fichiers dans `data/` pour mettre à jour les statistiques affichées.

### Format attendu par les agents IA

Les agents Claude peuvent écrire directement dans `/data/*.json` selon ce protocole :

```json
{
  "meta": { "period": "Mois Année", "updated": "YYYY-MM-DD" },
  "subscribers": { "total": 0, "new": 0, "change_pct": 0.0 }
}
```

Chaque fichier JSON suit le même schéma documenté dans les analytics agents (`/agents/`).

## 🎨 Identité visuelle

| Couleur      | Hex       | Usage             |
|-------------|-----------|-------------------|
| Navy         | `#0B0F1A` | Fond principal    |
| Navy Card    | `#141928` | Cartes & panneaux |
| Gold         | `#C9A84C` | Accents & titres  |
| Green        | `#2D6A4F` | Badges positifs   |
| White        | `#FFFFFF` | Texte principal   |

## 🤖 Mise à jour automatique par les agents

Les agents IA du dossier `/agents/` peuvent mettre à jour les fichiers JSON.
Nommer chaque fichier selon le schéma `data/{platform}.json` et respecter les clés existantes.
