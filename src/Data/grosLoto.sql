DROP TABLE IF EXISTS PARTICIPE;
DROP TABLE IF EXISTS QUESTION;
DROP TABLE IF EXISTS QUIZ;
DROP TABLE IF EXISTS UTILISATEUR;

CREATE TABLE QUIZ
(
    id_Quiz INT NOT NULL,
    name_Q  VARCHAR(42),
    theme   VARCHAR(42),
    PRIMARY KEY (id_Quiz)
);

CREATE TABLE QUESTION
(
    id_Q    INT NOT NULL,
    id_Quiz INT NOT NULL,
    type_Q  VARCHAR(42),
    label   VARCHAR(42),
    choices VARCHAR(250),
    correct VARCHAR(42),
    PRIMARY KEY (id_Q, id_Quiz),
    FOREIGN KEY (id_Quiz) REFERENCES QUIZ (id_Quiz)
);

CREATE TABLE UTILISATEUR
(
    uuid   VARCHAR NOT NULL,
    nom_U  VARCHAR(42) UNIQUE,
    mdp    VARCHAR(42),
    type_U VARCHAR(42),
    PRIMARY KEY (uuid)
);

CREATE TABLE PARTICIPE
(
    id_Quiz INT NOT NULL,
    uuid    VARCHAR NOT NULL,
    score   FLOAT,
    date    DATE DEFAULT (current_timestamp),
    PRIMARY KEY (id_Quiz, uuid, date),
    FOREIGN KEY (id_Quiz) REFERENCES QUIZ (id_Quiz),
    FOREIGN KEY (uuid) REFERENCES UTILISATEUR (uuid)
);


INSERT INTO UTILISATEUR (uuid, nom_U, mdp, type_U) VALUES
(1, 'admin', '$2y$10$qu4kz0lfn6FwNV08OCsTnOVZnrsTzFIM7pu3FAzGJrqItUyvxtksy', 'ADM');


-- Insérer les quiz
INSERT INTO QUIZ (id_Quiz, name_Q, theme) VALUES
                                              (1, 'Quiz Animaux', 'Animaux'),
                                              (2, 'Quiz Villes', 'Villes');

-- Insérer les questions pour le Quiz Animaux
INSERT INTO QUESTION (id_Q, id_Quiz, type_Q, label, choices, correct) VALUES
(1, 1, 'checkbox', 'Quels animaux peuvent nager ?', 'Poisson;Chien;Éléphant', 'Poisson;Chien'),
(2, 1, 'text', 'Quel animal miaule ?', null, 'chat'),
(3, 1, 'checkbox', 'Lequel de ces animaux a des plumes ?', 'Oiseau;Serpent;Chien', 'Oiseau'),
(4, 1, 'text', "De quelle couleur est le cheval d'Henry IV ?", null, 'blanc'),
(5, 1, 'checkbox', "Quels animaux vivent dans l'océan ?", 'Dauphin;Cheval;Requin', 'Dauphin;Requin');

-- Insérer les questions pour le Quiz Villes
INSERT INTO QUESTION (id_Q, id_Quiz, type_Q, label, choices, correct) VALUES
(1, 2, 'checkbox', 'Dans quelle ville peut-on voir la Tour Eiffel ?', 'Paris;New York;Londres', 'Paris'),
(2, 2, 'text', 'Quelle ville est la capitale de la France ?', null, 'Paris'),
(3, 2, 'checkbox', 'Quel est le nom de la capitale du Japon ?', 'Séoul;Tokyo;Pékin', 'Tokyo'),
(4, 2, 'text', 'Dans quelle ville trouve-t-on le Colisée ?', null, 'Rome'),
(5, 2, 'checkbox', 'Dans quelle ville peut-on voir la Statue de la Liberté ?', 'Paris;Londres;New York', 'New York');