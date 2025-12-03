<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne disponibilites (JSON texte) dans BENEVOLE';
    }

    public function up(Schema $schema): void
    {
        // SQLite: ajout simple de colonne
        $this->addSql("ALTER TABLE BENEVOLE ADD COLUMN disponibilites CLOB DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        // SQLite ne supporte pas DROP COLUMN facilement; on laisse la colonne en place
        // Option alternative: recréer la table sans la colonne (non nécessaire ici)
    }
}
