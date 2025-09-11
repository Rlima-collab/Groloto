DROP TABLE IF EXISTS PARAMETRE;
DROP TABLE IF EXISTS HELLOASSO;
DROP TABLE IF EXISTS COMMUNICATION;
DROP TABLE IF EXISTS AFFECTATION_CRENEAU;
DROP TABLE IF EXISTS CRENEAU;
DROP TABLE IF EXISTS DISPONIBILITE_BENEVOLE;
DROP TABLE IF EXISTS HISTORIQUE_STOCK;
DROP TABLE IF EXISTS STOCK;
DROP TABLE IF EXISTS EVENEMENT;
DROP TABLE IF EXISTS CONVENTION;
DROP TABLE IF EXISTS LOT;
DROP TABLE IF EXISTS MECENE;
DROP TABLE IF EXISTS BENEVOLE;
DROP TABLE IF EXISTS UTILISATEUR;
DROP TABLE IF EXISTS ROLE;


CREATE TABLE ROLE (
  id INTEGER PRIMARY KEY,
  nom TEXT NOT NULL UNIQUE,
  description TEXT
);

INSERT INTO ROLE (nom, description) VALUES
('admin','Administrateur organisation'),
('benevole','Bénévole'),
('mecene','Mécène');

CREATE TABLE UTILISATEUR (
  id INTEGER PRIMARY KEY,
  id_role INTEGER NOT NULL,
  email TEXT NOT NULL UNIQUE,
  mot_de_passe TEXT,
  prenom TEXT,
  nom TEXT,
  telephone TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_role) REFERENCES ROLE(id)
);

CREATE TABLE BENEVOLE (
  id INTEGER PRIMARY KEY,
  id_utilisateur INTEGER NOT NULL UNIQUE,
  adresse TEXT,
  contact_urgence TEXT,
  notes TEXT,
  actif BOOLEAN DEFAULT 1,
  FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

CREATE TABLE MECENE (
  id INTEGER PRIMARY KEY,
  id_utilisateur INTEGER,
  organisation TEXT,
  nom_contact TEXT,
  email_contact TEXT,
  telephone_contact TEXT,
  adresse TEXT,
  FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

CREATE TABLE LOT (
  id INTEGER PRIMARY KEY,
  id_mecene INTEGER,
  titre TEXT NOT NULL,
  description TEXT,
  quantite INTEGER DEFAULT 1,
  valeur_estimee REAL DEFAULT 0.0,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_mecene) REFERENCES MECENE(id)
);

CREATE TABLE CONVENTION (
  id INTEGER PRIMARY KEY,
  id_mecene INTEGER NOT NULL,
  nom_modele TEXT,
  url_pdf TEXT,
  date_signature DATETIME,
  methode_signature TEXT DEFAULT 'aucune'
    CHECK (methode_signature IN ('aucune','manuelle','electronique')),
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_mecene) REFERENCES MECENE(id)
);

CREATE TABLE EVENEMENT (
  id INTEGER PRIMARY KEY,
  nom TEXT NOT NULL,
  description TEXT,
  date_debut DATE,
  date_fin DATE,
  lieu TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE STOCK (
  id INTEGER PRIMARY KEY,
  nom TEXT NOT NULL,
  categorie TEXT
    CHECK (categorie IN ('bar','resto','deco','autre')),
  quantite INTEGER DEFAULT 0,
  unite TEXT,
  seuil INTEGER DEFAULT 0,
  notes TEXT,
  derniere_modif DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE HISTORIQUE_STOCK (
  id INTEGER PRIMARY KEY,
  id_stock INTEGER NOT NULL,
  type_changement TEXT NOT NULL
    CHECK (type_changement IN ('entree','sortie','ajustement')),
  quantite INTEGER NOT NULL,
  raison TEXT,
  id_evenement INTEGER,
  id_utilisateur INTEGER,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_stock) REFERENCES STOCK(id),
  FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id),
  FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

CREATE TABLE DISPONIBILITE_BENEVOLE (
  id INTEGER PRIMARY KEY,
  id_benevole INTEGER NOT NULL,
  id_evenement INTEGER NOT NULL,
  debut DATETIME,
  fin DATETIME,
  notes TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
  FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

CREATE TABLE CRENEAU (
  id INTEGER PRIMARY KEY,
  id_evenement INTEGER NOT NULL,
  titre TEXT NOT NULL,
  poste_requis TEXT
    CHECK (poste_requis IN ('bar','accueil','cuisine','technique','autre')),
  debut DATETIME NOT NULL,
  fin DATETIME NOT NULL,
  max_personnes INTEGER DEFAULT 1,
  notes TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

CREATE TABLE AFFECTATION_CRENEAU (
  id INTEGER PRIMARY KEY,
  id_creneau INTEGER NOT NULL,
  id_benevole INTEGER NOT NULL,
  id_utilisateur INTEGER,
  date_affectation DATETIME DEFAULT CURRENT_TIMESTAMP,
  statut TEXT DEFAULT 'assigne'
    CHECK (statut IN ('assigne','confirme','annule')),
  notes TEXT,
  FOREIGN KEY (id_creneau) REFERENCES CRENEAU(id),
  FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
  FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

CREATE TABLE COMMUNICATION (
  id INTEGER PRIMARY KEY,
  id_evenement INTEGER,
  titre TEXT NOT NULL,
  type TEXT DEFAULT 'post'
    CHECK (type IN ('post','affiche','flyer','email','autre')),
  date_prevue DATETIME,
  statut TEXT DEFAULT 'brouillon'
    CHECK (statut IN ('brouillon','programme','fait','annule')),
  budget REAL DEFAULT 0.0,
  notes TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

CREATE TABLE HELLOASSO (
  id INTEGER PRIMARY KEY,
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

CREATE TABLE PARAMETRE (
  id INTEGER PRIMARY KEY,
  cle TEXT NOT NULL UNIQUE,
  valeur TEXT,
  date_modification DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Exemple d’insertion
INSERT INTO UTILISATEUR (id_role, email, prenom, nom) 
VALUES (1, 'admin@groloto.local', 'Admin', 'Groloto');

INSERT INTO EVENEMENT (nom, description, date_debut, date_fin, lieu) 
VALUES ('Groloto 2024', 'Loto caritatif annuel', '2024-11-15', '2024-11-15', 'Salle des fêtes');