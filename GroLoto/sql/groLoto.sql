-- =============================================
-- SUPPRESSION COMPLÈTE DES TABLES
-- =============================================
DROP TABLE IF EXISTS CONTACT_MESSAGE;
DROP TABLE IF EXISTS NOTIFICATION;
DROP TABLE IF EXISTS HISTORIQUE_EVENEMENT;
DROP TABLE IF EXISTS PARAMETRE;
DROP TABLE IF EXISTS HELLOASSO;
DROP TABLE IF EXISTS COMMUNICATION;
DROP TABLE IF EXISTS DEMANDE_ANNULATION;
DROP TABLE IF EXISTS DEMANDE_TACHE;
DROP TABLE IF EXISTS AFFECTATION_TACHE;
DROP TABLE IF EXISTS PLAGE_HORAIRE;
DROP TABLE IF EXISTS TACHE;
DROP TABLE IF EXISTS DISPONIBILITE_BENEVOLE;
DROP TABLE IF EXISTS HISTORIQUE_STOCK;
DROP TABLE IF EXISTS STOCK;
DROP TABLE IF EXISTS INSCRIPTION_MECENE;
DROP TABLE IF EXISTS EVENEMENT;
DROP TABLE IF EXISTS CONVENTION;
DROP TABLE IF EXISTS LOT;
DROP TABLE IF EXISTS MECENE;
DROP TABLE IF EXISTS BENEVOLE;
DROP TABLE IF EXISTS UTILISATEUR;
DROP TABLE IF EXISTS ROLE;
DROP TABLE IF EXISTS WEEKEND;
DROP TABLE IF EXISTS messenger_messages;

-- =============================================
-- CRÉATION DES TABLES
-- =============================================

-- WEEKEND (CORRIGÉE : cover_image ajoutée correctement)
CREATE TABLE WEEKEND (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    cover_image TEXT,
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ROLE
CREATE TABLE ROLE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    description TEXT
);

-- UTILISATEUR
CREATE TABLE UTILISATEUR (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_role INTEGER NOT NULL,
    email TEXT NOT NULL UNIQUE,
    mot_de_passe TEXT,
    prenom TEXT,
    nom TEXT,
    telephone TEXT,
    profile_image TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_role) REFERENCES ROLE(id)
);

-- BENEVOLE
CREATE TABLE BENEVOLE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_utilisateur INTEGER NOT NULL UNIQUE,
    remarque TEXT,
  disponibilites TEXT,
    actif BOOLEAN DEFAULT 1,
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

-- MECENE
CREATE TABLE MECENE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_utilisateur INTEGER,
    organisation TEXT NOT NULL,
    siret TEXT NOT NULL,
    adresse_postale TEXT,
    logo TEXT,
    instagram TEXT,
    facebook TEXT,
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

-- LOT
CREATE TABLE LOT (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
  id_weekend INTEGER,
    id_mecene INTEGER,
    titre TEXT NOT NULL,
    description TEXT,
    quantite INTEGER DEFAULT 1,
    valeur_estimee REAL DEFAULT 0.0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_mecene) REFERENCES MECENE(id),
  FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id)
);

-- CONVENTION
CREATE TABLE CONVENTION (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_mecene INTEGER NOT NULL,
    nom_modele TEXT,
    url_pdf TEXT,
    date_signature DATETIME,
    methode_signature TEXT DEFAULT 'aucune',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_methode_signature CHECK (methode_signature IN ('aucune', 'manuelle', 'electronique')),
    FOREIGN KEY (id_mecene) REFERENCES MECENE(id)
);

-- EVENEMENT
CREATE TABLE EVENEMENT (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_weekend INTEGER,
    nom TEXT NOT NULL,
    description TEXT,
    date_debut DATE,
    date_fin DATE,
    lieu TEXT,
    heure_debut TIME,
    duree_minutes INTEGER,
    image TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id)
);

-- INSCRIPTION_MECENE
CREATE TABLE INSCRIPTION_MECENE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_mecene INTEGER NOT NULL,
    id_evenement INTEGER NOT NULL,
    description_don TEXT NOT NULL,
    montant_estime REAL,
    statut TEXT DEFAULT 'en_attente',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    remarques TEXT,
  remarque_acceptation TEXT,
    nom_don TEXT NOT NULL,
    categorie TEXT NOT NULL,
    quantite INTEGER NOT NULL,
    valeur_unitaire REAL,
    remarque_refus TEXT,
    adresse_postale TEXT,
    type_don TEXT DEFAULT 'fonctionnement',
    CONSTRAINT check_statut CHECK (statut IN ('en_attente', 'accepte', 'refuse')),
    FOREIGN KEY (id_mecene) REFERENCES MECENE(id),
    FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

