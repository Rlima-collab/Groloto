<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260204160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create EXCEL_DATA table for dynamic Excel sheet storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE EXCEL_DATA (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sheet_name VARCHAR(255) NOT NULL, columns JSON NOT NULL, data JSON NOT NULL, imported_at DATETIME NOT NULL, original_filename VARCHAR(255))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE EXCEL_DATA');
    }
}
