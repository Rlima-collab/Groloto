<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260128083000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute don_fonctionnement et don_lots à INSCRIPTION_MECENE et migre les valeurs existantes';
    }

    public function up(Schema $schema): void
    {
        // Ajouter les colonnes (SQLite supporte ADD COLUMN)
        $this->addSql("ALTER TABLE INSCRIPTION_MECENE ADD COLUMN don_fonctionnement BOOLEAN DEFAULT 1 NOT NULL");
        $this->addSql("ALTER TABLE INSCRIPTION_MECENE ADD COLUMN don_lots BOOLEAN DEFAULT 0 NOT NULL");

        // Migrer les valeurs existantes depuis type_don
        $this->addSql("UPDATE INSCRIPTION_MECENE SET don_fonctionnement = 0, don_lots = 1 WHERE type_don = 'lot'");
        $this->addSql("UPDATE INSCRIPTION_MECENE SET don_fonctionnement = 1, don_lots = 0 WHERE type_don = 'fonctionnement' OR type_don IS NULL");
        $this->addSql("UPDATE INSCRIPTION_MECENE SET don_fonctionnement = 1, don_lots = 1 WHERE type_don = 'both'");
    }

    public function down(Schema $schema): void
    {
        // Restaurer type_don d'après les booléens
        $this->addSql("UPDATE INSCRIPTION_MECENE SET type_don = CASE WHEN don_fonctionnement = 1 AND don_lots = 1 THEN 'both' WHEN don_lots = 1 THEN 'lot' ELSE 'fonctionnement' END");

        // Remarque : suppression des colonnes n'est pas implémentée (SQLite nécessite reconstruction de la table).
    }
}
