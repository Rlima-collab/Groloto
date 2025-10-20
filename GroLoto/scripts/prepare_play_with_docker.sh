#!/usr/bin/env bash
set -euo pipefail

# Script de préparation pour Play with Docker
# Crée une archive ZIP prête à être uploadée et utilisée dans Play with Docker

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo "=========================================="
echo "  Préparation pour Play with Docker"
echo "=========================================="
echo ""

ARCHIVE_NAME="GroLoto_PlayWithDocker.tar.gz"

echo "[1/3] Vérification des fichiers Docker..."

# Vérifier que les fichiers essentiels existent
REQUIRED_FILES=(
  "Dockerfile"
  "docker-compose.yaml"
  "docker/nginx/default.conf"
)

MISSING=0
for file in "${REQUIRED_FILES[@]}"; do
  if [ -f "$file" ]; then
    echo "✓ $file"
  else
    echo "✗ $file manquant"
    MISSING=1
  fi
done

if [ $MISSING -eq 1 ]; then
  echo ""
  echo "❌ Certains fichiers Docker sont manquants."
  echo "   Assurez-vous d'avoir exécuté le script de configuration Docker."
  exit 1
fi

echo ""
echo "[2/3] Création de l'archive..."

# Créer l'archive en excluant les dossiers inutiles
tar --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='var/cache' \
    --exclude='var/log' \
    --exclude='.git' \
    --exclude='*.tar.gz' \
    --exclude='*.zip' \
    -czf "$ARCHIVE_NAME" \
    . 2>&1 | grep -v "fichier modifié pendant sa lecture" || true

# Vérifier que l'archive existe
if [ -f "$ARCHIVE_NAME" ]; then
  ARCHIVE_SIZE=$(du -h "$ARCHIVE_NAME" | cut -f1)
  echo "✓ Archive créée: $ARCHIVE_NAME ($ARCHIVE_SIZE)"
else
  echo "✗ Erreur lors de la création de l'archive"
  exit 1
fi

echo ""
echo "[3/3] Création du fichier de commandes..."

# Créer un fichier avec les commandes à exécuter dans Play with Docker
cat > "COMMANDES_PLAY_WITH_DOCKER.txt" << 'EOF'
=======================================================
 COMMANDES À EXÉCUTER DANS PLAY WITH DOCKER
=======================================================

1. Télécharger et extraire l'archive:
   -------------------------------------
   wget "VOTRE_URL_DE_TELECHARGEMENT" -O GroLoto.tar.gz
   tar -xzf GroLoto.tar.gz
   cd GroLoto

2. Construire et démarrer les conteneurs:
   ---------------------------------------
   docker compose up --build -d

3. Vérifier l'état:
   -----------------
   docker compose ps

4. Voir les logs (optionnel):
   ----------------------------
   docker compose logs -f

5. Accéder à l'application:
   -------------------------
   Cliquez sur le badge "8000" en haut de l'écran

6. Exécuter les migrations (optionnel):
   -------------------------------------
   docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

7. Créer un admin (optionnel):
   ----------------------------
   docker compose exec php php bin/console app:create-admin

=======================================================
 COMMANDES DE DÉPANNAGE
=======================================================

Voir les logs d'un service spécifique:
docker compose logs -f nginx
docker compose logs -f php
docker compose logs -f database

Redémarrer un service:
docker compose restart php

Reconstruire après modification:
docker compose down
docker compose up --build -d

Nettoyer tout:
docker compose down -v
docker system prune -a -f

=======================================================
EOF

echo "✓ Fichier de commandes créé: COMMANDES_PLAY_WITH_DOCKER.txt"

echo ""
echo "=========================================="
echo "  Préparation terminée ✓"
echo "=========================================="
echo ""
echo "📦 Archive créée: $ARCHIVE_NAME ($ARCHIVE_SIZE)"
echo ""
echo "PROCHAINES ÉTAPES:"
echo ""
echo "1. Uploadez l'archive sur un service de partage:"
echo "   - Google Drive (https://drive.google.com)"
echo "   - Dropbox (https://www.dropbox.com)"
echo "   - WeTransfer (https://wetransfer.com)"
echo "   - OU GitHub (git push vers votre repo)"
echo ""
echo "2. Récupérez le lien de téléchargement direct"
echo ""
echo "3. Allez sur Play with Docker:"
echo "   https://labs.play-with-docker.com"
echo ""
echo "4. Créez une instance et utilisez les commandes du fichier:"
echo "   COMMANDES_PLAY_WITH_DOCKER.txt"
echo ""
echo "📖 Guide complet: PLAY_WITH_DOCKER.md"
echo ""
