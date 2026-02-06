# Gestion des Membres - Page Admin

## Vue d'ensemble

La page **Membres** dans l'interface administrateur permet de gérer les données de membres via import/export Excel.

## Fonctionnalités

### 1. **Import Excel (.xlsx)**
- Sélectionnez un fichier Excel au format `.xlsx`
- Le système affiche un aperçu des données avant import
- Confirmez pour importer les données dans la base de données

**Format attendu pour les colonnes Excel:**
| Colonne | Nom |
|---------|-----|
| A | Nom |
| B | Prénom |
| C | Email |
| D | Numéro Billet |
| E | Tarif |
| F | Date Création |
| G | Date Séance |
| H | Montant Tarif |
| I | Code Promo |
| J | Montant Code Promo |

### 2. **Affichage des Données**
- Liste complète de tous les membres importés
- Tableau avec tous les détails (Nom, Prénom, Email, etc.)
- Affichage des statistiques (Total des membres)

### 3. **Export Excel**
- Clic sur le bouton "Exporter en Excel"
- Télécharge tous les membres au format `.xlsx`
- Nom du fichier: `membres_YYYY-MM-DD_HH-MM-SS.xlsx`

### 4. **Suppression de Membre**
- Supprimez un membre via le bouton action dans le tableau
- Confirmation de sécurité obligatoire

## Accès

La page est accessible uniquement par les administrateurs:
- URL: `/admin/membres`
- Rôle requis: `ROLE_ADMIN`

## Fichier d'Exemple

Un fichier d'exemple est disponible à:
```
/public/exemples/membres_exemple.xlsx
```

Vous pouvez le télécharger et l'utiliser comme modèle pour vos imports.

## Gestion des Doublons

Lors de l'import:
- Si l'**email existe déjà**, les données existantes sont **mises à jour**
- Si l'**email est nouveau**, un **nouveau membre** est créé

## Données Stockées

Chaque membre a les champs suivants:
- **nom**: Nom du membre (requis)
- **prenom**: Prénom du membre (requis)
- **email**: Adresse email (requis, unique)
- **numero_billet**: Numéro du billet
- **tarif**: Type de tarif
- **date_creation**: Date de création de l'enregistrement
- **date_seance**: Date de la séance
- **montant_tarif**: Montant en € du tarif
- **code_promo**: Code promotionnel appliqué
- **montant_code_promo**: Montant de la réduction

## Flux de Travail Complet

1. **Préparation**: Préparez votre fichier Excel avec les données correctes
2. **Import**: Allez sur `/admin/membres` et sélectionnez le fichier
3. **Aperçu**: Vérifiez les données affichées
4. **Confirmation**: Confirmez l'import
5. **Vérification**: La liste se met à jour automatiquement
6. **Export**: Exportez les données à tout moment en Excel

## Notes Importantes

- Les dates doivent être au format `JJ/MM/AAAA` ou `YYYY-MM-DD`
- L'email est l'identifiant unique (ne peut pas avoir de doublons)
- Tous les champs sauf Nom, Prénom et Email sont optionnels
- L'interface détecte et parse automatiquement les formats de dates Excel
