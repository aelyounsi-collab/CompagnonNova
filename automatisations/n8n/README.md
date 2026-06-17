# Automatisations n8n — CompagnonNova

## Workflows planifiés

### Workflow 1 : Génération de script
- **Trigger** : Nouveau sujet dans le calendrier éditorial
- **Actions** : Appel API Claude → Génération script → Sauvegarde GitHub
- **Statut** : Planifié

### Workflow 2 : Rapport analytics
- **Trigger** : 1er de chaque mois
- **Actions** : Collecte stats API YouTube/TikTok → Synthèse → Rapport Markdown
- **Statut** : Planifié

### Workflow 3 : Alerte performance
- **Trigger** : Vidéo dépasse X vues en 24h
- **Actions** : Notification + Extraction extraits courts
- **Statut** : Planifié
