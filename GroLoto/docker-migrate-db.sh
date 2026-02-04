#!/bin/bash

# Script pour migrer de SQLite à PostgreSQL dans Docker
# Usage: bash docker-migrate-db.sh

echo "🔄 Migration SQLite → PostgreSQL"
echo "=================================="

# 1. Attendre que la DB soit prête
echo "⏳ Attente de la disponibilité de PostgreSQL..."
docker compose exec -T db pg_isready -U groloto || sleep 10

# 2. Lancer les migrations Doctrine
echo "🗂️  Exécution des migrations Doctrine..."
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# 3. Vérifier la connexion
echo "✅ Vérification de la connexion à la BD..."
docker compose exec app php bin/console doctrine:query:sql "SELECT VERSION()" || echo "⚠️  Vérification échouée"

echo ""
echo "✨ Migration terminée !"
echo ""
echo "Prochaines étapes:"
echo "  1. Charger les données initiales: docker compose exec app php bin/console doctrine:fixtures:load"
echo "  2. Ou importer depuis SQL: docker compose exec -T db psql -U groloto -d groloto < sql/groLoto.sql"
