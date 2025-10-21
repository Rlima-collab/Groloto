# Fonctionnalité : Modifier mon profil

## 📋 Description

Cette fonctionnalité permet aux utilisateurs connectés de modifier leurs informations personnelles depuis leur page de profil.

## ✨ Caractéristiques

### Pages créées :
1. **Page de profil** (`/profile`) - Affichage des informations de l'utilisateur
2. **Page de modification** (`/profile/edit`) - Formulaire d'édition du profil

### Fonctionnalités :
- ✅ Modification du prénom et nom
- ✅ Modification de l'email (avec validation d'unicité)
- ✅ Modification du téléphone
- ✅ Changement de mot de passe sécurisé
- ✅ Vérification du mot de passe actuel requis
- ✅ Messages flash pour les succès et erreurs
- ✅ Design moderne et responsive
- ✅ Validation des données côté serveur
- ✅ Protection contre les doublons d'email
- ✅ Mise à jour automatique de la date de modification

## 📁 Fichiers créés/modifiés

### Nouveaux fichiers :
```
src/Form/ProfileType.php                  # Formulaire Symfony pour l'édition
templates/auth/profile_edit.html.twig     # Vue de la page d'édition
public/css/profile.css                    # Styles personnalisés
```

### Fichiers modifiés :
```
src/Controller/AuthController.php         # Ajout de la méthode editProfile()
templates/auth/profile.html.twig          # Ajout du bouton "Modifier mon profil"
```

## 🚀 Utilisation

### 1. Accéder au profil
- Se connecter à l'application
- Cliquer sur son nom/email dans le header
- Sélectionner "Mon profil" dans le menu déroulant
- Ou accéder directement à `/profile`

### 2. Modifier le profil
- Sur la page du profil, cliquer sur le bouton **"Modifier mon profil"**
- Remplir le formulaire avec les nouvelles informations
- **Obligatoire** : Entrer le mot de passe actuel
- **Optionnel** : Changer le mot de passe en remplissant les champs dédiés
- Cliquer sur **"Enregistrer les modifications"**

### 3. Validation des données

#### Email :
- Format valide requis
- Doit être unique dans la base de données
- Ne peut pas être vide

#### Mot de passe actuel :
- **Requis** pour valider toute modification
- Doit correspondre au mot de passe actuel

#### Nouveau mot de passe (optionnel) :
- Minimum 6 caractères
- Doit correspondre à la confirmation
- Laissez vide pour conserver le mot de passe actuel

#### Autres champs :
- Prénom, Nom, Téléphone : optionnels

## 🔒 Sécurité

### Mesures de sécurité implémentées :
1. **Authentification requise** : Seuls les utilisateurs connectés peuvent accéder
2. **Vérification du mot de passe** : Le mot de passe actuel est requis pour toute modification
3. **Validation des données** : Validation côté serveur avec Symfony Forms
4. **Protection CSRF** : Intégrée automatiquement par Symfony
5. **Hashage des mots de passe** : Utilisation de `UserPasswordHasherInterface`
6. **Validation d'unicité** : L'email ne peut pas être utilisé par un autre compte

## 🎨 Design

