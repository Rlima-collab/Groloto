<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251118123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne id_weekend à la table LOT';
    }

    public function up(Schema $schema): void
    {
        // SQLite: ADD COLUMN est supporté pour colonnes simples
        $this->addSql('ALTER TABLE LOT ADD COLUMN id_weekend INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Recreate table without id_weekend (SQLite requires table recreation)
        $this->addSql('CREATE TEMPORARY TABLE __temp__LOT AS SELECT id, id_mecene, titre, description, quantite, valeur_estimee, date_creation FROM LOT');
        $this->addSql('DROP TABLE LOT');
        $this->addSql('CREATE TABLE LOT (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_mecene INTEGER DEFAULT NULL, titre VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, quantite INTEGER DEFAULT 1 NOT NULL, valeur_estimee DOUBLE PRECISION DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP, CONSTRAINT FK_9D266B91704F64DE FOREIGN KEY (id_mecene) REFERENCES MECENE (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO LOT (id, id_mecene, titre, description, quantite, valeur_estimee, date_creation) SELECT id, id_mecene, titre, description, quantite, valeur_estimee, date_creation FROM __temp__LOT');
        $this->addSql('DROP TABLE __temp__LOT');
    }
}
