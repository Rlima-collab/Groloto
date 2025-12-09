<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203155458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE BENEVOLE ADD COLUMN disponibilites CLOB DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__CONTACT_MESSAGE AS SELECT id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour FROM CONTACT_MESSAGE');
        $this->addSql('DROP TABLE CONTACT_MESSAGE');
        $this->addSql('CREATE TABLE CONTACT_MESSAGE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, repondu_par_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, destinataire VARCHAR(255) DEFAULT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, lu BOOLEAN NOT NULL, reponse CLOB DEFAULT NULL, repondu_le DATETIME DEFAULT NULL, cloturee BOOLEAN DEFAULT 0 NOT NULL, masquee_pour CLOB DEFAULT NULL, FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO CONTACT_MESSAGE (id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour) SELECT id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour FROM __temp__CONTACT_MESSAGE');
        $this->addSql('DROP TABLE __temp__CONTACT_MESSAGE');
        $this->addSql('CREATE INDEX IDX_B609370B26CA41A0 ON CONTACT_MESSAGE (repondu_par_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__NOTIFICATION AS SELECT id, id_destinataire, type, message, lien, lue, created_at FROM NOTIFICATION');
        $this->addSql('DROP TABLE NOTIFICATION');
        $this->addSql('CREATE TABLE NOTIFICATION (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_destinataire INTEGER NOT NULL, type VARCHAR(50) NOT NULL, message CLOB NOT NULL, lien VARCHAR(255) DEFAULT NULL, lue BOOLEAN DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (id_destinataire) REFERENCES UTILISATEUR (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO NOTIFICATION (id, id_destinataire, type, message, lien, lue, created_at) SELECT id, id_destinataire, type, message, lien, lue, created_at FROM __temp__NOTIFICATION');
        $this->addSql('DROP TABLE __temp__NOTIFICATION');
        $this->addSql('CREATE INDEX IDX_2A663FDADD688AE0 ON NOTIFICATION (id_destinataire)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__BENEVOLE AS SELECT id, id_utilisateur, remarque, actif FROM BENEVOLE');
        $this->addSql('DROP TABLE BENEVOLE');
        $this->addSql('CREATE TABLE BENEVOLE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_utilisateur INTEGER DEFAULT NULL, remarque CLOB DEFAULT NULL, actif BOOLEAN DEFAULT 1 NOT NULL, CONSTRAINT FK_7232D39750EAE44 FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO BENEVOLE (id, id_utilisateur, remarque, actif) SELECT id, id_utilisateur, remarque, actif FROM __temp__BENEVOLE');
        $this->addSql('DROP TABLE __temp__BENEVOLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7232D39750EAE44 ON BENEVOLE (id_utilisateur)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__CONTACT_MESSAGE AS SELECT id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour FROM CONTACT_MESSAGE');
        $this->addSql('DROP TABLE CONTACT_MESSAGE');
        $this->addSql('CREATE TABLE CONTACT_MESSAGE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, repondu_par_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, destinataire VARCHAR(255) DEFAULT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, lu BOOLEAN NOT NULL, reponse CLOB DEFAULT NULL, repondu_le DATETIME DEFAULT NULL, cloturee BOOLEAN DEFAULT 0 NOT NULL, masquee_pour CLOB DEFAULT NULL, CONSTRAINT FK_B609370B26CA41A0 FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO CONTACT_MESSAGE (id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour) SELECT id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour FROM __temp__CONTACT_MESSAGE');
        $this->addSql('DROP TABLE __temp__CONTACT_MESSAGE');
        $this->addSql('CREATE INDEX IDX_B609370B26CA41A0 ON CONTACT_MESSAGE (repondu_par_id)');
        $this->addSql('CREATE INDEX IDX_B609370B727ACA70 ON CONTACT_MESSAGE (parent_id)');
        $this->addSql('ALTER TABLE NOTIFICATION ADD COLUMN reponse CLOB DEFAULT NULL');
    }
}
