DROP TABLE IF EXISTS HISTORIQUE_EVENEMENT;
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
  remarque TEXT,
  actif BOOLEAN DEFAULT 1,
  FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

CREATE TABLE MECENE (
  id INTEGER PRIMARY KEY,
  id_utilisateur INTEGER,
  organisation TEXT NOT NULL,
  siret TEXT NOT NULL,
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
  methode_signature TEXT DEFAULT 'aucune',
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT check_methode_signature CHECK (methode_signature IN ('aucune', 'manuelle', 'electronique')),
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
  categorie TEXT,
  quantite INTEGER DEFAULT 0,
  unite TEXT,
  seuil INTEGER DEFAULT 0,
  valeur_unitaire REAL DEFAULT 0.0,
  remarque TEXT,
  derniere_modif DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT check_categorie CHECK (categorie IN ('bar', 'resto', 'deco', 'autre'))
);

CREATE TABLE HISTORIQUE_STOCK (
  id INTEGER PRIMARY KEY,
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

CREATE TABLE DISPONIBILITE_BENEVOLE (
  id INTEGER PRIMARY KEY,
  id_benevole INTEGER NOT NULL,
  id_evenement INTEGER NOT NULL,
  debut DATETIME,
  fin DATETIME,
  remarque TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
  FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

CREATE TABLE CRENEAU (
  id INTEGER PRIMARY KEY,
  id_evenement INTEGER NOT NULL,
  titre TEXT NOT NULL,
  poste_requis TEXT,
  debut DATETIME NOT NULL,
  fin DATETIME NOT NULL,
  max_personnes INTEGER DEFAULT 1,
  remarque TEXT,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT check_poste_requis CHECK (poste_requis IN ('bar', 'accueil', 'cuisine', 'technique', 'autre')),
  FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
);

CREATE TABLE AFFECTATION_CRENEAU (
  id INTEGER PRIMARY KEY,
  id_creneau INTEGER NOT NULL,
  id_benevole INTEGER NOT NULL,
  id_utilisateur INTEGER,
  date_affectation DATETIME DEFAULT CURRENT_TIMESTAMP,
  statut TEXT DEFAULT 'assigne',
  remarque TEXT,
  CONSTRAINT check_statut CHECK (statut IN ('assigne', 'confirme', 'annule')),
  FOREIGN KEY (id_creneau) REFERENCES CRENEAU(id),
  FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
  FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

CREATE TABLE COMMUNICATION (
  id INTEGER PRIMARY KEY,
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

CREATE TABLE HISTORIQUE_EVENEMENT (
  id INTEGER PRIMARY KEY,
  id_evenement INTEGER NOT NULL,
  action TEXT NOT NULL,
  description TEXT,
  id_utilisateur INTEGER,
  date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT check_action CHECK (action IN ('creation', 'modification', 'annulation', 'autre')),
  FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id),
  FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
);

-- Insertions
INSERT INTO ROLE (nom, description) VALUES
  ('admin', 'Administrateur organisation'),
  ('benevole', 'Bénévole participant aux événements'),
  ('mecene', 'Partenaire/mécène offrant des lots ou financements');

INSERT INTO UTILISATEUR (id_role, email, prenom, nom, telephone, mot_de_passe, date_creation)
VALUES
  (1, 'admin@groloto.local', 'Admin', 'User', '0600000000', '$2y$13$rPw4LiI3Pt91wm8QUAE20OGJ3Cl/wPS35ix1h5C1ZtpL06bPOqby2', datetime('now')),
  (2, 'benevole1@groloto.local', 'Alice', 'Durand', '0612345678', NULL, datetime('now')),
  (3, 'mecene1@groloto.local', 'Jean', 'Martin', '0712345678', NULL, datetime('now'));

INSERT INTO BENEVOLE (id_utilisateur, remarque, actif)
VALUES
  (2, 'Disponible surtout les week-ends', 1);

INSERT INTO MECENE (id_utilisateur, organisation, siret)
VALUES
  (3, 'Boulangerie Martin', '12345678901234');

INSERT INTO EVENEMENT (nom, description, date_debut, date_fin, lieu)
VALUES
  ('Tournoi de belote 2025', 'Compétition amicale avec lots pour les gagnants', '2025-11-01', '2025-11-01', 'Maison des associations'),
  ('Soirée quizz caritative', 'Quiz généraliste pour récolter des fonds', '2025-11-02', '2025-11-02', 'Salle municipale'),
  ('Concert solidaire', 'Concert avec groupes locaux au profit de l’association', '2025-11-03', '2025-11-03', 'Parc central'),
  ('Vide-grenier Groloto', 'Vide-grenier pour collecter des fonds pour les projets associatifs', '2025-11-04', '2025-11-04', 'Place du marché'),
  ('Groloto 2025', 'Loto caritatif annuel édition 2025', '2025-11-15', '2025-11-15', 'Salle des fêtes'),
  ('Groloto 2024', 'Loto caritatif annuel', '2024-11-15', '2024-11-15', 'Salle des fêtes');

INSERT INTO HISTORIQUE_EVENEMENT (id_evenement, action, description, id_utilisateur, date_action)
VALUES
  (6, 'creation', 'Création de l''événement Groloto 2024', 1, '2024-10-01 10:00:00'),
  (6, 'modification', 'Mise à jour du lieu de Groloto 2024', 1, '2024-10-05 14:30:00'),
  (6, 'annulation', 'Annulation partielle d''une activité', 1, '2024-10-10 09:00:00'),
  (1, 'creation', 'Création du Tournoi de belote 2025', 1, '2025-09-01 08:00:00'),
  (2, 'creation', 'Création de la Soirée quizz caritative', 1, '2025-09-02 09:00:00');

INSERT INTO STOCK (nom, categorie, quantite, unite, seuil, valeur_unitaire, remarque)
VALUES
  ('Panier gourmand', 'resto', 15, 'pièces', 5, 10.0, 'Lots donnés par Boulangerie Martin'),
  ('Bon d''achat restaurant', 'resto', 3, 'bons', 10, 30.0, 'Offert par Restaurant Le Gourmet'),
  ('Coffret livres', 'autre', 25, 'coffrets', 8, 25.0, 'Don Librairie des Arts'),
  ('Séance spa', 'autre', 2, 'tickets', 5, 50.0, 'Centre bien-être partenaire'),
  ('Pack boissons', 'bar', 50, 'bouteilles', 20, 5.0, 'Lots pour le bar'),
  ('Décoration de salle', 'deco', 10, 'kits', 2, 12.0, 'Décos pour l''événement');

INSERT INTO HISTORIQUE_STOCK (id_stock, type_changement, quantite, raison, id_utilisateur)
VALUES
  (1, 'entree', 10, 'Don initial Boulangerie Martin', 1),
  (2, 'sortie', 2, 'Lots utilisés pour tombola', 1),
  (3, 'entree', 15, 'Don Librairie des Arts', 1),
  (4, 'sortie', 1, 'Prix événement test', 1),
  (5, 'entree', 50, 'Commande boissons sponsor', 1),
  (6, 'sortie', 3, 'Utilisé pour préparation salle', 1);

INSERT INTO LOT (id_mecene, titre, description, quantite, valeur_estimee)
VALUES
  (1, 'Panier gourmand premium', 'Composé de produits artisanaux', 5, 50.0),
  (1, 'Bon d''achat 50€', 'Utilisable dans la boulangerie', 10, 50.0);

INSERT INTO CONVENTION (id_mecene, nom_modele, url_pdf, date_signature, methode_signature)
VALUES
  (1, 'Modele partenariat standard', '/docs/conventions/convention1.pdf', '2024-10-01', 'electronique');

INSERT INTO CRENEAU (id_evenement, titre, poste_requis, debut, fin, max_personnes, remarque)
VALUES
  (6, 'Accueil participants', 'accueil', '2024-11-15 18:00:00', '2024-11-15 19:00:00', 3, 'Accueil et orientation des participants'),
  (6, 'Service bar', 'bar', '2024-11-15 19:00:00', '2024-11-15 22:00:00', 2, 'Préparer et servir les boissons');

INSERT INTO AFFECTATION_CRENEAU (id_creneau, id_benevole, id_utilisateur, statut, remarque)
VALUES
  (1, 1, 2, 'confirme', 'Alice affectée à l''accueil');

INSERT INTO DISPONIBILITE_BENEVOLE (id_benevole, id_evenement, debut, fin, remarque)
VALUES
  (1, 6, '2024-11-15 17:00:00', '2024-11-15 23:00:00', 'Disponible toute la durée de l''événement');

INSERT INTO COMMUNICATION (id_evenement, titre, type, date_prevue, statut, budget, remarque)
VALUES
  (6, 'Campagne Facebook', 'post', '2024-10-15 10:00:00', 'programme', 50.0, 'Campagne sponsorisée Facebook'),
  (6, 'Affiches locales', 'affiche', '2024-10-20 09:00:00', 'fait', 30.0, 'Affiches imprimées et distribuées en ville');

INSERT INTO HELLOASSO (id_evenement, id_externe, prenom, nom, email, telephone, type_ticket, date_achat)
VALUES
  (6, 'HA12345', 'Paul', 'Lemoine', 'paul.lemoine@example.com', '0611223344', 'Entrée standard', '2024-10-10 15:00:00');

INSERT INTO PARAMETRE (cle, valeur)
VALUES
  ('site_name', 'Groloto Manager'),
  ('devise', '€'),
  ('email_contact', 'contact@groloto.local');
