# 🎯 MODE D'EMPLOI COMPLET - Play with Docker

## ✅ TOUT EST PRÊT !

Votre application est maintenant **entièrement conteneurisée** et prête à être testée avec Play with Docker.

---

## 📦 CE QUI A ÉTÉ CRÉÉ

### Fichiers générés:
- ✅ `GroLoto_PlayWithDocker.tar.gz` (168K) - Archive complète à uploader
- ✅ `COMMANDES_PLAY_WITH_DOCKER.txt` - Toutes les commandes à copier-coller
- ✅ `PLAY_WITH_DOCKER.md` - Guide détaillé avec screenshots et FAQ
- ✅ `Dockerfile`, `docker-compose.yaml`, config Nginx - Configuration Docker complète

---

## 🚀 MARCHE À SUIVRE (5 ÉTAPES SIMPLES)

### ÉTAPE 1: Uploader l'archive (2 minutes)

Uploadez le fichier `GroLoto_PlayWithDocker.tar.gz` sur un de ces services:

**Option A: WeTransfer (le plus simple)**
1. Allez sur https://wetransfer.com
2. Cliquez "+" et sélectionnez `GroLoto_PlayWithDocker.tar.gz`
3. Entrez votre email
4. Cliquez "Transfer"
5. Récupérez le lien de téléchargement reçu par email

**Option B: Google Drive**
1. Allez sur https://drive.google.com
2. Uploadez `GroLoto_PlayWithDocker.tar.gz`
3. Clic droit → "Obtenir le lien" → "Modifier" → "Tous les utilisateurs ayant le lien"
4. Copiez le lien

**Option C: GitHub (si vous avez un repo)**
```bash
git add .
git commit -m "Add Docker configuration"
git push
```

---

### ÉTAPE 2: Aller sur Play with Docker (30 secondes)

1. Ouvrez votre navigateur
2. Allez sur: **https://labs.play-with-docker.com**
3. Cliquez sur **"Login"** puis connectez-vous avec Docker Hub
   - Pas de compte? Créez-en un sur https://hub.docker.com (gratuit, 2 minutes)
4. Cliquez sur **"Start"**
5. Cliquez sur **"+ ADD NEW INSTANCE"**

➡️ Un terminal Linux avec Docker apparaît!

---

### ÉTAPE 3: Télécharger et extraire (1 minute)

Dans le terminal Play with Docker, copiez-collez ces commandes:

#### Si vous utilisez GitHub:
```bash
git clone https://github.com/VOTRE-USER/VOTRE-REPO.git
cd VOTRE-REPO
```

#### Si vous utilisez WeTransfer/Google Drive:
```bash
# Remplacez VOTRE_LIEN par le lien de téléchargement
wget "VOTRE_LIEN" -O GroLoto.tar.gz
tar -xzf GroLoto.tar.gz
cd GroLoto
```

---

### ÉTAPE 4: Démarrer l'application (5-10 minutes)

Copiez-collez cette commande:

```bash
docker compose up --build -d
```

⏳ **Attendez 5-10 minutes** pour le premier build (installation PHP, Composer, dépendances...)

Pendant ce temps, vous pouvez suivre les logs:
```bash
docker compose logs -f
```

---

### ÉTAPE 5: Accéder à l'application (instantané)

Une fois le build terminé:

1. **Regardez en haut de l'écran** → Un badge **"8000"** apparaît
2. **Cliquez sur ce badge**
3. **Votre application GroLoto s'ouvre!** 🎉

L'URL ressemble à: `http://ip172-18-0-X-XXXX.direct.labs.play-with-docker.com`

---

## 🎯 COMMANDES UTILES (Optionnelles)

### Vérifier l'état des conteneurs
```bash
docker compose ps
```

### Voir les logs
```bash
docker compose logs -f
```

### Exécuter les migrations
```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### Créer un utilisateur admin
```bash
docker compose exec php php bin/console app:create-admin
```

### Entrer dans le conteneur PHP
```bash
docker compose exec php bash
```

---

## ❓ FAQ RAPIDE

**Q: Combien de temps ça dure?**
A: 4 heures par session, renouvelable gratuitement.

**Q: C'est vraiment gratuit?**
A: Oui, 100% gratuit, pas de carte bancaire.

**Q: Le badge "8000" n'apparaît pas?**
A: Attendez que le build se termine (5-10 min), vérifiez avec `docker compose ps`

**Q: Erreur "Database connection refused"?**
A: Attendez 30 secondes puis: `docker compose restart php`

**Q: Comment arrêter?**
A: `docker compose down`

**Q: Puis-je sauvegarder mon travail?**
A: Exportez la DB avant la fin des 4h:
```bash
docker compose exec database pg_dump -U app app > backup.sql
```

---

## 🆘 EN CAS DE PROBLÈME

### Le build échoue
```bash
# Voir les erreurs détaillées
docker compose logs php

# Reconstruire
docker compose down
docker compose up --build
```

### 502 Bad Gateway
```bash
# Redémarrer PHP et Nginx
docker compose restart php nginx
```

### Port 8000 occupé
Modifiez `docker-compose.yaml`:
```yaml
ports:
  - "80:80"  # au lieu de "8000:80"
```
Puis le badge sera "80"

---

## 📋 CHECKLIST COMPLÈTE

- [ ] Archive créée (`GroLoto_PlayWithDocker.tar.gz`)
- [ ] Archive uploadée (WeTransfer/Drive/GitHub)
- [ ] Lien de téléchargement récupéré
- [ ] Compte Docker Hub créé
- [ ] Play with Docker ouvert (labs.play-with-docker.com)
- [ ] Instance créée (+ ADD NEW INSTANCE)
- [ ] Archive téléchargée et extraite dans Play with Docker
- [ ] `docker compose up --build -d` exécuté
- [ ] Attendu 5-10 minutes
- [ ] Badge "8000" cliqué
- [ ] Application testée dans le navigateur
- [ ] (Optionnel) Migrations exécutées

---

## 🎉 RÉSUMÉ ULTRA-COURT

```bash
# 1. Uploader GroLoto_PlayWithDocker.tar.gz sur WeTransfer

# 2. Aller sur https://labs.play-with-docker.com
#    Login → Start → + ADD NEW INSTANCE

# 3. Dans le terminal:
wget "VOTRE_LIEN" -O GroLoto.tar.gz
tar -xzf GroLoto.tar.gz
cd GroLoto
docker compose up --build -d

# 4. Attendre 5-10 min

# 5. Cliquer sur le badge "8000" en haut

# 6. Profiter! 🚀
```

---

## 📚 DOCUMENTATION COMPLÈTE

Pour plus de détails:
- **Guide détaillé**: `PLAY_WITH_DOCKER.md`
- **Liste des commandes**: `COMMANDES_PLAY_WITH_DOCKER.txt`
- **Architecture Docker**: `DOCKER_GUIDE.md`

---

**Tout est prêt! Suivez les 5 étapes ci-dessus et votre application sera en ligne dans 15 minutes! 💪**
