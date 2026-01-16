<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251219100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute task_offset_before et task_offset_after à la table WEEKEND (migration corrective)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE WEEKEND ADD COLUMN task_offset_before INTEGER DEFAULT 2 NOT NULL");
        $this->addSql("ALTER TABLE WEEKEND ADD COLUMN task_offset_after INTEGER DEFAULT 3 NOT NULL");
    }

    public function down(Schema $schema): void
    {
        throw new \RuntimeException('Irreversible migration.');
    }
}
