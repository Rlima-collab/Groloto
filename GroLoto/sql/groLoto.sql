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
DROP TABLE IF EXISTS DISPONIBILITE_WEEKEND;
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
  task_offset_before INTEGER DEFAULT 2 NOT NULL,
  task_offset_after INTEGER DEFAULT 3 NOT NULL,
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

-- DISPONIBILITE_BENEVOLE (ancienne table - conservée pour compatibilité)
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

-- DISPONIBILITE_WEEKEND (nouvelle table - disponibilités par weekend)
CREATE TABLE DISPONIBILITE_WEEKEND (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_benevole INTEGER NOT NULL,
    id_weekend INTEGER NOT NULL,
    jour DATE NOT NULL,
    matin BOOLEAN DEFAULT 0,
    apres_midi BOOLEAN DEFAULT 0,
    soir BOOLEAN DEFAULT 0,
    remarque TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
    FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id),
    UNIQUE(id_benevole, id_weekend, jour)
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

INSERT INTO WEEKEND (nom, date_debut, date_fin, task_offset_before, task_offset_after, cover_image, description) VALUES
  ('Weekend Groloto 2024', '2024-11-15', '2024-11-17', 2, 3, '/uploads/covers/weekend-2024.jpg', 'Premier grand weekend Groloto avec loto caritatif et soirée dansante'),
  ('Weekend Groloto 2025', '2025-11-14', '2025-11-16', 2, 3, '/uploads/covers/weekend-2025.jpg', 'Édition 2025 du weekend Groloto - Programme enrichi avec brunch et concert'),
  ('Weekend Solidaire Printemps 2025', '2025-03-21', '2025-03-23', 1, 2, '/uploads/covers/printemps-2025.jpg', 'Weekend thématique printemps avec loto nature et atelier jardinage'),
  ('Weekend Caritatif Été 2025', '2025-06-20', '2025-06-22', 3, 3, '/uploads/covers/ete-2025.jpg', 'Grande fête d''été en plein air au parc de Blossac'),
  ('Weekend Groloto 2026', '2026-11-13', '2026-11-15', 2, 3, NULL, 'Prochain weekend Groloto annuel - Planification en cours'),
  ('Weekend de la Galette 2025', '2025-01-17', '2025-01-19', 1, 1, '/uploads/covers/galette-2025.jpg', 'Weekend festif autour de la galette des rois avec jeux et animations'),
  ('Weekend Carnaval 2025', '2025-02-28', '2025-03-02', 2, 2, '/uploads/covers/carnaval-2025.jpg', 'Weekend masqué avec défilé, concours de déguisements et bal costumé'),
  ('Weekend Pâques Solidaire 2025', '2025-04-19', '2025-04-21', 1, 2, '/uploads/covers/paques-2025.jpg', 'Chasse aux œufs géante, brunch et activités familiales'),
  ('Weekend Fête de la Musique 2025', '2025-06-20', '2025-06-22', 2, 2, '/uploads/covers/musique-2025.jpg', 'Concerts gratuits, scène ouverte et animations musicales'),
  ('Weekend de la Rentrée 2025', '2025-09-12', '2025-09-14', 2, 3, '/uploads/covers/rentree-2025.jpg', 'Forum associatif, vide-grenier et brocante solidaire'),
  ('Weekend Halloween 2025', '2025-10-31', '2025-11-02', 2, 2, '/uploads/covers/halloween-2025.jpg', 'Soirée d''épouvante, jeux effrayants et concours du meilleur costume'),
  ('Weekend Téléthon 2025', '2025-12-05', '2025-12-07', 3, 2, '/uploads/covers/telethon-2025.jpg', 'Mobilisation exceptionnelle pour le Téléthon avec challenges sportifs'),
  ('Weekend Marché de Noël 2025', '2025-12-19', '2025-12-21', 2, 3, '/uploads/covers/noel-2025.jpg', 'Marché de Noël artisanal, vin chaud et animations pour enfants'),
  ('Weekend Galette 2026', '2026-01-16', '2026-01-18', 1, 1, '/uploads/covers/galette-2026.jpg', 'Nouvelle édition du weekend galette avec programme renouvelé - Dégustation de galettes artisanales, jeux traditionnels et soirée conviviale'),
  ('Weekend Saint-Valentin Solidaire 2026', '2026-02-13', '2026-02-15', 2, 2, '/uploads/covers/valentin-2026.jpg', 'Weekend romantique et caritatif avec dîner aux chandelles, bal des amoureux et collecte pour les personnes isolées'),
  ('Weekend du Printemps 2026', '2026-03-27', '2026-03-29', 2, 2, '/uploads/covers/printemps-2026.jpg', 'Weekend célébrant l''arrivée du printemps avec marché aux fleurs, ateliers jardinage bio et randonnée nature guidée'),
  ('Weekend Pâques 2026', '2026-04-03', '2026-04-05', 2, 3, '/uploads/covers/paques-2026.jpg', 'Grande chasse aux œufs dans le parc, brunch pascal familial, ateliers créatifs pour enfants et marché artisanal de Pâques'),
  ('Weekend Fête du Travail 2026', '2026-05-01', '2026-05-03', 1, 2, '/uploads/covers/1er-mai-2026.jpg', 'Célébration du 1er mai avec marché solidaire, vente de muguet au profit de l''association et concert populaire gratuit'),
  ('Weekend Festival de l''Été 2026', '2026-06-19', '2026-06-21', 3, 3, '/uploads/covers/festival-ete-2026.jpg', 'Grand festival estival avec concerts, food trucks, activités nautiques et spectacles de rue - 3 jours de festivités en plein air'),
  ('Weekend Fête Nationale 2026', '2026-07-13', '2026-07-15', 2, 2, '/uploads/covers/14juillet-2026.jpg', 'Weekend patriotique avec bal populaire du 13 juillet, feu d''artifice, défilé et activités familiales pour célébrer le 14 juillet'),
  ('Weekend Rentrée Solidaire 2026', '2026-09-04', '2026-09-06', 2, 3, '/uploads/covers/rentree-2026.jpg', 'Forum des associations de rentrée, vide-grenier géant, collecte de fournitures scolaires et concert de rentrée'),
  ('Weekend Vendanges Caritatives 2026', '2026-09-25', '2026-09-27', 2, 2, '/uploads/covers/vendanges-2026.jpg', 'Weekend thématique autour du vin avec dégustation responsable, loto des vendanges, repas du terroir et animation folklorique'),
  ('Weekend Halloween 2026', '2026-10-30', '2026-11-01', 2, 2, '/uploads/covers/halloween-2026.jpg', 'Frissons garantis avec parcours hanté, soirée costumée, chasse aux bonbons pour enfants et projection de films d''horreur cultes'),
  ('Weekend Téléthon 2026', '2026-12-04', '2026-12-06', 3, 2, '/uploads/covers/telethon-2026.jpg', 'Mobilisation totale pour le Téléthon avec marathon, tournois sportifs, vente de crêpes et grand concert solidaire'),
  ('Weekend Marché de Noël 2026', '2026-12-18', '2026-12-20', 2, 3, '/uploads/covers/noel-2026.jpg', 'Village de Noël féérique avec chalets artisanaux, patinoire, rencontre avec le Père Noël et concerts de chants traditionnels');

