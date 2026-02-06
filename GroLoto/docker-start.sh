#!/bin/bash

# Script de démarrage rapide avec PostgreSQL
# Usage: bash docker-start.sh

set -e

echo "🚀 Démarrage de GroLoto avec PostgreSQL"
echo "========================================"

# Charger les variables d'environnement
if [ -f ".env.local.docker" ]; then
    export $(cat .env.local.docker | grep -v '^#' | xargs)
    echo "✅ Variables .env.local.docker chargées"
else
    echo "⚠️  Fichier .env.local.docker non trouvé, utilisation des valeurs par défaut"
fi

# Arrêter les conteneurs précédents (optionnel)
# docker compose down

# Démarrer les services
echo "📦 Démarrage des services Docker..."
docker compose up -d

# Attendre que PostgreSQL soit prêt
echo "⏳ Attente de la disponibilité de PostgreSQL..."
sleep 5
docker compose exec -T db pg_isready -U ${POSTGRES_USER:-groloto} || sleep 10

# Exécuter les migrations Doctrine
echo "🗂️  Exécution des migrations Doctrine..."
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction || true

# Afficher le statut
echo ""
echo "✨ Démarrage terminé !"
echo ""
echo "📋 Services disponibles :"
echo "  - Application : http://localhost:8080"
echo "  - MailPit Web: http://localhost:8025"
echo "  - PostgreSQL : localhost:5432"
echo ""
echo "📚 Commandes utiles :"
echo "  - Logs app    : docker compose logs -f app"
echo "  - Logs BD     : docker compose logs -f db"
echo "  - DB Shell    : docker compose exec db psql -U groloto -d groloto"
echo "  - App Shell   : docker compose exec app bash"
echo "  - Stop        : docker compose down"
