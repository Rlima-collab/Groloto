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
    CONSTRAINT check_statut CHECK (statut IN ('assigne', 'confirme', 'annule', 'proposee', 'refusee')),
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
-- INSERTIONS DE DONNÉES ESSENTIELLES
-- =============================================

-- Insertion des rôles utilisateur
INSERT INTO ROLE (nom, description) VALUES
  ('admin', 'Administrateur organisation'),
  ('benevole', 'Bénévole participant aux événements'),
  ('mecene', 'Partenaire/mécène offrant des lots ou financements');

-- Insertion du compte administrateur unique
-- Email: grolotocaritatif@gmail.com
-- Mot de passe: @GROLOTO86
INSERT INTO UTILISATEUR (id_role, email, prenom, nom, telephone, mot_de_passe, date_creation) VALUES
  (1, 'grolotocaritatif@gmail.com', 'Admin', 'User', '0600000000', '$2y$10$PfThR6aqinEjlRwM.ISCG.RNrRfR2X7kpjFwYl7qbSn51j.4CD.Jy', datetime('now'));
