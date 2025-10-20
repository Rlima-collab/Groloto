#!/usr/bin/env bash
set -euo pipefail

# Script de démarrage des conteneurs (Docker ou Podman)

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo "=========================================="
echo "  Démarrage Application Conteneurisée"
echo "=========================================="
echo ""

# Vérifier quelle commande utiliser
if command -v podman-compose >/dev/null 2>&1; then
  COMPOSE_CMD="podman-compose"
  echo "✓ Utilisation de Podman"
elif command -v podman >/dev/null 2>&1 && command -v docker-compose >/dev/null 2>&1; then
  COMPOSE_CMD="docker-compose"
  alias docker='podman'
  echo "✓ Utilisation de Podman avec docker-compose"
elif command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  COMPOSE_CMD="docker compose"
  echo "✓ Utilisation de Docker Compose (plugin)"
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE_CMD="docker-compose"
  echo "✓ Utilisation de docker-compose (standalone)"
else
  echo "✗ Ni Docker ni Podman n'est disponible"
  echo ""
  echo "Options:"
  echo "  1. Installer Podman (rootless, sans sudo):"
  echo "     ./scripts/install_podman.sh"
  echo ""
  echo "  2. Installer Docker (nécessite sudo):"
  echo "     sudo apt install docker.io docker-compose-plugin"
  echo ""
  echo "  3. Utiliser un environnement cloud:"
  echo "     - GitHub Codespaces"
  echo "     - GitPod"
  echo "     - Play with Docker"
  exit 1
fi

echo "Commande utilisée: $COMPOSE_CMD"
echo ""

# Construire et démarrer les conteneurs
echo "Build et démarrage des conteneurs..."
echo "(Cela peut prendre plusieurs minutes la première fois)"
echo ""

$COMPOSE_CMD up --build -d

echo ""
echo "=========================================="
echo "  Conteneurs démarrés ✓"
echo "=========================================="
echo ""

# Afficher l'état des conteneurs
echo "État des conteneurs:"
if [[ "$COMPOSE_CMD" == "podman-compose" ]]; then
  podman-compose ps
else
  $COMPOSE_CMD ps
fi

echo ""
echo "=========================================="
echo "  Application disponible"
echo "=========================================="
echo ""
echo "URL: http://localhost:8000"
echo ""
echo "Commandes utiles:"
echo "  - Voir les logs:        $COMPOSE_CMD logs -f"
echo "  - Arrêter:             $COMPOSE_CMD down"
echo "  - Redémarrer:          $COMPOSE_CMD restart"
echo "  - Exec dans PHP:       $COMPOSE_CMD exec php bash"
echo "  - Migrations:          $COMPOSE_CMD exec php bin/console doctrine:migrations:migrate"
echo ""
