-- Nettoyage
DROP TABLE IF EXISTS PARTICIPANT;
DROP TABLE IF EXISTS PUBLIC;
DROP TABLE IF EXISTS COMMUNICATION;
DROP TABLE IF EXISTS STOCK;
DROP TABLE IF EXISTS MECENE;
DROP TABLE IF EXISTS PLANNING;
DROP TABLE IF EXISTS BENEVOLE;

-- Table des bénévoles
CREATE TABLE BENEVOLE(
    id_benevole INT NOT NULL,
    nom VARCHAR(42),
    prenom VARCHAR(42),
    disponibilites VARCHAR(250),
    PRIMARY KEY (id_benevole)
);

-- Table planning (qui fait quoi et quand)
CREATE TABLE PLANNING(
    id_planning INT NOT NULL,
    id_benevole INT NOT NULL,
    poste VARCHAR(42),
    jour DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    PRIMARY KEY (id_planning),
    FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id_benevole)
);

-- Table des mécènes
CREATE TABLE MECENE(
    id_mecene INT NOT NULL,
    nom VARCHAR(42),
    contact VARCHAR(100),
    lot_offert VARCHAR(100),
    montant FLOAT,
    PRIMARY KEY (id_mecene)
);

-- Table des stocks
CREATE TABLE STOCK(
    id_stock  INT NOT NULL,
    categorie VARCHAR(42),
    article VARCHAR(100),
    quantite INT,
    prix FLOAT,
    annee YEAR,
    PRIMARY KEY (id_stock)
);

-- Table communication
CREATE TABLE COMMUNICATION(
    id_com INT NOT NULL,
    date DATE NOT NULL DEFAULT (current_date),
    support VARCHAR(42),
    depense FLOAT,
    PRIMARY KEY (id_com)
);

-- Table public (import HelloAsso)
CREATE TABLE PUBLIC (
    id_public INT NOT NULL,
    nom VARCHAR(42),
    prenom VARCHAR(42),
    email VARCHAR(100),
    annee YEAR,
    PRIMARY KEY (id_public)
);

-- Table participation (qui participe à quoi, optionnel)
CREATE TABLE PARTICIPANT(
    id_benevole INT NOT NULL,
    id_planning INT NOT NULL,
    PRIMARY KEY (id_benevole, id_planning),
    FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id_benevole),
    FOREIGN KEY (id_planning) REFERENCES PLANNING(id_planning)
);

-- Données de test
INSERT INTO BENEVOLE (id_benevole, nom, prenom, disponibilites) VALUES
(1, 'Durand', 'Alice', 'Lundi matin; Mardi soir'),
(2, 'Martin', 'Paul', 'Mercredi après-midi; Samedi');

INSERT INTO MECENE (id_mecene, nom, contact, lot_offert, montant) VALUES
(1, 'Supermarché X', 'contact@superx.fr', 'Panier garni', 200),
(2, 'Banque Y', 'banque.y@mail.fr', 'Chèque cadeau', 500);

INSERT INTO STOCK (id_stock, categorie, article, quantite, prix, annee) VALUES
(1, 'Bar', 'Bouteilles d''eau', 100, 0.50, 2025),
(2, 'Restauration', 'Sandwichs', 50, 2.00, 2025);

INSERT INTO COMMUNICATION (id_com, date, support, depense) VALUES
(1, CURRENT_DATE, 'Affiche A3', 120),
(2, CURRENT_DATE, 'Facebook Ads', 80);

INSERT INTO PUBLIC (id_public, nom, prenom, email, annee) VALUES
(1, 'Lefevre', 'Julie', 'julie.lefevre@mail.fr', 2025),
(2, 'Nguyen', 'Bao', 'bao.nguyen@mail.fr', 2025);
