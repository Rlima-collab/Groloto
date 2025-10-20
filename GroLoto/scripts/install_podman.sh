#!/usr/bin/env bash
set -euo pipefail

# Script d'installation de Podman (alternative à Docker sans privilèges root)
# Podman est compatible avec Docker et peut utiliser les mêmes Dockerfile et docker-compose.yaml

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo "=========================================="
echo "  Installation Podman (Alternative Docker)"
echo "=========================================="
echo ""
echo "Podman est une alternative à Docker qui ne nécessite pas"
echo "de privilèges root et est compatible avec les commandes Docker."
echo ""

# Vérifier si on est sur un système supporté
if ! command -v apt >/dev/null 2>&1; then
  echo "✗ Ce script nécessite apt (Ubuntu/Debian)"
  exit 1
fi

# Vérifier si Podman est déjà installé
if command -v podman >/dev/null 2>&1; then
  echo "✓ Podman est déjà installé: $(podman --version)"
  read -p "Voulez-vous continuer avec la configuration? [y/N] " yn
  if [[ ! "$yn" =~ ^[Yy]$ ]]; then
    exit 0
  fi
else
  echo "[1/4] Installation de Podman..."
  echo "Podman peut être installé via apt. Cela nécessite les droits sudo."
  echo ""
  read -p "Voulez-vous installer Podman? [y/N] " yn
  if [[ ! "$yn" =~ ^[Yy]$ ]]; then
    echo "Installation annulée."
    echo ""
    echo "Alternative: Utilisez Docker sur une autre machine ou via cloud:"
    echo "  - GitHub Codespaces: https://github.com/codespaces"
    echo "  - GitPod: https://www.gitpod.io"
    echo "  - Play with Docker: https://labs.play-with-docker.com"
    exit 0
  fi
  
  echo ""
  echo "Installation de Podman..."
  sudo apt update
  sudo apt install -y podman podman-compose || {
    echo "✗ Échec de l'installation de Podman"
    echo "Vérifiez les droits sudo ou contactez l'administrateur"
    exit 1
  }
  
  echo "✓ Podman installé: $(podman --version)"
fi

# Configurer Podman en mode rootless
echo ""
echo "[2/4] Configuration Podman en mode rootless..."
podman system migrate || echo "⚠ Migration déjà effectuée"
echo "✓ Podman configuré en mode rootless"

# Créer des alias pour compatibilité Docker
echo ""
echo "[3/4] Configuration des alias Docker..."

ALIAS_FILE="$HOME/.bash_aliases"
if [ -f "$ALIAS_FILE" ]; then
  if grep -q "alias docker=" "$ALIAS_FILE"; then
    echo "✓ Alias 'docker' déjà configuré"
  else
    echo "alias docker='podman'" >> "$ALIAS_FILE"
    echo "alias docker-compose='podman-compose'" >> "$ALIAS_FILE"
    echo "✓ Alias ajoutés à $ALIAS_FILE"
  fi
else
  echo "alias docker='podman'" > "$ALIAS_FILE"
  echo "alias docker-compose='podman-compose'" >> "$ALIAS_FILE"
  echo "✓ Alias créés dans $ALIAS_FILE"
fi

# Recharger les alias (pour la session actuelle)
alias docker='podman' 2>/dev/null || true
alias docker-compose='podman-compose' 2>/dev/null || true

echo ""
echo "Pour activer les alias dans votre session actuelle:"
echo "  source ~/.bash_aliases"
echo ""

# Vérifier podman-compose
echo "[4/4] Vérification de podman-compose..."
if command -v podman-compose >/dev/null 2>&1; then
  echo "✓ podman-compose installé"
else
  echo "⚠ podman-compose non trouvé. Installation via pip..."
  if command -v pip3 >/dev/null 2>&1; then
    pip3 install --user podman-compose || {
      echo "✗ Échec installation podman-compose"
      echo "Vous pouvez l'installer manuellement:"
      echo "  pip3 install --user podman-compose"
    }
  else
    echo "✗ pip3 non trouvé. Installez-le avec:"
    echo "  sudo apt install python3-pip"
    echo "  pip3 install --user podman-compose"
  fi
fi

echo ""
echo "=========================================="
echo "  Installation terminée ✓"
echo "=========================================="
echo ""
echo "Prochaines étapes:"
echo ""
echo "1. Recharger les alias:"
echo "   source ~/.bash_aliases"
echo ""
echo "2. Tester Podman:"
echo "   podman --version"
echo "   podman run hello-world"
echo ""
echo "3. Utiliser avec docker-compose:"
echo "   podman-compose up --build"
echo "   # ou avec l'alias:"
echo "   docker compose up --build"
echo ""
echo "Note: Podman est compatible avec Docker mais fonctionne"
echo "      sans daemon et sans privilèges root."
echo ""
