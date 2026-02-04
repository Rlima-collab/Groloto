#!/bin/bash

# Script pour arrêter les services Docker
# Usage: bash docker-stop.sh [--volumes] [--clean]

echo "🛑 Arrêt de GroLoto"
echo "=================="

if [[ "$*" == *"--clean"* ]]; then
    echo "🗑️  Suppression de tous les conteneurs et volumes..."
    docker compose down -v
    echo "✅ Tout supprimé (données perdues !)"
elif [[ "$*" == *"--volumes"* ]]; then
    echo "🗑️  Arrêt et suppression du volume db-data..."
    docker compose down -v
    echo "✅ Arrêté (données PostgreSQL perdues)"
else
    echo "🔴 Arrêt des services..."
    docker compose down
    echo "✅ Arrêté (données persistées dans db-data)"
fi

echo ""
echo "💡 Utilisation :"
echo "  - Sans options       : Arrête les services (données conservées)"
echo "  - --volumes (-v)     : Supprime le volume db-data"
echo "  - --clean            : Supprime conteneurs, volumes et networks"
