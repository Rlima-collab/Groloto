#!/bin/bash

# Script de test des fonctionnalités de gestion des participants

echo "========================================="
echo "Tests des fonctionnalités Participants"
echo "========================================="
echo ""

BASE_URL="http://127.0.0.1:8000"

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}1. Test de la page liste des participants${NC}"
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/participants")
if [ "$response" = "200" ]; then
    echo -e "${GREEN}✓ Page liste accessible (HTTP $response)${NC}"
else
    echo -e "${RED}✗ Erreur page liste (HTTP $response)${NC}"
fi
echo ""

echo -e "${BLUE}2. Test de l'export CSV${NC}"
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/participants/export")
if [ "$response" = "200" ]; then
    echo -e "${GREEN}✓ Export CSV accessible (HTTP $response)${NC}"
    # Télécharger un exemple
    curl -s "$BASE_URL/participants/export" -o /tmp/participants_test.csv
    if [ -f /tmp/participants_test.csv ]; then
        lines=$(wc -l < /tmp/participants_test.csv)
        echo -e "${GREEN}  Fichier CSV créé avec $lines ligne(s)${NC}"
    fi
else
    echo -e "${RED}✗ Erreur export CSV (HTTP $response)${NC}"
fi
echo ""

echo -e "${BLUE}3. Test de la page d'import${NC}"
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/participants/import")
if [ "$response" = "200" ]; then
    echo -e "${GREEN}✓ Page d'import accessible (HTTP $response)${NC}"
else
    echo -e "${RED}✗ Erreur page d'import (HTTP $response)${NC}"
fi
echo ""

echo -e "${BLUE}4. Test du filtrage (API)${NC}"
response=$(curl -s -X POST "$BASE_URL/participants/filter" \
    -H "Content-Type: application/json" \
    -d '{"search":"", "type":"", "statut":""}' \
    -w "\n%{http_code}" -o /tmp/filter_test.json)

http_code=$(echo "$response" | tail -n 1)
if [ "$http_code" = "200" ]; then
    echo -e "${GREEN}✓ API de filtrage fonctionnelle (HTTP $http_code)${NC}"
    # Compter les résultats
    if [ -f /tmp/filter_test.json ]; then
        count=$(head -n -1 /tmp/filter_test.json | grep -o "\"id\":" | wc -l)
        echo -e "${GREEN}  $count participant(s) trouvé(s)${NC}"
    fi
else
    echo -e "${RED}✗ Erreur API filtrage (HTTP $http_code)${NC}"
fi
echo ""

echo -e "${BLUE}5. Vérification des routes${NC}"
cd /home/iut45/Etudiants/o22301253/Bureau/SAE/Groloto/GroLoto
routes=$(php bin/console debug:router | grep -c "participants_")
echo -e "${GREEN}✓ $routes routes 'participants' trouvées${NC}"
php bin/console debug:router | grep "participants_" | awk '{print "  - " $1 " (" $2 ")"}'
echo ""

echo -e "${BLUE}6. Vérification des templates${NC}"
templates=("participants/index.html.twig" "participants/import.html.twig")
for template in "${templates[@]}"; do
    if [ -f "templates/$template" ]; then
        echo -e "${GREEN}✓ Template $template existe${NC}"
    else
        echo -e "${RED}✗ Template $template manquant${NC}"
    fi
done
echo ""

echo -e "${BLUE}7. Vérification du contrôleur${NC}"
if [ -f "src/Controller/ParticipantController.php" ]; then
    echo -e "${GREEN}✓ ParticipantController existe${NC}"
    methods=$(grep -c "#\[Route" src/Controller/ParticipantController.php)
    echo -e "${GREEN}  $methods méthodes de route définies${NC}"
else
    echo -e "${RED}✗ ParticipantController manquant${NC}"
fi
echo ""

echo -e "${BLUE}8. Vérification de PhpSpreadsheet${NC}"
if grep -q "phpoffice/phpspreadsheet" composer.json; then
    echo -e "${GREEN}✓ PhpSpreadsheet installé${NC}"
    version=$(grep "phpoffice/phpspreadsheet" composer.json | head -1 | sed 's/.*: "\([^"]*\)".*/\1/')
    echo -e "${GREEN}  Version: $version${NC}"
else
    echo -e "${RED}✗ PhpSpreadsheet non installé${NC}"
fi
echo ""

echo "========================================="
echo -e "${GREEN}Tests terminés!${NC}"
echo "========================================="
echo ""
echo "Pour tester manuellement:"
echo "1. Ouvrir http://127.0.0.1:8000/participants"
echo "2. Cliquer sur 'Exporter CSV'"
echo "3. Cliquer sur 'Filtrer' et tester la recherche"
echo "4. Cliquer sur 'Import Manuel' et importer le fichier exemple_import.csv"
echo "5. Tester 'Synchroniser HelloAsso'"
echo ""
