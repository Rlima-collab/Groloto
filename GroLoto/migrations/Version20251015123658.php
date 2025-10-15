<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015123658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__UTILISATEUR AS SELECT id, id_role, email, mot_de_passe, prenom, nom, telephone, date_creation, date_modification FROM UTILISATEUR');
        $this->addSql('DROP TABLE UTILISATEUR');
        $this->addSql('CREATE TABLE UTILISATEUR (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_role INTEGER NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, date_modification DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, FOREIGN KEY (id_role) REFERENCES ROLE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO UTILISATEUR (id, id_role, email, mot_de_passe, prenom, nom, telephone, date_creation, date_modification) SELECT id, id_role, email, mot_de_passe, prenom, nom, telephone, date_creation, date_modification FROM __temp__UTILISATEUR');
        $this->addSql('DROP TABLE __temp__UTILISATEUR');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_901FF15BE7927C74 ON UTILISATEUR (email)');
        $this->addSql('CREATE INDEX IDX_901FF15BDC499668 ON UTILISATEUR (id_role)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__MECENE AS SELECT id, id_utilisateur, organisation, siret FROM MECENE');
        $this->addSql('DROP TABLE MECENE');
        $this->addSql('CREATE TABLE MECENE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER DEFAULT NULL, organisation VARCHAR(255) DEFAULT NULL, siret VARCHAR(50) DEFAULT NULL, nom VARCHAR(180) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, actif BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , FOREIGN KEY (utilisateur_id) REFERENCES UTILISATEUR (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F553C7C2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO MECENE (id, utilisateur_id, organisation, siret) SELECT id, id_utilisateur, organisation, siret FROM __temp__MECENE');
        $this->addSql('DROP TABLE __temp__MECENE');
        $this->addSql('CREATE INDEX IDX_F553C7C2FB88E14F ON MECENE (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__mecene AS SELECT id, utilisateur_id, organisation, siret FROM mecene');
        $this->addSql('DROP TABLE mecene');
        $this->addSql('CREATE TABLE mecene (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_utilisateur INTEGER DEFAULT NULL, organisation VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO mecene (id, id_utilisateur, organisation, siret) SELECT id, utilisateur_id, organisation, siret FROM __temp__mecene');
        $this->addSql('DROP TABLE __temp__mecene');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AB004450EAE44 ON mecene (id_utilisateur)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__UTILISATEUR AS SELECT id, id_role, email, mot_de_passe, prenom, nom, telephone, date_creation, date_modification FROM UTILISATEUR');
        $this->addSql('DROP TABLE UTILISATEUR');
        $this->addSql('CREATE TABLE UTILISATEUR (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_role INTEGER NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, date_modification DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CONSTRAINT FK_901FF15BDC499668 FOREIGN KEY (id_role) REFERENCES ROLE (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO UTILISATEUR (id, id_role, email, mot_de_passe, prenom, nom, telephone, date_creation, date_modification) SELECT id, id_role, email, mot_de_passe, prenom, nom, telephone, date_creation, date_modification FROM __temp__UTILISATEUR');
        $this->addSql('DROP TABLE __temp__UTILISATEUR');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_901FF15BE7927C74 ON UTILISATEUR (email)');
        $this->addSql('CREATE INDEX IDX_901FF15BDC499668 ON UTILISATEUR (id_role)');
    }
}
