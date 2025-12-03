<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203163141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE PLAGE_HORAIRE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_tache INTEGER NOT NULL, jour DATE NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, max_personnes_plage INTEGER DEFAULT NULL, CONSTRAINT FK_59D5EA847D026145 FOREIGN KEY (id_tache) REFERENCES TACHE (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_59D5EA847D026145 ON PLAGE_HORAIRE (id_tache)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__CONTACT_MESSAGE AS SELECT id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour FROM CONTACT_MESSAGE');
        $this->addSql('DROP TABLE CONTACT_MESSAGE');
        $this->addSql('CREATE TABLE CONTACT_MESSAGE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, repondu_par_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, destinataire VARCHAR(255) DEFAULT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, lu BOOLEAN NOT NULL, reponse CLOB DEFAULT NULL, repondu_le DATETIME DEFAULT NULL, cloturee BOOLEAN DEFAULT 0 NOT NULL, masquee_pour CLOB DEFAULT NULL, FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO CONTACT_MESSAGE (id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour) SELECT id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour FROM __temp__CONTACT_MESSAGE');
        $this->addSql('DROP TABLE __temp__CONTACT_MESSAGE');
        $this->addSql('CREATE INDEX IDX_B609370B26CA41A0 ON CONTACT_MESSAGE (repondu_par_id)');
        $this->addSql('ALTER TABLE INSCRIPTION_MECENE ADD COLUMN adresse_postale CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE MECENE ADD COLUMN adresse_postale CLOB DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__TACHE AS SELECT id, id_weekend, titre, debut, fin, max_personnes, remarque FROM TACHE');
        $this->addSql('DROP TABLE TACHE');
        $this->addSql('CREATE TABLE TACHE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_weekend INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, debut DATETIME DEFAULT NULL, fin DATETIME DEFAULT NULL, max_personnes INTEGER DEFAULT NULL, remarque CLOB DEFAULT NULL, FOREIGN KEY (id_weekend) REFERENCES WEEKEND (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO TACHE (id, id_weekend, titre, debut, fin, max_personnes, remarque) SELECT id, id_weekend, titre, debut, fin, max_personnes, remarque FROM __temp__TACHE');
        $this->addSql('DROP TABLE __temp__TACHE');
        $this->addSql('CREATE INDEX IDX_64D3E2C5704F64DE ON TACHE (id_weekend)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE PLAGE_HORAIRE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__CONTACT_MESSAGE AS SELECT id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour FROM CONTACT_MESSAGE');
        $this->addSql('DROP TABLE CONTACT_MESSAGE');
        $this->addSql('CREATE TABLE CONTACT_MESSAGE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, repondu_par_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, destinataire VARCHAR(255) DEFAULT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, lu BOOLEAN NOT NULL, reponse CLOB DEFAULT NULL, repondu_le DATETIME DEFAULT NULL, cloturee BOOLEAN DEFAULT 0 NOT NULL, masquee_pour CLOB DEFAULT NULL, CONSTRAINT FK_B609370B26CA41A0 FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO CONTACT_MESSAGE (id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour) SELECT id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour FROM __temp__CONTACT_MESSAGE');
        $this->addSql('DROP TABLE __temp__CONTACT_MESSAGE');
        $this->addSql('CREATE INDEX IDX_B609370B26CA41A0 ON CONTACT_MESSAGE (repondu_par_id)');
        $this->addSql('CREATE INDEX IDX_B609370B727ACA70 ON CONTACT_MESSAGE (parent_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__INSCRIPTION_MECENE AS SELECT id, id_mecene, id_evenement, description_don, montant_estime, statut, date_inscription, remarques, nom_don, categorie, quantite, valeur_unitaire, remarque_refus, remarque_acceptation FROM INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE INSCRIPTION_MECENE');
        $this->addSql('CREATE TABLE INSCRIPTION_MECENE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_mecene INTEGER NOT NULL, id_evenement INTEGER NOT NULL, description_don CLOB NOT NULL, montant_estime DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_inscription DATETIME NOT NULL, remarques CLOB DEFAULT NULL, nom_don VARCHAR(255) NOT NULL, categorie VARCHAR(50) NOT NULL, quantite INTEGER NOT NULL, valeur_unitaire DOUBLE PRECISION DEFAULT NULL, remarque_refus CLOB DEFAULT NULL, remarque_acceptation CLOB DEFAULT NULL, CONSTRAINT FK_35E63F33D364722F FOREIGN KEY (id_mecene) REFERENCES MECENE (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_35E63F338B13D439 FOREIGN KEY (id_evenement) REFERENCES EVENEMENT (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO INSCRIPTION_MECENE (id, id_mecene, id_evenement, description_don, montant_estime, statut, date_inscription, remarques, nom_don, categorie, quantite, valeur_unitaire, remarque_refus, remarque_acceptation) SELECT id, id_mecene, id_evenement, description_don, montant_estime, statut, date_inscription, remarques, nom_don, categorie, quantite, valeur_unitaire, remarque_refus, remarque_acceptation FROM __temp__INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE __temp__INSCRIPTION_MECENE');
        $this->addSql('CREATE INDEX IDX_35E63F33D364722F ON INSCRIPTION_MECENE (id_mecene)');
        $this->addSql('CREATE INDEX IDX_35E63F338B13D439 ON INSCRIPTION_MECENE (id_evenement)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__MECENE AS SELECT id, id_utilisateur, organisation, siret FROM MECENE');
        $this->addSql('DROP TABLE MECENE');
        $this->addSql('CREATE TABLE MECENE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_utilisateur INTEGER DEFAULT NULL, organisation VARCHAR(255) DEFAULT NULL, siret VARCHAR(50) DEFAULT NULL, CONSTRAINT FK_5AB004450EAE44 FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO MECENE (id, id_utilisateur, organisation, siret) SELECT id, id_utilisateur, organisation, siret FROM __temp__MECENE');
        $this->addSql('DROP TABLE __temp__MECENE');
        $this->addSql('CREATE INDEX IDX_5AB004450EAE44 ON MECENE (id_utilisateur)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__TACHE AS SELECT id, id_weekend, titre, debut, fin, max_personnes, remarque FROM TACHE');
        $this->addSql('DROP TABLE TACHE');
        $this->addSql('CREATE TABLE TACHE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_weekend INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, debut DATETIME NOT NULL, fin DATETIME NOT NULL, max_personnes INTEGER DEFAULT NULL, remarque CLOB DEFAULT NULL, CONSTRAINT FK_64D3E2C5704F64DE FOREIGN KEY (id_weekend) REFERENCES WEEKEND (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO TACHE (id, id_weekend, titre, debut, fin, max_personnes, remarque) SELECT id, id_weekend, titre, debut, fin, max_personnes, remarque FROM __temp__TACHE');
        $this->addSql('DROP TABLE __temp__TACHE');
        $this->addSql('CREATE INDEX IDX_64D3E2C5704F64DE ON TACHE (id_weekend)');
    }
}
