<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216152403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__CONTACT_MESSAGE AS SELECT id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour FROM CONTACT_MESSAGE');
        $this->addSql('DROP TABLE CONTACT_MESSAGE');
        $this->addSql('CREATE TABLE CONTACT_MESSAGE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, repondu_par_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, destinataire VARCHAR(255) DEFAULT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, lu BOOLEAN NOT NULL, reponse CLOB DEFAULT NULL, repondu_le DATETIME DEFAULT NULL, cloturee BOOLEAN DEFAULT 0 NOT NULL, masquee_pour CLOB DEFAULT NULL, FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO CONTACT_MESSAGE (id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour) SELECT id, repondu_par_id, parent_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, cloturee, masquee_pour FROM __temp__CONTACT_MESSAGE');
        $this->addSql('DROP TABLE __temp__CONTACT_MESSAGE');
        $this->addSql('CREATE INDEX IDX_B609370B26CA41A0 ON CONTACT_MESSAGE (repondu_par_id)');
        $this->addSql('ALTER TABLE MECENE ADD COLUMN instagram VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE MECENE ADD COLUMN facebook VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__CONTACT_MESSAGE AS SELECT id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour FROM CONTACT_MESSAGE');
        $this->addSql('DROP TABLE CONTACT_MESSAGE');
        $this->addSql('CREATE TABLE CONTACT_MESSAGE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, repondu_par_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, destinataire VARCHAR(255) DEFAULT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, lu BOOLEAN NOT NULL, reponse CLOB DEFAULT NULL, repondu_le DATETIME DEFAULT NULL, cloturee BOOLEAN DEFAULT 0 NOT NULL, masquee_pour CLOB DEFAULT NULL, CONSTRAINT FK_B609370B26CA41A0 FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO CONTACT_MESSAGE (id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour) SELECT id, repondu_par_id, nom, email, destinataire, message, created_at, lu, reponse, repondu_le, parent_id, cloturee, masquee_pour FROM __temp__CONTACT_MESSAGE');
        $this->addSql('DROP TABLE __temp__CONTACT_MESSAGE');
        $this->addSql('CREATE INDEX IDX_B609370B26CA41A0 ON CONTACT_MESSAGE (repondu_par_id)');
        $this->addSql('CREATE INDEX IDX_B609370B727ACA70 ON CONTACT_MESSAGE (parent_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__MECENE AS SELECT id, id_utilisateur, organisation, siret, adresse_postale, logo FROM MECENE');
        $this->addSql('DROP TABLE MECENE');
        $this->addSql('CREATE TABLE MECENE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_utilisateur INTEGER DEFAULT NULL, organisation VARCHAR(255) DEFAULT NULL, siret VARCHAR(50) DEFAULT NULL, adresse_postale CLOB DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_5AB004450EAE44 FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO MECENE (id, id_utilisateur, organisation, siret, adresse_postale, logo) SELECT id, id_utilisateur, organisation, siret, adresse_postale, logo FROM __temp__MECENE');
        $this->addSql('DROP TABLE __temp__MECENE');
        $this->addSql('CREATE INDEX IDX_5AB004450EAE44 ON MECENE (id_utilisateur)');
    }
}
