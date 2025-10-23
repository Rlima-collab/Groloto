# Système d'Inscription des Mécènes aux Événements

## Vue d'ensemble

Ce système permet aux mécènes de s'inscrire aux événements en proposant des dons (lots, services, etc.). Les inscriptions sont ensuite validées par les administrateurs.

## Fonctionnalités

### Pour les Mécènes

1. **S'inscrire à un événement** (`/mecene/inscription`)
   - Sélection de l'événement dans une liste déroulante
   - Informations personnelles pré-remplies automatiquement
   - Description du don proposé
   - Valeur estimée (optionnelle)
   - Remarques complémentaires

2. **Voir mes inscriptions** (`/mecene/mes-inscriptions`)
   - Liste de toutes les inscriptions
   - Statut de chaque inscription (en attente, acceptée, refusée)
   - Détails de chaque don proposé

### Interface Utilisateur

- **Page Événements** : Le bouton "Voir le planning" est remplacé par "S'inscrire à un événement" pour les mécènes
- **Menu Navigation** : Ajout du lien "Mes Inscriptions" visible uniquement pour les mécènes (non-admin)

## Structure Technique

### Entité : InscriptionMecene

```
- id: identifiant unique
- mecene: relation vers le mécène
- evenement: relation vers l'événement
- description_don: description du don proposé
- montant_estime: valeur estimée du don
- statut: en_attente | accepte | refuse
- date_inscription: date de soumission
- remarques: notes complémentaires
```

### Routes

- `GET/POST /mecene/inscription` : Formulaire d'inscription
- `GET /mecene/mes-inscriptions` : Liste des inscriptions du mécène

### Sécurité

- Accès réservé aux utilisateurs avec le rôle `ROLE_MECENE`
- Les informations du mécène sont automatiquement récupérées depuis l'utilisateur connecté
- Validation des données du formulaire (description obligatoire, montant positif)

## Base de Données

La table `INSCRIPTION_MECENE` a été ajoutée avec :
- Clés étrangères vers MECENE et EVENEMENT
- Contrainte CHECK sur le statut
- Horodatage automatique de la date d'inscription

## Prochaines étapes possibles

1. **Interface Admin** : Créer une page pour que les admins puissent valider/refuser les inscriptions
2. **Notifications** : Envoyer des emails lors des changements de statut
3. **Dashboard** : Afficher les statistiques d'inscriptions sur le tableau de bord
4. **Historique** : Garder une trace des modifications de statut

## Utilisation

1. Se connecter en tant que mécène
2. Aller sur la page "Événements"
3. Cliquer sur "S'inscrire à un événement"
4. Remplir le formulaire et soumettre
5. Consulter "Mes Inscriptions" pour suivre le statut