-- STOCK
CREATE TABLE STOCK (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    categorie TEXT,
    quantite INTEGER DEFAULT 0,
    unite TEXT,
    seuil INTEGER DEFAULT 0,
    valeur_unitaire REAL DEFAULT 0.0,
    remarque TEXT,
    derniere_modif DATETIME DEFAULT CURRENT_TIMESTAMP,
    source TEXT DEFAULT 'achat',
    date_retour DATE,
    preteur TEXT,
    CONSTRAINT check_categorie CHECK (categorie IN ('bar', 'resto', 'deco', 'autre'))
);

-- HISTORIQUE_STOCK
CREATE TABLE HISTORIQUE_STOCK (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_stock INTEGER NOT NULL,
    type_changement TEXT NOT NULL,
    quantite INTEGER NOT NULL,
    raison TEXT,
    id_evenement INTEGER,
    id_utilisateur INTEGER,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_type_changement CHECK (type_changement IN ('entree', 'sortie', 'ajustement')),
    FOREIGN KEY (id_stock) REFERENCES STOCK(id),
    FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id),
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

-- DISPONIBILITE_BENEVOLE
CREATE TABLE DISPONIBILITE_BENEVOLE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_benevole INTEGER NOT NULL,
    id_evenement INTEGER NOT NULL,
    debut DATETIME,
    fin DATETIME,
    remarque TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
    FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

-- TACHE
CREATE TABLE TACHE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_weekend INTEGER NOT NULL,
    id_evenement INTEGER,
    titre TEXT NOT NULL,
    poste_requis TEXT,
    debut DATETIME NOT NULL,
    fin DATETIME NOT NULL,
    max_personnes INTEGER DEFAULT 1,
    remarque TEXT,
    FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id),
    FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id) ON DELETE SET NULL
);

CREATE TABLE PLAGE_HORAIRE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_tache INTEGER NOT NULL,
    jour DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    max_personnes_plage INTEGER,
    FOREIGN KEY (id_tache) REFERENCES TACHE(id)
);

CREATE TABLE DEMANDE_TACHE (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  id_tache INTEGER NOT NULL,
  id_benevole INTEGER NOT NULL,
  id_admin_reponse INTEGER,
  date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
  statut TEXT DEFAULT 'en_attente',
  message_benevole TEXT,
  message_admin TEXT,
  date_reponse DATETIME,
  CONSTRAINT check_statut_demande CHECK (statut IN ('en_attente', 'acceptee', 'refusee')),
  FOREIGN KEY (id_tache) REFERENCES TACHE(id),
  FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
  FOREIGN KEY (id_admin_reponse) REFERENCES UTILISATEUR(id)
);

CREATE TABLE DEMANDE_ANNULATION (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  id_affectation INTEGER NOT NULL,
  id_benevole INTEGER NOT NULL,
  id_admin_reponse INTEGER,
  date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
  statut TEXT DEFAULT 'en_attente',
  motif_benevole TEXT,
  tache_titre TEXT,
  message_admin TEXT,
  date_reponse DATETIME,
  CONSTRAINT check_statut_annulation CHECK (statut IN ('en_attente', 'acceptee', 'refusee')),
  FOREIGN KEY (id_affectation) REFERENCES AFFECTATION_TACHE(id),
  FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
  FOREIGN KEY (id_admin_reponse) REFERENCES UTILISATEUR(id)
);

CREATE TABLE AFFECTATION_TACHE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_tache INTEGER NOT NULL,
    id_benevole INTEGER NOT NULL,
    id_utilisateur INTEGER,
    date_affectation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut TEXT DEFAULT 'assigne',
    remarque TEXT,
    CONSTRAINT check_statut CHECK (statut IN ('assigne', 'confirme', 'annule')),
    FOREIGN KEY (id_tache) REFERENCES TACHE(id),
    FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

