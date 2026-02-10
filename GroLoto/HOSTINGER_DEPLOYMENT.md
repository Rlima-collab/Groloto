# Guide de Déploiement sur Hostinger

## Résumé des Corrections pour Hostinger

Ce projet a été configuré pour fonctionner correctement sur un hébergement comme Hostinger, où l'application peut être déployée dans un sous-dossier de domaine.

### Problèmes corrigés

1. **Chemins absolus en JavaScript** - Remplacés par des chemins relatifs avec gestion de base URL
2. **Fetch API calls** - Configurable dynamiquement avec une variable globale `APP_BASE_URL`
3. **URLs de routes** - Utilisation correcte de `path()` dans les templates Twig
4. **Images dynamiques** - Gestion correcte des chemins d'images

## Configuration sur Hostinger

### Étape 1 : Configuration de l'environnement

Avant de déployer, modifiez le fichier `.env.local` sur votre serveur Hostinger :

```dotenv
# Si le site est à la racine du domaine
APP_URL=https://mondomaine.com/

# Si le site est dans un sous-dossier (ex: /groloto/)
APP_URL=https://mondomaine.com/groloto/

# Configuration de la base de données PostgreSQL sur Hostinger
DATABASE_URL="pgsql://user:password@host:5432/db_name?serverVersion=15&charset=utf8"

# Autres configurations
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=<votre_clé_secrète>
```

### Étape 2 : Déploiement des fichiers

1. **Téléchargez le projet** sur votre espace Hostinger
2. **Installez les dépendances** :
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Migrez la base de données** (première fois) :
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

4. **Générez les fichiers de cache** :
   ```bash
   php bin/console cache:clear -e prod
   php bin/console cache:warmup -e prod
   ```

### Étape 3 : Sécurité des fichiers

- **Permissions** : `var/`, `public/uploads/` doivent avoir les bonnes permissions d'écriture
- **`.env`** : Ne pas mettre `.env` en production, utiliser `.env.local` à la place
- **`public/`** : Configurez le webroot des domaines/sous-domaines à pointer vers le dossier `public/`

### Étape 4 : Vérifi...

Après le déploiement, vérifiez :

1. ✅ Le CSS se charge correctement
2. ✅ Les images s'affichent
3. ✅ Les boutons et formulaires fonctionnent
4. ✅ Les liens de notification marchent
5. ✅ Les uploads de fichiers fonctionnent

## Architecture des corrections

### Variables globales JavaScript

Un variable globale `APP_BASE_URL` est automatiquement définie par ces templates :
- `templates/admin/message/messages.html.twig`
- `templates/mecene/message/messages.html.twig`
- `templates/benevole/message/messages.html.twig`

Cette variable est utilisée dans :
- `public/js/admin/messaging.js`
- `public/js/shared/messaging.js`
- `public/js/notifications.js`

### Utilisation des routes Symfony

Les routes générées via `{{ path('route_name') }}` dans les templates gèrent automatiquement :
- Les chemins absolus et relatifs
- Les sous-dossiers
- Les paramètres de route

Exemples corrigés :
```twig
{# ❌ Avant (ne fonctionne pas sur Hostinger/subdossier) #}
<a href="/">Accueil</a>
<form action="/taches/{{ id }}/delete" method="POST">

{# ✅ Après (fonctionne partout) #}
<a href="{{ path('app_home') }}">Accueil</a>
<form action="{{ path('tache_delete', {'id': id}) }}" method="POST">
```

## Troubleshooting

### Le CSS/Images ne charge pas

1. Vérifiez que `APP_URL` dans `.env.local` est correct
2. Vérifiez que le dossier `public/` est le webroot configuré
3. Vérifiez les permissions sur `public/` (755 minimum)

### Les formulaires ne soumettent pas

1. Vérifiez que `{{ path('route_name') }}` est utilisé pour tous les `action` des formulaires
2. Vérifiez que la route existe dans `config/routes.yaml`

### Les images dynamiques ne s'affichent pas

1. Vérifiez que `{{ asset('path/to/image') }}` est utilisé pour les images statiques
2. Pour les images dynamiques (uploaded), vérifiez que le chemin utilise les bonnes fonctions

## Performance

- **Cache** : Le cache Symfony est important en production
- **Assets** : Les assets encodés en base64 dans les CSS n'ont pas besoin de serveur externe
- **Uploads** : Les uploads sont dans `public/uploads/` directement accessible

## Support PostgreSQL

Si vous utilisez PostgreSQL sur Hostinger au lieu de SQLite :

```bash
# Mettez à jour .env.local
DATABASE_URL="pgsql://user:password@localhost:5432/groloto?serverVersion=15&charset=utf8"

# Créez la base et migrez
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Conseils de sécurité

1. ✅ Utilisez `.env.local` sur le serveur, ne committez pas les secrets
2. ✅ Désactivez `APP_DEBUG=false` en production
3. ✅ Configurez les permissions correctement
4. ✅ Utilisez HTTPS
5. ✅ Maintenez Symfony à jour pour les correctifs de sécurité
