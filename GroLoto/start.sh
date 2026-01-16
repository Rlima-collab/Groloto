#!/bin/bash

# Script de démarrage rapide pour GroLoto
# Usage: ./start.sh

set -e

echo "🎰 GroLoto - Démarrage de l'application"
echo "========================================"

# Couleurs pour l'affichage
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Vérifier si Docker Compose est installé
if ! command -v docker compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

echo ""
echo -e "${BLUE}📦 Construction des images Docker...${NC}"
docker compose build

echo ""
echo -e "${BLUE}🚀 Démarrage des conteneurs...${NC}"
docker compose up -d

echo ""
echo -e "${GREEN}✅ Application démarrée avec succès !${NC}"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${YELLOW}🌐 Accès aux services :${NC}"
echo ""
echo "  📱 Application :  http://localhost:8080"
echo "  📧 MailPit :      http://localhost:8025"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${BLUE}📋 Commandes utiles :${NC}"
echo ""
echo "  Voir les logs :       docker compose logs -f"
echo "  Arrêter :             docker compose stop"
echo "  Redémarrer :          docker compose restart"
echo "  Arrêter et supprimer: docker compose down"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