-- COMMUNICATION
CREATE TABLE COMMUNICATION (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_evenement INTEGER,
    titre TEXT NOT NULL,
    type TEXT DEFAULT 'post',
    date_prevue DATETIME,
    statut TEXT DEFAULT 'brouillon',
    budget REAL DEFAULT 0.0,
    remarque TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_type CHECK (type IN ('post', 'affiche', 'flyer', 'email', 'autre')),
    CONSTRAINT check_statut CHECK (statut IN ('brouillon', 'programme', 'fait', 'annule')),
    FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

-- HELLOASSO
CREATE TABLE HELLOASSO (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_evenement INTEGER,
    id_externe TEXT,
    prenom TEXT,
    nom TEXT,
    email TEXT,
    telephone TEXT,
    type_ticket TEXT,
    date_achat DATETIME,
    date_import DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

-- PARAMETRE
CREATE TABLE PARAMETRE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cle TEXT NOT NULL UNIQUE,
    valeur TEXT,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- HISTORIQUE_EVENEMENT
CREATE TABLE HISTORIQUE_EVENEMENT (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_evenement INTEGER NOT NULL,
    action TEXT NOT NULL,
    description TEXT,
    id_utilisateur INTEGER,
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_action CHECK (action IN ('creation', 'modification', 'annulation', 'autre')),
    FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id),
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

-- CONTACT_MESSAGE
CREATE TABLE CONTACT_MESSAGE (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    email TEXT NOT NULL,
    destinataire VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT 0,
    repondu_par_id INTEGER,
    reponse TEXT,
    repondu_le DATETIME,
    parent_id INTEGER DEFAULT NULL,
    cloturee BOOLEAN DEFAULT 0,
    masquee_pour TEXT DEFAULT NULL,
    FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR(id),
    FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE(id)
);

CREATE INDEX IF NOT EXISTS idx_parent ON CONTACT_MESSAGE(parent_id);

-- NOTIFICATION (AVEC COLONNE reponse)
CREATE TABLE NOTIFICATION (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_destinataire INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    lien VARCHAR(255),
    lue BOOLEAN DEFAULT 0,
    created_at DATETIME NOT NULL,
    reponse TEXT,  -- AJOUTÉ ICI
    FOREIGN KEY (id_destinataire) REFERENCES UTILISATEUR(id)
);

-- MESSENGER_MESSAGES
CREATE TABLE messenger_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    body TEXT NOT NULL,
    headers TEXT NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL,
    available_at DATETIME NOT NULL,
    delivered_at DATETIME DEFAULT NULL
);

-- =============================================
-- INSERTIONS DE DONNÉES COMBINÉES ET CORRIGÉES
-- =============================================

INSERT INTO ROLE (nom, description) VALUES
  ('admin', 'Administrateur organisation'),
  ('benevole', 'Bénévole participant aux événements'),
  ('mecene', 'Partenaire/mécène offrant des lots ou financements');

INSERT INTO UTILISATEUR (id_role, email, prenom, nom, telephone, mot_de_passe, date_creation) VALUES
  -- Administrateurs
  (1, 'admin@groloto.local', 'Admin', 'User', '0600000000', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (1, 'sophie.bernard@groloto.local', 'Sophie', 'Bernard', '0601020304', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  -- Bénévoles
  (2, 'benevole1@groloto.local', 'Alice', 'Durand', '0612345678', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'benevole2@groloto.local', 'Bob', 'Lefevre', '0623456789', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'marie.dupont@groloto.local', 'Marie', 'Dupont', '0634567890', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'pierre.moreau@groloto.local', 'Pierre', 'Moreau', '0645678901', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'lucie.petit@groloto.local', 'Lucie', 'Petit', '0656789012', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'thomas.robert@groloto.local', 'Thomas', 'Robert', '0667890123', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'emma.simon@groloto.local', 'Emma', 'Simon', '0678901234', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'lucas.laurent@groloto.local', 'Lucas', 'Laurent', '0689012345', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'chloe.michel@groloto.local', 'Chloé', 'Michel', '0690123456', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'hugo.garcia@groloto.local', 'Hugo', 'Garcia', '0601234567', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  -- Mécènes
  (3, 'mecene1@groloto.local', 'Jean', 'Martin', '0712345678', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (3, 'contact@restaurant-gourmet.fr', 'François', 'Dubois', '0723456789', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (3, 'direction@librairie-arts.fr', 'Catherine', 'Roux', '0734567890', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (3, 'info@spa-zenitude.fr', 'Isabelle', 'Fournier', '0745678901', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (3, 'commercial@supermarche-bio.fr', 'Marc', 'Girard', '0756789012', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (3, 'contact@garage-vitesse.fr', 'Philippe', 'Andre', '0767890123', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now'));

INSERT INTO BENEVOLE (id_utilisateur, remarque, actif) VALUES
  (3, 'Disponible surtout les week-ends', 1),
  (4, 'Disponible en semaine', 1),
  (5, 'Étudiante, disponible le soir et week-end', 1),
  (6, 'Retraité, très disponible', 1),
  (7, 'Travaille à mi-temps, disponible l''après-midi', 1),
  (8, 'Expérience en animation', 1),
  (9, 'Permis B, peut faire les courses', 1),
  (10, 'Compétences en informatique', 1),
  (11, 'Formation premiers secours', 1),
  (12, 'Musicien amateur', 0);

INSERT INTO MECENE (id_utilisateur, organisation, siret, adresse_postale, instagram, facebook) VALUES
  (13, 'Boulangerie Martin', '12345678901234', '12 Rue du Pain, 86000 Poitiers', 'https://instagram.com/boulangerie_martin', 'https://facebook.com/boulangeriemartin'),
  (14, 'Restaurant Le Gourmet', '23456789012345', '45 Avenue de la Gastronomie, 86000 Poitiers', NULL, 'https://facebook.com/legourmet86'),
  (15, 'Librairie des Arts', '34567890123456', '8 Place des Lettres, 86000 Poitiers', 'https://instagram.com/librairiedesarts', NULL),
  (16, 'Spa Zénitude', '45678901234567', '23 Boulevard du Bien-être, 86000 Poitiers', 'https://instagram.com/spa_zenitude', 'https://facebook.com/spazenitude'),
  (17, 'Supermarché Bio Nature', '56789012345678', '156 Route de la Santé, 86000 Poitiers', NULL, NULL),
  (18, 'Garage Auto Vitesse', '67890123456789', '78 Zone Industrielle Nord, 86000 Poitiers', NULL, 'https://facebook.com/garagevitesse');

INSERT INTO WEEKEND (nom, date_debut, date_fin) VALUES
  ('Weekend Groloto 2024', '2024-11-15', '2024-11-17'),
  ('Weekend Groloto 2025', '2025-11-14', '2025-11-16'),
  ('Weekend Solidaire Printemps 2025', '2025-03-21', '2025-03-23'),
  ('Weekend Caritatif Été 2025', '2025-06-20', '2025-06-22'),
  ('Weekend Groloto 2026', '2026-11-13', '2026-11-15');

INSERT INTO EVENEMENT (id_weekend, nom, description, date_debut, date_fin, lieu, heure_debut, duree_minutes) VALUES
  -- Événements du Weekend 2024
  (1, 'Groloto 2024', 'Loto caritatif annuel édition 2024', '2024-11-16', '2024-11-16', 'Salle des fêtes de Poitiers', '14:00:00', 300),
  (1, 'Soirée dansante 2024', 'Bal populaire après le loto', '2024-11-16', '2024-11-16', 'Salle des fêtes de Poitiers', '20:00:00', 240),
  -- Événements du Weekend 2025
  (2, 'Groloto 2025', 'Loto caritatif annuel édition 2025 - Plus de lots que jamais!', '2025-11-15', '2025-11-15', 'Salle des fêtes de Poitiers', '14:00:00', 360),
  (2, 'Brunch solidaire', 'Brunch participatif pour lancer le weekend', '2025-11-14', '2025-11-14', 'Maison des associations', '10:00:00', 180),
  (2, 'Concert de clôture', 'Concert avec artistes locaux', '2025-11-15', '2025-11-15', 'Salle des fêtes de Poitiers', '20:00:00', 180),
  -- Événements hors weekend
  (NULL, 'Tournoi de belote 2025', 'Compétition amicale avec lots pour les gagnants', '2025-01-18', '2025-01-18', 'Maison des associations', '14:00:00', 240),
  (NULL, 'Soirée quizz caritative', 'Quiz généraliste pour récolter des fonds', '2025-02-15', '2025-02-15', 'Bar Le Central', '19:00:00', 180),
  (NULL, 'Vide-grenier Groloto', 'Vide-grenier pour collecter des fonds', '2025-04-12', '2025-04-12', 'Place du marché', '08:00:00', 480),
  (NULL, 'Randonnée solidaire', 'Marche de 10km au profit de l''association', '2025-05-24', '2025-05-24', 'Forêt de Moulière', '09:00:00', 300),
  (NULL, 'Brocante caritative', 'Vente d''objets donnés au profit de l''association', '2025-09-06', '2025-09-06', 'Parking du supermarché', '07:00:00', 600),
  -- Événements Weekend Printemps 2025
  (3, 'Loto de printemps', 'Petit loto printanier avec lots nature', '2025-03-22', '2025-03-22', 'Salle polyvalente', '15:00:00', 180),
  (3, 'Atelier jardinage', 'Initiation au jardinage bio', '2025-03-22', '2025-03-22', 'Jardins partagés', '10:00:00', 120),
  -- Événements Weekend Été 2025
  (4, 'Fête de l''été', 'Grande fête en plein air', '2025-06-21', '2025-06-21', 'Parc de Blossac', '14:00:00', 480),
  (4, 'Concert champêtre', 'Concert en plein air avec pique-nique', '2025-06-21', '2025-06-21', 'Parc de Blossac', '19:00:00', 180),
  (4, 'Tombola géante', 'Tombola avec lots exceptionnels', '2025-06-22', '2025-06-22', 'Parc de Blossac', '11:00:00', 120);

INSERT INTO HISTORIQUE_EVENEMENT (id_evenement, action, description, id_utilisateur, date_action) VALUES
  (1, 'creation', 'Création de l''événement Groloto 2024', 1, '2024-09-01 10:00:00'),
  (1, 'modification', 'Mise à jour du lieu', 1, '2024-10-05 14:30:00'),
  (2, 'creation', 'Création soirée dansante 2024', 1, '2024-09-15 11:00:00'),
  (3, 'creation', 'Création de l''événement Groloto 2025', 1, '2025-08-01 09:00:00'),
  (3, 'modification', 'Augmentation durée à 6h', 1, '2025-09-15 16:00:00'),
  (6, 'creation', 'Création du Tournoi de belote', 1, '2024-12-01 08:00:00'),
  (7, 'creation', 'Création de la Soirée quizz', 1, '2025-01-10 09:00:00'),
  (11, 'creation', 'Création du Loto de printemps', 2, '2025-02-01 10:00:00');

INSERT INTO STOCK (nom, categorie, quantite, unite, seuil, valeur_unitaire, remarque) VALUES
  ('Panier gourmand', 'resto', 20, 'pièces', 5, 25.0, 'Lots donnés par Boulangerie Martin'),
  ('Bon d''achat restaurant 30€', 'resto', 10, 'bons', 3, 30.0, 'Offert par Restaurant Le Gourmet'),
  ('Bon d''achat restaurant 50€', 'resto', 5, 'bons', 2, 50.0, 'Offert par Restaurant Le Gourmet'),
  ('Coffret livres', 'autre', 15, 'coffrets', 5, 35.0, 'Don Librairie des Arts'),
  ('Séance spa duo', 'autre', 8, 'tickets', 2, 80.0, 'Spa Zénitude partenaire'),
  ('Panier bio', 'resto', 25, 'paniers', 10, 40.0, 'Supermarché Bio Nature'),
  ('Vidange offerte', 'autre', 12, 'bons', 5, 60.0, 'Garage Auto Vitesse'),
  ('Pack boissons', 'bar', 100, 'bouteilles', 30, 3.0, 'Pour le bar de l''événement'),
  ('Gobelets réutilisables', 'bar', 500, 'pièces', 100, 0.5, 'Gobelets personnalisés Groloto'),
  ('Décoration de salle', 'deco', 20, 'kits', 5, 15.0, 'Décorations festives'),
  ('Guirlandes lumineuses', 'deco', 30, 'unités', 10, 12.0, 'Pour l''ambiance'),
  ('Tables pliantes', 'autre', 50, 'tables', 20, 0.0, 'Prêtées par la mairie'),
  ('Chaises', 'autre', 300, 'chaises', 100, 0.0, 'Prêtées par la mairie'),
  ('Sono portable', 'autre', 2, 'unités', 1, 0.0, 'Matériel association'),
  ('Lots chocolat', 'resto', 30, 'boîtes', 10, 20.0, 'Chocolatier local');

INSERT INTO HISTORIQUE_STOCK (id_stock, type_changement, quantite, raison, id_utilisateur) VALUES
  (1, 'entree', 20, 'Don initial Boulangerie Martin', 1),
  (2, 'entree', 10, 'Don Restaurant Le Gourmet', 1),
  (3, 'entree', 5, 'Don Restaurant Le Gourmet', 1),
  (4, 'entree', 15, 'Don Librairie des Arts', 1),
  (5, 'entree', 8, 'Partenariat Spa Zénitude', 1),
  (6, 'entree', 25, 'Don Supermarché Bio', 1),
  (7, 'entree', 12, 'Partenariat Garage', 1),
  (8, 'entree', 100, 'Achat boissons', 1),
  (1, 'sortie', 5, 'Lots distribués Groloto 2024', 1),
  (2, 'sortie', 3, 'Lots distribués Groloto 2024', 1);

INSERT INTO LOT (id_mecene, titre, description, quantite, valeur_estimee) VALUES
  (1, 'Panier gourmand premium', 'Composé de produits artisanaux de la boulangerie', 5, 50.0),
  (1, 'Bon d''achat 30€', 'Utilisable dans la boulangerie', 10, 30.0),
  (1, 'Gâteau personnalisé', 'Gâteau sur commande pour événement', 3, 80.0),
  (2, 'Repas gastronomique pour 2', 'Menu complet avec vin', 5, 120.0),
  (2, 'Brunch dominical', 'Brunch à volonté pour 2 personnes', 8, 60.0),
  (3, 'Coffret romans policiers', '5 romans policiers sélectionnés', 10, 45.0),
  (3, 'Bon d''achat librairie 25€', 'Utilisable sur tout le magasin', 15, 25.0),
  (4, 'Journée détente spa', 'Accès spa + 1 soin au choix', 4, 150.0),
  (4, 'Massage relaxant 1h', 'Massage californien ou suédois', 6, 70.0),
  (5, 'Panier bio gourmand', 'Fruits, légumes et produits bio', 20, 50.0),
  (5, 'Bon d''achat 40€', 'Rayon bio uniquement', 10, 40.0),
  (6, 'Révision complète', 'Vidange + contrôle 30 points', 5, 120.0),
  (6, 'Bon carburant 50€', 'Utilisable à la station partenaire', 8, 50.0);

INSERT INTO CONVENTION (id_mecene, nom_modele, url_pdf, date_signature, methode_signature) VALUES
  (1, 'Convention partenariat 2025', '/docs/conventions/conv_boulangerie_2025.pdf', '2025-01-15', 'electronique'),
  (2, 'Convention partenariat 2025', '/docs/conventions/conv_restaurant_2025.pdf', '2025-01-20', 'manuelle'),
  (3, 'Convention partenariat 2025', '/docs/conventions/conv_librairie_2025.pdf', '2025-02-01', 'electronique'),
  (4, 'Convention partenariat 2025', '/docs/conventions/conv_spa_2025.pdf', '2025-02-10', 'electronique'),
  (5, 'Convention partenariat 2025', '/docs/conventions/conv_bio_2025.pdf', '2025-02-15', 'manuelle'),
  (6, 'Convention partenariat 2025', '/docs/conventions/conv_garage_2025.pdf', '2025-03-01', 'manuelle');

INSERT INTO TACHE (id_weekend, id_evenement, titre, poste_requis, debut, fin, max_personnes, remarque) VALUES
  -- Tâches Weekend 2025
  (2, NULL, 'Accueil participants', 'accueil', '2025-11-15 13:00:00', '2025-11-15 15:00:00', 4, 'Accueil et orientation des participants'),
  (2, NULL, 'Service bar', 'bar', '2025-11-15 14:00:00', '2025-11-15 20:00:00', 3, 'Préparer et servir les boissons'),
  (2, NULL, 'Montage scène', 'technique', '2025-11-14 08:00:00', '2025-11-14 12:00:00', 5, 'Montage de la scène et sonorisation'),
  (2, NULL, 'Démontage', 'technique', '2025-11-16 08:00:00', '2025-11-16 12:00:00', 6, 'Démontage et rangement'),
  (2, NULL, 'Vente cartons loto', 'vente', '2025-11-15 13:30:00', '2025-11-15 14:30:00', 6, 'Vente des cartons aux participants'),
  (2, NULL, 'Animation scène', 'animation', '2025-11-15 14:00:00', '2025-11-15 18:00:00', 2, 'Animation du loto au micro'),
  (2, NULL, 'Cuisine / Restauration', 'cuisine', '2025-11-15 11:00:00', '2025-11-15 14:00:00', 4, 'Préparation des repas'),
  (2, NULL, 'Service restauration', 'service', '2025-11-15 12:00:00', '2025-11-15 14:00:00', 5, 'Service des repas'),
  (2, NULL, 'Sécurité parking', 'securite', '2025-11-15 13:00:00', '2025-11-15 20:00:00', 2, 'Gestion du parking'),
  (2, NULL, 'Vestiaire', 'accueil', '2025-11-15 13:00:00', '2025-11-15 20:00:00', 2, 'Tenue du vestiaire'),
  -- Tâches Weekend Printemps 2025
  (3, NULL, 'Accueil printemps', 'accueil', '2025-03-22 14:00:00', '2025-03-22 16:00:00', 3, 'Accueil loto printemps'),
  (3, NULL, 'Bar printemps', 'bar', '2025-03-22 14:00:00', '2025-03-22 18:00:00', 2, 'Service boissons'),
  (3, NULL, 'Animation jardinage', 'animation', '2025-03-22 10:00:00', '2025-03-22 12:00:00', 2, 'Encadrement atelier'),
  -- Tâches Weekend Été 2025
  (4, NULL, 'Installation parc', 'technique', '2025-06-21 08:00:00', '2025-06-21 12:00:00', 8, 'Installation des stands'),
  (4, NULL, 'Accueil fête été', 'accueil', '2025-06-21 13:30:00', '2025-06-21 22:00:00', 4, 'Accueil public'),
  (4, NULL, 'Buvette été', 'bar', '2025-06-21 14:00:00', '2025-06-21 22:00:00', 4, 'Tenue de la buvette'),
  (4, NULL, 'Tombola', 'vente', '2025-06-22 10:30:00', '2025-06-22 12:30:00', 3, 'Vente et tirage tombola'),
  (4, NULL, 'Rangement parc', 'technique', '2025-06-22 14:00:00', '2025-06-22 18:00:00', 8, 'Démontage et nettoyage'),
  -- Tâches avec weekend 1 (2024) pour événements hors weekend
  (1, 6, 'Organisation belote', 'animation', '2025-01-18 13:00:00', '2025-01-18 19:00:00', 3, 'Gestion du tournoi'),
  (1, 7, 'Animation quizz', 'animation', '2025-02-15 18:00:00', '2025-02-15 22:00:00', 2, 'Maître du quizz'),
  (1, 8, 'Gestion stands vide-grenier', 'accueil', '2025-04-12 06:00:00', '2025-04-12 15:00:00', 4, 'Attribution emplacements'),
  (1, 9, 'Encadrement randonnée', 'animation', '2025-05-24 08:00:00', '2025-05-24 14:00:00', 3, 'Guides et serre-file'),
  (1, 10, 'Caisse brocante', 'vente', '2025-09-06 07:00:00', '2025-09-06 17:00:00', 2, 'Encaissement ventes');

INSERT INTO PLAGE_HORAIRE (id_tache, jour, heure_debut, heure_fin, max_personnes_plage) VALUES
  (1, '2025-11-15', '13:00:00', '14:00:00', 2),
  (1, '2025-11-15', '14:00:00', '15:00:00', 2),
  (2, '2025-11-15', '14:00:00', '16:00:00', 2),
  (2, '2025-11-15', '16:00:00', '18:00:00', 2),
  (2, '2025-11-15', '18:00:00', '20:00:00', 2),
  (3, '2025-11-14', '08:00:00', '10:00:00', 3),
  (3, '2025-11-14', '10:00:00', '12:00:00', 3),
  (5, '2025-11-15', '13:30:00', '14:00:00', 3),
  (5, '2025-11-15', '14:00:00', '14:30:00', 3),
  (6, '2025-11-15', '14:00:00', '16:00:00', 1),
  (6, '2025-11-15', '16:00:00', '18:00:00', 1);

INSERT INTO AFFECTATION_TACHE (id_tache, id_benevole, id_utilisateur, statut, remarque) VALUES
  (1, 1, 1, 'confirme', 'Alice affectée à l''accueil'),
  (1, 3, 1, 'confirme', 'Marie en renfort accueil'),
  (2, 2, 1, 'confirme', 'Bob au bar'),
  (2, 5, 1, 'confirme', 'Lucie au bar'),
  (3, 4, 1, 'confirme', 'Pierre montage scène'),
  (3, 6, 1, 'confirme', 'Thomas montage scène'),
  (3, 8, 1, 'confirme', 'Lucas montage scène'),
  (5, 7, 1, 'assigne', 'Emma vente cartons'),
  (6, 4, 1, 'confirme', 'Pierre à l''animation'),
  (7, 3, 1, 'assigne', 'Marie en cuisine'),
  (9, 9, 1, 'confirme', 'Chloé parking'),
  (14, 1, 2, 'assigne', 'Alice installation parc été'),
  (14, 2, 2, 'assigne', 'Bob installation parc été');

INSERT INTO DISPONIBILITE_BENEVOLE (id_benevole, id_evenement, debut, fin, remarque) VALUES
  (1, 3, '2025-11-15 12:00:00', '2025-11-15 20:00:00', 'Disponible tout l''après-midi'),
  (2, 3, '2025-11-15 14:00:00', '2025-11-15 22:00:00', 'Après 14h seulement'),
  (3, 3, '2025-11-14 08:00:00', '2025-11-16 18:00:00', 'Disponible tout le weekend'),
  (4, 3, '2025-11-15 08:00:00', '2025-11-15 18:00:00', 'Journée complète samedi'),
  (5, 3, '2025-11-15 18:00:00', '2025-11-15 23:00:00', 'Soir uniquement'),
  (1, 13, '2025-06-21 10:00:00', '2025-06-21 22:00:00', 'Dispo fête été'),
  (2, 13, '2025-06-21 14:00:00', '2025-06-22 14:00:00', 'Dispo weekend complet');

INSERT INTO COMMUNICATION (id_evenement, titre, type, date_prevue, statut, budget, remarque) VALUES
  (3, 'Campagne Facebook Groloto 2025', 'post', '2025-10-01 10:00:00', 'programme', 100.0, 'Campagne sponsorisée Facebook'),
  (3, 'Affiches Groloto 2025', 'affiche', '2025-10-15 09:00:00', 'programme', 80.0, '200 affiches A3 couleur'),
  (3, 'Flyers distribution', 'flyer', '2025-10-20 14:00:00', 'programme', 50.0, '1000 flyers A5'),
  (3, 'Article presse locale', 'autre', '2025-11-01 08:00:00', 'programme', 0.0, 'Article gratuit Nouvelle République'),
  (1, 'Bilan Facebook 2024', 'post', '2024-10-15 10:00:00', 'fait', 50.0, 'Campagne sponsorisée'),
  (1, 'Affiches 2024', 'affiche', '2024-10-20 09:00:00', 'fait', 60.0, 'Affiches distribuées'),
  (11, 'Affiche loto printemps', 'affiche', '2025-03-01 10:00:00', 'programme', 30.0, '50 affiches A4'),
  (13, 'Communication fête été', 'post', '2025-05-15 10:00:00', 'programme', 150.0, 'Campagne réseaux sociaux');

INSERT INTO HELLOASSO (id_evenement, id_externe, prenom, nom, email, telephone, type_ticket, date_achat) VALUES
  (3, 'HA-2025-001', 'Paul', 'Lemoine', 'paul.lemoine@example.com', '0611223344', 'Entrée standard', '2025-10-15 15:00:00'),
  (3, 'HA-2025-002', 'Claire', 'Dubois', 'claire.dubois@example.com', '0622334455', 'Entrée standard', '2025-10-16 10:00:00'),
  (3, 'HA-2025-003', 'Marc', 'Petit', 'marc.petit@example.com', '0633445566', 'Entrée VIP', '2025-10-17 14:00:00'),
  (3, 'HA-2025-004', 'Julie', 'Martin', 'julie.martin@example.com', '0644556677', 'Entrée standard', '2025-10-18 09:00:00'),
  (3, 'HA-2025-005', 'Antoine', 'Bernard', 'antoine.bernard@example.com', '0655667788', 'Entrée standard', '2025-10-19 16:00:00'),
  (1, 'HA-2024-001', 'Sophie', 'Roux', 'sophie.roux@example.com', '0666778899', 'Entrée standard', '2024-10-10 11:00:00'),
  (1, 'HA-2024-002', 'Nicolas', 'Fournier', 'nicolas.fournier@example.com', '0677889900', 'Entrée standard', '2024-10-12 14:00:00'),
  (13, 'HA-2025-ETE-001', 'Laura', 'Simon', 'laura.simon@example.com', '0688990011', 'Entrée famille', '2025-06-01 10:00:00');

INSERT INTO PARAMETRE (cle, valeur) VALUES
  ('site_name', 'Groloto Manager'),
  ('devise', '€'),
  ('email_contact', 'contact@groloto.local'),
  ('telephone_contact', '05 49 00 00 00'),
  ('adresse_association', '6 rue de Blossac, 86000 Poitiers'),
  ('facebook_url', 'https://facebook.com/groloto'),
  ('instagram_url', 'https://instagram.com/groloto_asso');

INSERT INTO NOTIFICATION (id_destinataire, type, message, lue, created_at) VALUES
  (3, 'info', 'Bienvenue dans l''équipe des bénévoles Groloto !', 1, datetime('now', '-30 days')),
  (3, 'info', 'Une nouvelle tâche "Accueil" est disponible pour le weekend du 15 novembre.', 1, datetime('now', '-10 days')),
  (3, 'success', 'Votre affectation à la tâche "Accueil participants" a été confirmée.', 0, datetime('now', '-5 days')),
  (4, 'info', 'Bienvenue dans l''équipe des bénévoles Groloto !', 1, datetime('now', '-25 days')),
  (4, 'warning', 'N''oubliez pas le weekend Groloto 2025 dans 2 semaines !', 0, datetime('now', '-2 days')),
  (13, 'success', 'Merci ! Votre convention de partenariat a bien été enregistrée.', 1, datetime('now', '-20 days')),
  (5, 'success', 'Votre demande pour la tâche "Cuisine" a été acceptée.', 0, datetime('now', '-3 days'));
