#!/usr/bin/env bash
set -euo pipefail

# Script de réinitialisation de la base de données SQLite

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo "=========================================="
echo "  Réinitialisation Base de Données"
echo "=========================================="
echo ""

# Supprimer l'ancienne base
if [ -f "var/data.db" ]; then
  echo "Suppression de l'ancienne base var/data.db..."
  rm -f var/data.db
  echo "✓ Ancienne base supprimée"
fi

# Recréer la base et le schéma
echo ""
echo "Création du schéma à partir des entités..."
php bin/console doctrine:schema:create --no-interaction || {
  echo "✗ Erreur lors de la création du schéma"
  exit 1
}
echo "✓ Schéma créé"

# Optionnel: charger des fixtures si disponibles
if php bin/console list | grep -q "doctrine:fixtures:load"; then
  echo ""
  read -p "Voulez-vous charger les fixtures de test? [y/N] " yn
  if [[ "$yn" =~ ^[Yy]$ ]]; then
    php bin/console doctrine:fixtures:load --no-interaction || echo "⚠ Fixtures non chargées"
  fi
fi

echo ""
echo "=========================================="
echo "  Base de données réinitialisée ✓"
echo "=========================================="
echo ""