INSERT INTO EVENEMENT (id_weekend, nom, description, date_debut, date_fin, lieu, heure_debut, duree_minutes, image) VALUES
  -- Événements du Weekend 2024 (id_weekend = 1)
  (1, 'Groloto 2024', 'Loto caritatif annuel édition 2024', '2024-11-16', '2024-11-16', 'Salle des fêtes de Poitiers', '14:00:00', 300, '/uploads/events/groloto-2024.jpg'),
  (1, 'Soirée dansante 2024', 'Bal populaire après le loto', '2024-11-16', '2024-11-16', 'Salle des fêtes de Poitiers', '20:00:00', 240, '/uploads/events/bal-2024.jpg'),
  -- Événements du Weekend 2025 (id_weekend = 2)
  (2, 'Groloto 2025', 'Loto caritatif annuel édition 2025 - Plus de lots que jamais!', '2025-11-15', '2025-11-15', 'Salle des fêtes de Poitiers', '14:00:00', 360, '/uploads/events/groloto-2025.jpg'),
  (2, 'Brunch solidaire', 'Brunch participatif pour lancer le weekend', '2025-11-14', '2025-11-14', 'Maison des associations', '10:00:00', 180, '/uploads/events/brunch-2025.jpg'),
  (2, 'Concert de clôture', 'Concert avec artistes locaux', '2025-11-15', '2025-11-15', 'Salle des fêtes de Poitiers', '20:00:00', 180, '/uploads/events/concert-2025.jpg'),
  -- Événements hors weekend
  (NULL, 'Tournoi de belote 2025', 'Compétition amicale avec lots pour les gagnants', '2025-01-18', '2025-01-18', 'Maison des associations', '14:00:00', 240, NULL),
  (NULL, 'Soirée quizz caritative', 'Quiz généraliste pour récolter des fonds', '2025-02-15', '2025-02-15', 'Bar Le Central', '19:00:00', 180, NULL),
  (NULL, 'Vide-grenier Groloto', 'Vide-grenier pour collecter des fonds', '2025-04-12', '2025-04-12', 'Place du marché', '08:00:00', 480, NULL),
  (NULL, 'Randonnée solidaire', 'Marche de 10km au profit de l''association', '2025-05-24', '2025-05-24', 'Forêt de Moulière', '09:00:00', 300, NULL),
  (NULL, 'Brocante caritative', 'Vente d''objets donnés au profit de l''association', '2025-09-06', '2025-09-06', 'Parking du supermarché', '07:00:00', 600, NULL),
  -- Événements Weekend Printemps 2025 (id_weekend = 3)
  (3, 'Loto de printemps', 'Petit loto printanier avec lots nature', '2025-03-22', '2025-03-22', 'Salle polyvalente', '15:00:00', 180, '/uploads/events/loto-printemps.jpg'),
  (3, 'Atelier jardinage', 'Initiation au jardinage bio', '2025-03-22', '2025-03-22', 'Jardins partagés', '10:00:00', 120, NULL),
  -- Événements Weekend Été 2025 (id_weekend = 4)
  (4, 'Fête de l''été', 'Grande fête en plein air', '2025-06-21', '2025-06-21', 'Parc de Blossac', '14:00:00', 480, '/uploads/events/fete-ete.jpg'),
  (4, 'Concert champêtre', 'Concert en plein air avec pique-nique', '2025-06-21', '2025-06-21', 'Parc de Blossac', '19:00:00', 180, NULL),
  (4, 'Tombola géante', 'Tombola avec lots exceptionnels', '2025-06-22', '2025-06-22', 'Parc de Blossac', '11:00:00', 120, NULL),
  -- Événements Weekend Galette 2025 (id_weekend = 6)
  (6, 'Grand tirage de la galette', 'Dégustation de galettes et tirage des rois', '2025-01-18', '2025-01-18', 'Salle municipale', '14:00:00', 180, '/uploads/events/galette.jpg'),
  (6, 'Atelier pâtisserie enfants', 'Les enfants préparent leur propre galette', '2025-01-18', '2025-01-18', 'Cuisine associative', '10:00:00', 120, NULL),
  (6, 'Soirée jeux de société', 'Tournoi de jeux de plateau en équipe', '2025-01-18', '2025-01-18', 'Salle municipale', '20:00:00', 240, NULL),
  -- Événements Weekend Carnaval 2025 (id_weekend = 7)
  (7, 'Défilé de carnaval', 'Grand défilé dans les rues du centre-ville', '2025-03-01', '2025-03-01', 'Centre-ville de Poitiers', '14:00:00', 120, '/uploads/events/defile-carnaval.jpg'),
  (7, 'Concours de déguisements', 'Compétition pour le meilleur costume avec prix', '2025-03-01', '2025-03-01', 'Place du marché', '16:00:00', 90, NULL),
  (7, 'Bal masqué', 'Soirée dansante en costume', '2025-03-01', '2025-03-01', 'Salle des fêtes', '20:00:00', 300, '/uploads/events/bal-masque.jpg'),
  (7, 'Atelier maquillage', 'Maquillage artistique gratuit pour tous', '2025-03-01', '2025-03-01', 'Stand place du marché', '13:00:00', 180, NULL),
  -- Événements Weekend Pâques 2025 (id_weekend = 8)
  (8, 'Chasse aux œufs géante', 'Chasse aux œufs dans le parc pour petits et grands', '2025-04-20', '2025-04-20', 'Parc de Blossac', '10:00:00', 120, '/uploads/events/chasse-oeufs.jpg'),
  (8, 'Brunch de Pâques', 'Brunch pascal en plein air', '2025-04-20', '2025-04-20', 'Parc de Blossac', '11:00:00', 150, NULL),
  (8, 'Atelier décoration d''œufs', 'Activité créative pour enfants', '2025-04-20', '2025-04-20', 'Chapiteau animations', '14:00:00', 120, NULL),
  (8, 'Spectacle de marionnettes', 'Conte de Pâques pour les enfants', '2025-04-20', '2025-04-20', 'Scène plein air', '16:00:00', 60, NULL),
  -- Événements Weekend Fête de la Musique 2025 (id_weekend = 9)
  (9, 'Concert rock local', 'Groupes de rock de la région', '2025-06-21', '2025-06-21', 'Scène principale place du marché', '18:00:00', 180, '/uploads/events/concert-rock.jpg'),
  (9, 'Scène jazz', 'Soirée jazz et blues', '2025-06-21', '2025-06-21', 'Terrasse bar Le Central', '20:00:00', 240, NULL),
  (9, 'Scène ouverte', 'Musiciens amateurs bienvenus', '2025-06-21', '2025-06-21', 'Scène secondaire', '14:00:00', 300, NULL),
  (9, 'Atelier percussion', 'Initiation aux percussions africaines', '2025-06-21', '2025-06-21', 'Chapiteau animations', '15:00:00', 90, NULL),
  -- Événements Weekend Rentrée 2025 (id_weekend = 10)
  (10, 'Forum des associations', 'Découverte des associations locales', '2025-09-13', '2025-09-13', 'Salle polyvalente', '10:00:00', 360, '/uploads/events/forum-asso.jpg'),
  (10, 'Vide-grenier de rentrée', 'Braderie pour faire de bonnes affaires', '2025-09-13', '2025-09-13', 'Parking municipal', '08:00:00', 480, NULL),
  (10, 'Brocante solidaire', 'Vente au profit des associations', '2025-09-13', '2025-09-13', 'Place du marché', '09:00:00', 420, NULL),
  (10, 'Concert de rentrée', 'Concert pop-rock pour bien commencer l''année', '2025-09-13', '2025-09-13', 'Salle des fêtes', '20:00:00', 180, NULL),
  -- Événements Weekend Halloween 2025 (id_weekend = 11)
  (11, 'Soirée d''épouvante', 'Soirée effrayante avec maison hantée', '2025-10-31', '2025-10-31', 'Ancienne usine désaffectée', '19:00:00', 240, '/uploads/events/halloween.jpg'),
  (11, 'Concours meilleur costume', 'Qui sera le plus effrayant?', '2025-10-31', '2025-10-31', 'Place centrale', '18:00:00', 60, NULL),
  (11, 'Chasse aux bonbons', 'Pour les enfants dans le quartier', '2025-10-31', '2025-10-31', 'Centre-ville', '17:00:00', 120, NULL),
  (11, 'Projection film d''horreur', 'Classique du cinéma d''épouvante', '2025-10-31', '2025-10-31', 'Cinéma municipal', '21:00:00', 120, NULL),
  -- Événements Weekend Téléthon 2025 (id_weekend = 12)
  (12, 'Marathon Téléthon', 'Course solidaire de 10km', '2025-12-06', '2025-12-06', 'Parc de Blossac', '09:00:00', 180, '/uploads/events/marathon.jpg'),
  (12, 'Tournoi de foot', 'Tournoi inter-associations', '2025-12-06', '2025-12-06', 'Stade municipal', '14:00:00', 300, NULL),
  (12, 'Zumba géante', 'Session de Zumba collective', '2025-12-06', '2025-12-06', 'Place du marché', '11:00:00', 90, NULL),
  (12, 'Vente de crêpes', 'Stand crêpes au profit du Téléthon', '2025-12-06', '2025-12-06', 'Stand place centrale', '10:00:00', 480, NULL),
  (12, 'Concert caritatif', 'Concert de soutien au Téléthon', '2025-12-06', '2025-12-06', 'Salle des fêtes', '20:00:00', 180, NULL),
  -- Événements Weekend Marché de Noël 2025 (id_weekend = 13)
  (13, 'Marché de Noël artisanal', 'Chalets avec artisans locaux', '2025-12-20', '2025-12-21', 'Place du marché', '10:00:00', 600, '/uploads/events/marche-noel.jpg'),
  (13, 'Rencontre avec le Père Noël', 'Photos avec le Père Noël', '2025-12-20', '2025-12-21', 'Chalet central', '14:00:00', 240, NULL),
  (13, 'Atelier décorations de Noël', 'Création de décorations avec les enfants', '2025-12-20', '2025-12-20', 'Chapiteau animations', '14:00:00', 120, NULL),
  (13, 'Concert de chants de Noël', 'Chorales et chants traditionnels', '2025-12-20', '2025-12-20', 'Scène place du marché', '18:00:00', 90, NULL),
  (13, 'Patinoire de Noël', 'Patinoire éphémère en plein air', '2025-12-20', '2025-12-21', 'Esplanade', '10:00:00', 720, '/uploads/events/patinoire.jpg'),
  -- Événements Weekend Galette 2026 (id_weekend = 14)
  (14, 'Grand tirage de la galette 2026', 'Dégustation de galettes des rois artisanales avec tirage au sort des fèves dorées - Animation musicale avec accordéoniste', '2026-01-17', '2026-01-17', 'Salle municipale Saint-Cyprien', '14:30:00', 180, '/uploads/events/galette-2026-tirage.jpg'),
  (14, 'Atelier confection galettes', 'Atelier pâtisserie pour petits et grands - Apprenez à préparer votre propre galette avec un chef pâtissier professionnel', '2026-01-17', '2026-01-17', 'Cuisine pédagogique Maison des associations', '10:00:00', 150, '/uploads/events/atelier-galette.jpg'),
  (14, 'Tournoi de pétanque des rois', 'Compétition amicale de pétanque avec couronnes pour les vainqueurs - Lots gourmands à gagner', '2026-01-17', '2026-01-17', 'Boulodrome municipal', '14:00:00', 240, NULL),
  (14, 'Soirée jeux de cartes conviviale', 'Belote, tarot et autres jeux traditionnels - Ambiance chaleureuse autour d''une galette et d''un verre de cidre', '2026-01-17', '2026-01-17', 'Café associatif Le Relais', '20:00:00', 180, NULL),
  -- Événements Weekend Saint-Valentin 2026 (id_weekend = 15)
  (15, 'Dîner aux chandelles solidaire', 'Repas romantique préparé par des chefs bénévoles - Menu gastronomique 3 services avec accord mets-vins - Bénéfices reversés aux personnes isolées', '2026-02-14', '2026-02-14', 'Restaurant Le Gourmet', '19:30:00', 180, '/uploads/events/diner-chandelles.jpg'),
  (15, 'Bal des amoureux', 'Soirée dansante sur les plus belles chansons d''amour - DJ et animation - Photobooth romantique gratuit', '2026-02-14', '2026-02-14', 'Salle des fêtes décorée', '21:00:00', 240, '/uploads/events/bal-amoureux.jpg'),
  (15, 'Atelier création de cartes Saint-Valentin', 'Confection de cartes artisanales destinées aux personnes âgées en EHPAD - Matériel fourni - Moment créatif et solidaire', '2026-02-14', '2026-02-14', 'Maison des associations', '14:00:00', 120, NULL),
  (15, 'Vente de roses solidaires', 'Stand de vente de roses au profit de l''association - Possibilité de faire livrer vos roses dans Poitiers', '2026-02-14', '2026-02-14', 'Place du marché Notre-Dame', '09:00:00', 480, NULL),
  -- Événements Weekend Printemps 2026 (id_weekend = 16)
  (16, 'Marché aux fleurs printanier', 'Grand marché de plantes, fleurs et arbustes avec producteurs locaux - Conseils jardinage gratuits - Vente au profit de l''association', '2026-03-28', '2026-03-28', 'Parvis de l''Hôtel de Ville', '09:00:00', 420, '/uploads/events/marche-fleurs.jpg'),
  (16, 'Atelier jardinage bio et permaculture', 'Initiation au jardinage écologique - Création d''un potager en carrés - Techniques de compostage - Animé par un expert permaculteur', '2026-03-28', '2026-03-28', 'Jardins partagés du Porteau', '14:00:00', 180, '/uploads/events/atelier-jardinage.jpg'),
  (16, 'Randonnée nature guidée', 'Balade de 12km à travers les sentiers du bois de Saint-Pierre - Découverte de la faune et flore printanière avec guide naturaliste - Pique-nique tiré du sac', '2026-03-29', '2026-03-29', 'Départ parking Bois de Saint-Pierre', '09:30:00', 240, '/uploads/events/rando-printemps.jpg'),
  (16, 'Bourse aux plantes', 'Échange et troc de graines, boutures et plants entre jardiniers amateurs - Gratuit et convivial', '2026-03-28', '2026-03-28', 'Maison de quartier des Trois-Cités', '10:00:00', 180, NULL),
  -- Événements Weekend Pâques 2026 (id_weekend = 17)
  (17, 'Grande chasse aux œufs du parc', 'Plus de 5000 œufs cachés dans le parc de Blossac pour une chasse géante - Plusieurs zones par tranche d''âge - Lots et chocolats à gagner', '2026-04-05', '2026-04-05', 'Parc de Blossac', '10:00:00', 150, '/uploads/events/chasse-oeufs-2026.jpg'),
  (17, 'Brunch pascal familial', 'Grand brunch en plein air avec produits locaux - Formule buffet à volonté - Animation musicale douce - Réservation recommandée', '2026-04-05', '2026-04-05', 'Terrasse du Parc de Blossac', '11:00:00', 180, '/uploads/events/brunch-paques.jpg'),
  (17, 'Ateliers créatifs pour enfants', 'Décoration d''œufs, bricolages de Pâques, maquillage lapin - Plusieurs ateliers simultanés - Encadrement par animateurs diplômés', '2026-04-05', '2026-04-05', 'Chapiteaux animations', '14:00:00', 180, '/uploads/events/ateliers-paques.jpg'),
  (17, 'Marché artisanal de Pâques', 'Artisans locaux proposant créations chocolatées, décorations printanières et produits du terroir - Démonstrations en direct', '2026-04-04', '2026-04-05', 'Place du Marché Notre-Dame', '10:00:00', 600, NULL),
  (17, 'Spectacle de clowns Les Joyeux Lapins', 'Spectacle comique familial avec jonglage, acrobaties et magie - Gratuit pour tous - Durée 45 minutes', '2026-04-05', '2026-04-05', 'Scène du parc', '16:00:00', 60, NULL),
  -- Événements Weekend Fête du Travail 2026 (id_weekend = 18)
  (18, 'Marché solidaire du 1er mai', 'Stands associatifs, artisans locaux et producteurs engagés dans le commerce équitable - Restauration sur place', '2026-05-01', '2026-05-01', 'Place du Maréchal Leclerc', '10:00:00', 420, '/uploads/events/marche-1mai.jpg'),
  (18, 'Vente de muguet caritatif', 'Brins de muguet vendus au profit de l''association - Distribution dans tout le centre-ville - Bénévoles mobilisés', '2026-05-01', '2026-05-01', 'Divers points de vente centre-ville', '08:00:00', 540, '/uploads/events/muguet.jpg'),
  (18, 'Concert populaire gratuit', 'Groupe de musique folk et chanson française - Ambiance conviviale et familiale - Apportez vos couvertures pour vous installer', '2026-05-01', '2026-05-01', 'Parc de Blossac scène principale', '18:00:00', 180, '/uploads/events/concert-1mai.jpg'),
  (18, 'Défilé des associations', 'Défilé festif des associations poitevines avec fanfares et chars décorés - Parcours dans le centre historique', '2026-05-01', '2026-05-01', 'Départ Place d''Armes', '15:00:00', 120, NULL),
  -- Événements Weekend Festival Été 2026 (id_weekend = 19)
  (19, 'Scène rock et électro', 'Têtes d''affiche rock française et DJ sets électro - Scène principale - Buvette et restauration sur place', '2026-06-20', '2026-06-20', 'Parc de Blossac grande scène', '18:00:00', 360, '/uploads/events/scene-rock.jpg'),
  (19, 'Village des food trucks', 'Plus de 15 food trucks proposant cuisines du monde, spécialités régionales et desserts gourmands - Espace tables et chaises', '2026-06-19', '2026-06-21', 'Esplanade du parc', '12:00:00', 600, '/uploads/events/foodtrucks.jpg'),
  (19, 'Activités nautiques sur le Clain', 'Initiation paddle, canoë-kayak, pédalo - Encadrement par moniteurs diplômés - Gilets fournis - Gratuit sur inscription', '2026-06-20', '2026-06-21', 'Base nautique du Clain', '10:00:00', 480, '/uploads/events/nautique.jpg'),
  (19, 'Spectacles de rue itinérants', 'Jongleurs, cracheurs de feu, échassiers et musiciens ambulants - Déambulations dans tout le festival', '2026-06-19', '2026-06-21', 'Parc de Blossac divers espaces', '14:00:00', 420, NULL),
  (19, 'Scène jazz et world music', 'Concerts jazz, reggae, musiques du monde - Scène ombragée - Ambiance lounge avec transats', '2026-06-20', '2026-06-21', 'Scène jardin du parc', '15:00:00', 300, '/uploads/events/scene-jazz.jpg'),
  (19, 'Zone enfants avec structures gonflables', 'Châteaux gonflables géants, parcours d''obstacles, trampolines - Surveillance assurée - Gratuit pour les enfants', '2026-06-19', '2026-06-21', 'Prairie du parc', '10:00:00', 600, NULL),
  -- Événements Weekend 14 Juillet 2026 (id_weekend = 20)
  (20, 'Bal populaire du 13 juillet', 'Soirée dansante traditionnelle avec orchestre - Répertoire varié des années 60 à aujourd''hui - Entrée gratuite', '2026-07-13', '2026-07-13', 'Place d''Armes', '21:00:00', 240, '/uploads/events/bal-14juillet.jpg'),
  (20, 'Grand feu d''artifice', 'Spectacle pyrotechnique exceptionnel de 30 minutes tiré depuis les bords du Clain - Meilleur point de vue au parc de Blossac', '2026-07-14', '2026-07-14', 'Parc de Blossac', '23:00:00', 30, '/uploads/events/feu-artifice.jpg'),
  (20, 'Défilé militaire et civil', 'Défilé des pompiers, associations patriotiques et fanfares - Cérémonie aux monuments aux morts', '2026-07-14', '2026-07-14', 'Avenue du Général de Gaulle', '10:00:00', 90, NULL),
  (20, 'Village associatif tricolore', 'Stands d''associations, jeux pour enfants, maquillage aux couleurs de la France - Buvette et restauration', '2026-07-14', '2026-07-14', 'Parvis de l''Hôtel de Ville', '14:00:00', 360, NULL),
  (20, 'Concert militaire', 'Concert de la musique des équipages de la Flotte - Répertoire classique et variété française', '2026-07-14', '2026-07-14', 'Église Notre-Dame la Grande', '18:00:00', 90, NULL),
  -- Événements Weekend Rentrée 2026 (id_weekend = 21)
  (21, 'Forum des associations rentrée 2026', 'Plus de 80 associations présentes - Démonstrations, initiations gratuites - Stands d''information et inscriptions', '2026-09-05', '2026-09-05', 'Parc des expositions', '10:00:00', 420, '/uploads/events/forum-rentree.jpg'),
  (21, 'Vide-grenier géant de rentrée', 'Plus de 200 exposants - Brocante, vêtements, jouets, livres - Buvette et petite restauration', '2026-09-06', '2026-09-06', 'Parking du stade Rébeilleau', '07:00:00', 540, '/uploads/events/vide-grenier-rentree.jpg'),
  (21, 'Collecte fournitures scolaires', 'Collecte de fournitures neuves ou occasion pour les familles dans le besoin - Distribution gratuite sur justificatif', '2026-09-05', '2026-09-06', 'Maison des associations', '10:00:00', 480, NULL),
  (21, 'Concert de rentrée gratuit', 'Groupe de pop-rock local Les Échos de Poitiers - Concert tout public - Buvette associative', '2026-09-05', '2026-09-05', 'Salle des fêtes', '20:30:00', 150, '/uploads/events/concert-rentree.jpg'),
  (21, 'Ateliers découverte activités', 'Essais gratuits : danse, arts martiaux, yoga, théâtre, peinture - Inscriptions possibles sur place', '2026-09-05', '2026-09-05', 'Complexe sportif municipal', '14:00:00', 240, NULL),
  -- Événements Weekend Vendanges 2026 (id_weekend = 22)
  (22, 'Loto des vendanges', 'Loto caritatif avec lots gourmands et bouteilles de vin du Haut-Poitou - Dégustation gratuite de raisins', '2026-09-26', '2026-09-26', 'Salle polyvalente', '14:30:00', 240, '/uploads/events/loto-vendanges.jpg'),
  (22, 'Dégustation responsable de vins locaux', 'Présentation des vignobles du Haut-Poitou avec 5 vignerons - Dégustation commentée - Stand de vente - Crachoirs et eau disponibles', '2026-09-26', '2026-09-26', 'Chapiteau place du marché', '15:00:00', 180, '/uploads/events/degustation-vins.jpg'),
  (22, 'Repas du terroir', 'Menu traditionnel poitevin - Chèvre chaud, mique poitevine, farci, tarte aux poires - Vin local inclus - Sur réservation', '2026-09-26', '2026-09-26', 'Restaurant Le Terroir', '19:30:00', 180, '/uploads/events/repas-terroir.jpg'),
  (22, 'Animation folklorique', 'Groupe de danse folklorique Les Sabots Poitevins - Démonstrations et initiation publique - Costumes traditionnels', '2026-09-26', '2026-09-26', 'Place Charles de Gaulle', '17:00:00', 120, NULL),
  (22, 'Marché des producteurs', 'Producteurs locaux de vin, fromages, charcuteries, confitures - Produits du terroir poitevin - Vente directe', '2026-09-26', '2026-09-27', 'Halles du marché', '09:00:00', 480, NULL),
  -- Événements Weekend Halloween 2026 (id_weekend = 23)
  (23, 'Parcours hanté de l''ancienne usine', 'Parcours terrifiant dans l''usine désaffectée - Zombies et créatures effrayantes - Déconseillé -12 ans - Entrée 5€', '2026-10-31', '2026-10-31', 'Ancienne usine Michelin', '19:00:00', 300, '/uploads/events/parcours-hante.jpg'),
  (23, 'Soirée costumée Halloween', 'Grande fête déguisée avec DJ - Concours du costume le plus effrayant (lots à gagner) - Bar à cocktails horrifiques', '2026-10-31', '2026-10-31', 'Salle des fêtes décorée', '21:00:00', 300, '/uploads/events/soiree-halloween.jpg'),
  (23, 'Chasse aux bonbons quartier centre', 'Pour les 3-12 ans - Parcours fléché dans les commerces participants - Seau de bonbons à récupérer - Gratuit', '2026-10-31', '2026-10-31', 'Centre-ville historique', '17:00:00', 150, '/uploads/events/chasse-bonbons.jpg'),
  (23, 'Projection Shining en version restaurée', 'Film culte de Stanley Kubrick - Ciné-débat après la séance - Bar et pop-corn disponibles - Tarif réduit 4€', '2026-10-31', '2026-10-31', 'Cinéma le Dietrich', '20:30:00', 150, NULL),
  (23, 'Atelier maquillage horrifique', 'Atelier gratuit de maquillage zombie, vampire, sorcière - Animé par une maquilleuse professionnelle - Sur inscription', '2026-10-31', '2026-10-31', 'Maison de quartier', '14:00:00', 180, NULL),
  -- Événements Weekend Téléthon 2026 (id_weekend = 24)
  (24, 'Marathon du Téléthon', 'Course solidaire 10km, 21km et 42km - Inscriptions 10€ reversés au Téléthon - Ravitaillement et médaille pour tous', '2026-12-05', '2026-12-05', 'Départ Place du Maréchal Leclerc', '09:00:00', 360, '/uploads/events/marathon-telethon.jpg'),
  (24, 'Tournoi de foot inter-associations', 'Championnat à 7 joueurs - 16 équipes d''associations - Buvette et tombola - Entrée gratuite pour les spectateurs', '2026-12-05', '2026-12-05', 'Stade Jacques Bouvard', '14:00:00', 360, '/uploads/events/tournoi-foot.jpg'),
  (24, 'Zumba géante sur la place', 'Session collective de Zumba - Participation libre 2€ - Ambiance garantie - Tous niveaux bienvenus', '2026-12-05', '2026-12-05', 'Place du Marché Notre-Dame', '11:00:00', 90, '/uploads/events/zumba-telethon.jpg'),
  (24, 'Stand crêpes non-stop 24h', 'Vente de crêpes salées et sucrées pendant 24h - Bénévoles en relais - 100% des bénéfices pour le Téléthon', '2026-12-05', '2026-12-06', 'Stand chapiteau place centrale', '10:00:00', 1440, NULL),
  (24, 'Grand concert solidaire', 'Artistes locaux et régionaux - Variété française et rock - Entrée 8€ reversés au Téléthon - Buvette sur place', '2026-12-05', '2026-12-05', 'Salle du Confort Moderne', '20:00:00', 240, '/uploads/events/concert-telethon.jpg'),
  (24, 'Challenge sportif collectif', 'Relais natation, vélo, course à pied - Par équipes de 5 - Inscription 50€ par équipe - Goûter et boissons offerts', '2026-12-06', '2026-12-06', 'Complexe sportif La Pépinière', '10:00:00', 240, NULL),
  -- Événements Weekend Marché Noël 2026 (id_weekend = 25)
  (25, 'Village de Noël artisanal', '50 chalets d''artisans créateurs - Idées cadeaux, décorations, gastronomie - Ambiance féerique avec illuminations', '2026-12-19', '2026-12-20', 'Place du Marché Notre-Dame', '10:00:00', 660, '/uploads/events/village-noel-2026.jpg'),
  (25, 'Patinoire de Noël en plein air', 'Patinoire éphémère 20x40m - Location de patins incluse - Tarif 6€/heure - Musique de Noël - Vin chaud en buvette', '2026-12-19', '2026-12-20', 'Esplanade François Mitterrand', '10:00:00', 720, '/uploads/events/patinoire-2026.jpg'),
  (25, 'Rencontre avec le Père Noël', 'Photos gratuites avec le Père Noël - Distribution de papillotes - Petits cadeaux pour les enfants sages - Boîte aux lettres magique', '2026-12-19', '2026-12-20', 'Chalet central du marché', '14:00:00', 300, '/uploads/events/pere-noel-2026.jpg'),
  (25, 'Atelier création couronnes de Noël', 'Confection de couronnes avec éléments naturels - Matériel fourni - Animé par fleuriste - Participation 8€', '2026-12-19', '2026-12-19', 'Chapiteau animations', '14:00:00', 150, NULL),
  (25, 'Concert chants traditionnels de Noël', 'Chorales Cœur de Poitiers et Les Petits Chanteurs - Répertoire de Noël international - Gratuit', '2026-12-19', '2026-12-19', 'Scène place du marché', '18:00:00', 90, '/uploads/events/chants-noel.jpg'),
  (25, 'Projection La Belle et le Clochard', 'Ciné en plein air familial - Couvertures et boissons chaudes disponibles - Gratuit sur inscription', '2026-12-20', '2026-12-20', 'Parc de Blossac', '19:00:00', 120, NULL),
  (25, 'Manège et petits trains de Noël', 'Manège ancien et train touristique décoré - Tour du centre historique illuminé - Tarif 3€', '2026-12-19', '2026-12-20', 'Centre-ville', '14:00:00', 480, NULL);

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