### Caractéristiques visuelles :
- **Gradient moderne** : Header en dégradé violet (#667eea → #764ba2)
- **Badges colorés** : Rôles affichés avec des badges distinctifs
  - Admin : Rouge
  - Bénévole : Bleu
  - Mécène : Vert
- **Animations** : Effets de transition sur les boutons et cartes
- **Responsive** : Adapté mobile, tablette et desktop
- **Alerts stylisées** : Messages de succès/erreur avec dégradés

### CSS Classes principales :
- `.profile-card` : Carte de profil avec ombre et transitions
- `.profile-header` : Header avec gradient violet
- `.role-badge` : Badge pour le rôle utilisateur
- `.btn-profile-primary` : Bouton principal avec gradient
- `.profile-alert` : Alertes avec dégradés colorés

## 📊 Workflow technique

```mermaid
graph TD
    A[Utilisateur connecté] --> B[/profile]
    B --> C{Voir profil}
    C --> D[Cliquer "Modifier"]
    D --> E[/profile/edit]
    E --> F{Remplir formulaire}
    F --> G[Validation côté serveur]
    G --> H{Données valides?}
    H -->|Oui| I[Vérifier mot de passe]
    I --> J{Mot de passe correct?}
    J -->|Oui| K[Vérifier unicité email]
    K --> L{Email unique?}
    L -->|Oui| M[Sauvegarder en BD]
    M --> N[Message succès]
    N --> O[Retour /profile]
    H -->|Non| P[Message erreur]
    J -->|Non| P
    L -->|Non| P
    P --> E
```

## 🔧 Code important

### Route d'édition :
```php
#[Route('/profile/edit', name: 'app_profile_edit')]
public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
```

### Formulaire :
```php
$form = $this->createForm(ProfileType::class, $user);
$form->handleRequest($request);
```

### Vérification du mot de passe :
```php
if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
    $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
}
```

## 📱 Captures d'écran

### Page de profil
- Affichage des informations de l'utilisateur
- Badge du rôle avec couleur
- Bouton "Modifier mon profil" en évidence
- Informations : Email, Rôle, Prénom, Nom, Téléphone, Date d'inscription

### Page de modification
- Formulaire organisé en sections
- Alertes d'information
- Champs pré-remplis avec les données actuelles
- Section sécurité avec mots de passe
- Boutons "Annuler" et "Enregistrer"

## ⚠️ Messages d'erreur possibles

| Erreur | Cause | Solution |
|--------|-------|----------|
| "Le mot de passe actuel est incorrect" | Mauvais mot de passe entré | Vérifier et ressaisir le bon mot de passe |
| "Cette adresse email est déjà utilisée" | Email déjà dans la base | Choisir un autre email |
| "Les nouveaux mots de passe ne correspondent pas" | Confirmation différente | Ressaisir les mots de passe identiques |
| "Le nouveau mot de passe doit contenir au moins 6 caractères" | Mot de passe trop court | Utiliser 6 caractères minimum |
| "L'email n'est pas valide" | Format email incorrect | Saisir un email valide |

## 🎯 Points clés

### Ce qui peut être modifié :
- ✅ Prénom
- ✅ Nom  
- ✅ Email
- ✅ Téléphone
- ✅ Mot de passe

### Ce qui NE peut PAS être modifié :
- ❌ Rôle (admin, bénévole, mécène)
- ❌ Date de création
- ❌ ID utilisateur

Pour modifier le rôle, contacter un administrateur.

## 📚 Technologies utilisées

- **Symfony 7.3** : Framework PHP
- **Doctrine ORM** : Gestion de la base de données
- **Symfony Forms** : Création et validation des formulaires
- **Twig** : Moteur de templates
- **Bootstrap 5** : Framework CSS
- **CSS personnalisé** : Design moderne avec gradients

## 🔄 Tests recommandés

1. ✅ Modifier uniquement le prénom/nom
2. ✅ Changer l'email avec un email valide
3. ✅ Essayer de mettre un email déjà utilisé (doit échouer)
4. ✅ Changer le mot de passe
5. ✅ Essayer avec un mauvais mot de passe actuel (doit échouer)
6. ✅ Tester sans remplir le mot de passe actuel (doit demander)
7. ✅ Vérifier les messages de succès/erreur
8. ✅ Tester sur mobile/tablette (responsive)

## 📝 Notes

- La date de modification est automatiquement mise à jour à chaque sauvegarde
- Le formulaire utilise le mot de passe actuel comme sécurité
- Les sessions sont maintenues après modification
- Le design est cohérent avec le reste de l'application
- Compatible avec tous les rôles (admin, bénévole, mécène)

---

**Développé pour GroLoto - SAE 2025**
