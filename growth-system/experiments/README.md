# Experiments — CompagnonNova

> Système de tests A/B et expérimentations éditoriales.

## Principe

Chaque expérience suit le processus :
```
Hypothèse → Test → Mesure → Conclusion → Décision
```

## Convention de nommage

```
EXP-[PLATEFORME]-[NUMÉRO]-[SLUG].md
Ex: EXP-TK-001-hook-stop-vs-question.md
```

## Template d'expérience

```markdown
# EXP-[PLATEFORME]-[NUM] — [TITRE]

**Date de début** :
**Date de fin prévue** :
**Hypothèse** : Si [A], alors [B], parce que [raison]
**Variable testée** :
**Groupe A** :
**Groupe B** :
**Métrique de succès** :
**Seuil de validation** :

## Résultats

| Variable | Groupe A | Groupe B |
|---------|----------|----------|
| [métrique] | | |

## Conclusion

**Résultat** : ✅ Validée / ❌ Infirmée / ⚠️ Partielle
**Action décidée** :
**Documenté dans** : `growth-system/editorial-decisions/`
```
