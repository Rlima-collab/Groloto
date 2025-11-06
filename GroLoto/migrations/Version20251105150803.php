<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105150803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE CONTACT_MESSAGE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, repondu_par_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, lu BOOLEAN NOT NULL, reponse CLOB DEFAULT NULL, repondu_le DATETIME DEFAULT NULL, CONSTRAINT FK_B609370B26CA41A0 FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B609370B26CA41A0 ON CONTACT_MESSAGE (repondu_par_id)');
        $this->addSql('CREATE TABLE DEMANDE_ANNULATION (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_affectation INTEGER NOT NULL, id_benevole INTEGER NOT NULL, id_admin_reponse INTEGER DEFAULT NULL, date_demande DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, motif_benevole CLOB DEFAULT NULL, message_admin CLOB DEFAULT NULL, date_reponse DATETIME DEFAULT NULL, CONSTRAINT FK_75084DE6ECCFAC24 FOREIGN KEY (id_affectation) REFERENCES AFFECTATION_TACHE (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_75084DE6E4DAA34E FOREIGN KEY (id_benevole) REFERENCES BENEVOLE (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_75084DE625A28D2B FOREIGN KEY (id_admin_reponse) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_75084DE6ECCFAC24 ON DEMANDE_ANNULATION (id_affectation)');
        $this->addSql('CREATE INDEX IDX_75084DE6E4DAA34E ON DEMANDE_ANNULATION (id_benevole)');
        $this->addSql('CREATE INDEX IDX_75084DE625A28D2B ON DEMANDE_ANNULATION (id_admin_reponse)');
        $this->addSql('ALTER TABLE EVENEMENT ADD COLUMN heure_debut TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE EVENEMENT ADD COLUMN duree_minutes INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE EVENEMENT ADD COLUMN heure_fin TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE EVENEMENT ADD COLUMN image VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__INSCRIPTION_MECENE AS SELECT id, description_don, montant_estime, statut, date_inscription, remarques FROM INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE INSCRIPTION_MECENE');
        $this->addSql('CREATE TABLE INSCRIPTION_MECENE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_mecene INTEGER NOT NULL, id_evenement INTEGER NOT NULL, description_don CLOB NOT NULL, montant_estime DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_inscription DATETIME NOT NULL, remarques CLOB DEFAULT NULL, nom_don VARCHAR(255) NOT NULL, categorie VARCHAR(50) NOT NULL, quantite INTEGER NOT NULL, valeur_unitaire DOUBLE PRECISION DEFAULT NULL, remarque_refus CLOB DEFAULT NULL, CONSTRAINT FK_35E63F33D364722F FOREIGN KEY (id_mecene) REFERENCES MECENE (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_35E63F338B13D439 FOREIGN KEY (id_evenement) REFERENCES EVENEMENT (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO INSCRIPTION_MECENE (id, description_don, montant_estime, statut, date_inscription, remarques) SELECT id, description_don, montant_estime, statut, date_inscription, remarques FROM __temp__INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE __temp__INSCRIPTION_MECENE');
        $this->addSql('CREATE INDEX IDX_35E63F33D364722F ON INSCRIPTION_MECENE (id_mecene)');
        $this->addSql('CREATE INDEX IDX_35E63F338B13D439 ON INSCRIPTION_MECENE (id_evenement)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__TACHE AS SELECT id, id_evenement, titre, poste_requis, debut, fin, max_personnes, remarque FROM TACHE');
        $this->addSql('DROP TABLE TACHE');
        $this->addSql('CREATE TABLE TACHE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_weekend INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, poste_requis VARCHAR(100) NOT NULL, debut DATETIME NOT NULL, fin DATETIME NOT NULL, max_personnes INTEGER NOT NULL, remarque CLOB DEFAULT NULL, CONSTRAINT FK_64D3E2C5704F64DE FOREIGN KEY (id_weekend) REFERENCES WEEKEND (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO TACHE (id, id_weekend, titre, poste_requis, debut, fin, max_personnes, remarque) SELECT id, id_evenement, titre, poste_requis, debut, fin, max_personnes, remarque FROM __temp__TACHE');
        $this->addSql('DROP TABLE __temp__TACHE');
        $this->addSql('CREATE INDEX IDX_64D3E2C5704F64DE ON TACHE (id_weekend)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE CONTACT_MESSAGE');
        $this->addSql('DROP TABLE DEMANDE_ANNULATION');
        $this->addSql('CREATE TEMPORARY TABLE __temp__EVENEMENT AS SELECT id, id_weekend, nom, description, date_debut, date_fin, lieu, date_creation FROM EVENEMENT');
        $this->addSql('DROP TABLE EVENEMENT');
        $this->addSql('CREATE TABLE EVENEMENT (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_weekend INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, lieu VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, CONSTRAINT FK_4FE476F1704F64DE FOREIGN KEY (id_weekend) REFERENCES WEEKEND (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO EVENEMENT (id, id_weekend, nom, description, date_debut, date_fin, lieu, date_creation) SELECT id, id_weekend, nom, description, date_debut, date_fin, lieu, date_creation FROM __temp__EVENEMENT');
        $this->addSql('DROP TABLE __temp__EVENEMENT');
        $this->addSql('CREATE INDEX IDX_4FE476F1704F64DE ON EVENEMENT (id_weekend)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__INSCRIPTION_MECENE AS SELECT id, description_don, montant_estime, statut, date_inscription, remarques FROM INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE INSCRIPTION_MECENE');
        $this->addSql('CREATE TABLE INSCRIPTION_MECENE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, mecene_id INTEGER NOT NULL, evenement_id INTEGER NOT NULL, description_don CLOB NOT NULL, montant_estime DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_inscription DATETIME NOT NULL, remarques CLOB DEFAULT NULL, CONSTRAINT FK_35E63F3382B3C62C FOREIGN KEY (mecene_id) REFERENCES MECENE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_35E63F33FD02F13 FOREIGN KEY (evenement_id) REFERENCES EVENEMENT (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO INSCRIPTION_MECENE (id, description_don, montant_estime, statut, date_inscription, remarques) SELECT id, description_don, montant_estime, statut, date_inscription, remarques FROM __temp__INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE __temp__INSCRIPTION_MECENE');
        $this->addSql('CREATE INDEX IDX_35E63F33FD02F13 ON INSCRIPTION_MECENE (evenement_id)');
        $this->addSql('CREATE INDEX IDX_35E63F3382B3C62C ON INSCRIPTION_MECENE (mecene_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__TACHE AS SELECT id, id_weekend, titre, debut, fin, poste_requis, max_personnes, remarque FROM TACHE');
        $this->addSql('DROP TABLE TACHE');
        $this->addSql('CREATE TABLE TACHE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_evenement INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, debut DATETIME NOT NULL, fin DATETIME NOT NULL, poste_requis VARCHAR(100) NOT NULL, max_personnes INTEGER NOT NULL, remarque CLOB DEFAULT NULL, FOREIGN KEY (id_evenement) REFERENCES EVENEMENT (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO TACHE (id, id_evenement, titre, debut, fin, poste_requis, max_personnes, remarque) SELECT id, id_weekend, titre, debut, fin, poste_requis, max_personnes, remarque FROM __temp__TACHE');
        $this->addSql('DROP TABLE __temp__TACHE');
        $this->addSql('CREATE INDEX IDX_64D3E2C58B13D439 ON TACHE (id_evenement)');
    }
}
