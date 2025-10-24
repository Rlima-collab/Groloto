#!/bin/bash

# Script d'arrêt pour GroLoto
# Usage: ./stop.sh

set -e

echo "🎰 GroLoto - Arrêt de l'application"
echo "===================================="

# Couleurs pour l'affichage
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo ""
echo -e "${YELLOW}⏹️  Arrêt des conteneurs...${NC}"
docker compose stop

echo ""
echo -e "${RED}✅ Conteneurs arrêtés.${NC}"
echo ""
echo "Pour redémarrer: ./start.sh"
echo "Pour supprimer complètement: docker compose down"
