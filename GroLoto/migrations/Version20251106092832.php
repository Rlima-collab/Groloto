<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106092832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE EVENEMENT ADD COLUMN heure_debut TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE EVENEMENT ADD COLUMN duree_minutes INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE EVENEMENT ADD COLUMN image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__EVENEMENT AS SELECT id, id_weekend, nom, description, date_debut, date_fin, lieu, date_creation FROM EVENEMENT');
        $this->addSql('DROP TABLE EVENEMENT');
        $this->addSql('CREATE TABLE EVENEMENT (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_weekend INTEGER DEFAULT NULL, nom VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, lieu VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, CONSTRAINT FK_4FE476F1704F64DE FOREIGN KEY (id_weekend) REFERENCES WEEKEND (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO EVENEMENT (id, id_weekend, nom, description, date_debut, date_fin, lieu, date_creation) SELECT id, id_weekend, nom, description, date_debut, date_fin, lieu, date_creation FROM __temp__EVENEMENT');
        $this->addSql('DROP TABLE __temp__EVENEMENT');
        $this->addSql('CREATE INDEX IDX_4FE476F1704F64DE ON EVENEMENT (id_weekend)');
    }
}
