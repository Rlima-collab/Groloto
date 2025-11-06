<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106084707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__TACHE AS SELECT id, id_weekend, titre, poste_requis, debut, fin, max_personnes, remarque FROM TACHE');
        $this->addSql('DROP TABLE TACHE');
        $this->addSql('CREATE TABLE TACHE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_weekend INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, poste_requis VARCHAR(100) DEFAULT NULL, debut DATETIME NOT NULL, fin DATETIME NOT NULL, max_personnes INTEGER DEFAULT NULL, remarque CLOB DEFAULT NULL, CONSTRAINT FK_64D3E2C5704F64DE FOREIGN KEY (id_weekend) REFERENCES WEEKEND (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO TACHE (id, id_weekend, titre, poste_requis, debut, fin, max_personnes, remarque) SELECT id, id_weekend, titre, poste_requis, debut, fin, max_personnes, remarque FROM __temp__TACHE');
        $this->addSql('DROP TABLE __temp__TACHE');
        $this->addSql('CREATE INDEX IDX_64D3E2C5704F64DE ON TACHE (id_weekend)');
        $this->addSql('ALTER TABLE WEEKEND ADD COLUMN cover_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__TACHE AS SELECT id, id_weekend, titre, debut, fin, poste_requis, max_personnes, remarque FROM TACHE');
        $this->addSql('DROP TABLE TACHE');
        $this->addSql('CREATE TABLE TACHE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_weekend INTEGER NOT NULL, titre VARCHAR(255) NOT NULL, debut DATETIME NOT NULL, fin DATETIME NOT NULL, poste_requis VARCHAR(100) NOT NULL, max_personnes INTEGER NOT NULL, remarque CLOB DEFAULT NULL, CONSTRAINT FK_64D3E2C5704F64DE FOREIGN KEY (id_weekend) REFERENCES WEEKEND (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO TACHE (id, id_weekend, titre, debut, fin, poste_requis, max_personnes, remarque) SELECT id, id_weekend, titre, debut, fin, poste_requis, max_personnes, remarque FROM __temp__TACHE');
        $this->addSql('DROP TABLE __temp__TACHE');
        $this->addSql('CREATE INDEX IDX_64D3E2C5704F64DE ON TACHE (id_weekend)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__WEEKEND AS SELECT id, nom, date_vendredi, date_samedi, date_dimanche, date_creation FROM WEEKEND');
        $this->addSql('DROP TABLE WEEKEND');
        $this->addSql('CREATE TABLE WEEKEND (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, date_vendredi DATE NOT NULL, date_samedi DATE NOT NULL, date_dimanche DATE NOT NULL, date_creation DATETIME NOT NULL)');
        $this->addSql('INSERT INTO WEEKEND (id, nom, date_vendredi, date_samedi, date_dimanche, date_creation) SELECT id, nom, date_vendredi, date_samedi, date_dimanche, date_creation FROM __temp__WEEKEND');
        $this->addSql('DROP TABLE __temp__WEEKEND');
    }
}
