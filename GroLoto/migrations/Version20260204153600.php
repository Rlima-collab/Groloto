<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260204153600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create MEMBRE table for managing members';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE MEMBRE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL UNIQUE, numero_billet VARCHAR(50), tarif VARCHAR(50), date_creation DATETIME, date_seance DATETIME, montant_tarif INTEGER, code_promo VARCHAR(50), montant_code_promo VARCHAR(255))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE MEMBRE');
    }
}
