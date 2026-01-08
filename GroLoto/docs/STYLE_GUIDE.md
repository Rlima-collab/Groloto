# Guide de style CSS - GroLoto

## Conventions de nommage

### Classes CSS
- Utiliser la convention **BEM** (Block Element Modifier) quand approprié
- Noms en kebab-case : `nom-de-classe`
- Préfixes par domaine : `benevole-`, `mecene-`, `admin-`, `weekend-`

### Exemples
```css
/* Block */
.weekend-card { }

/* Element */
.weekend-card__header { }
.weekend-card__content { }

/* Modifier */
.weekend-card--active { }
.weekend-card--closed { }
```

## Variables et couleurs

### Palette principale
```css
/* Couleurs de marque */
--primary-dark: #07112a;
--primary-blue: #1e3a8a;
--accent-blue: #3b82f6;

/* Couleurs de statut */
--success: #10b981;
--warning: #f59e0b;
--danger: #ef4444;
--info: #3b82f6;

/* Couleurs neutres */
--gray-50: #f9fafb;
--gray-100: #f3f4f6;
--gray-500: #6b7280;
--gray-900: #111827;
```

## Organisation des fichiers CSS

### Structure recommandée
```
public/css/
├── shared/              # Styles partagés entre plusieurs modules
│   └── messaging.css    # Messagerie
├── admin.css            # Pages admin
├── benevoles.css        # Module bénévoles
├── mecenes.css          # Module mécènes
├── evenements.css       # Module événements
├── stocks.css           # Module stocks
├── taches.css           # Module tâches
└── weekends.css         # Module weekends
```

### Inclusion dans les templates
```twig
{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="{{ asset('css/nom-du-module.css') }}">
{% endblock %}
```

## Bonnes pratiques

### 1. Éviter le CSS inline
❌ Mauvais :
```html
<div style="margin-top: 20px; color: red;">
```

✅ Bon :
```html
<div class="mt-4 text-danger">
```

### 2. Utiliser les classes utilitaires existantes
Bootstrap 5 est disponible. Utiliser ses classes quand possible :
- Espacement : `mt-4`, `px-3`, `mb-2`
- Flexbox : `d-flex`, `justify-content-between`
- Texte : `text-center`, `fw-bold`

### 3. Commentaires de section
Chaque fichier CSS doit avoir des commentaires de section :
```css
/* ================================================
   NOM DE LA SECTION
   ================================================ */
```

### 4. Responsive design
Utiliser les media queries à la fin de chaque fichier :
```css
/* ================================================
   RESPONSIVE - Mobile
   ================================================ */
@media (max-width: 768px) {
    /* Styles mobile */
}
```

## JavaScript

### Organisation
```
public/js/
├── shared/              # Fonctions partagées
│   └── messaging.js     
├── admin/               # Scripts admin
│   └── messaging.js     
├── notifications.js     # Gestion notifications
├── sidebar.js           # Comportement sidebar
└── register.js          # Validation formulaires
```

### Variables Twig dans JS
Si le JS nécessite des variables Twig, garder un bloc `<script>` inline minimal :
```twig
{% block javascripts %}
    {{ parent() }}
    <script>
        // Configuration avec variables Twig
        const CONFIG = {
            apiUrl: '{{ path("api_endpoint") }}',
            userId: {{ app.user.id }}
        };
    </script>
    <script src="{{ asset('js/mon-module.js') }}"></script>
{% endblock %}
```
