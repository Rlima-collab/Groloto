<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251216100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add id_evenement nullable column on TACHE to link tasks to events (on delete set null).';
    }

    public function up(Schema $schema): void
    {
        // SQLite: simple ALTER TABLE ADD COLUMN (no FK enforcement here)
        $this->addSql('ALTER TABLE TACHE ADD COLUMN id_evenement INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // SQLite cannot drop columns easily
        $this->throwIrreversibleMigrationException();
    }
}
