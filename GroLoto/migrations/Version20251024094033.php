<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024094033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE DEMANDE_TACHE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_tache INTEGER NOT NULL, id_benevole INTEGER NOT NULL, id_admin_reponse INTEGER DEFAULT NULL, date_demande DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, message_benevole CLOB DEFAULT NULL, message_admin CLOB DEFAULT NULL, date_reponse DATETIME DEFAULT NULL, CONSTRAINT FK_44B9A48F7D026145 FOREIGN KEY (id_tache) REFERENCES TACHE (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_44B9A48FE4DAA34E FOREIGN KEY (id_benevole) REFERENCES BENEVOLE (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_44B9A48F25A28D2B FOREIGN KEY (id_admin_reponse) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_44B9A48F7D026145 ON DEMANDE_TACHE (id_tache)');
        $this->addSql('CREATE INDEX IDX_44B9A48FE4DAA34E ON DEMANDE_TACHE (id_benevole)');
        $this->addSql('CREATE INDEX IDX_44B9A48F25A28D2B ON DEMANDE_TACHE (id_admin_reponse)');
        $this->addSql('CREATE TABLE NOTIFICATION (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_destinataire INTEGER NOT NULL, type VARCHAR(50) NOT NULL, message CLOB NOT NULL, lien VARCHAR(255) DEFAULT NULL, lue BOOLEAN DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_2A663FDADD688AE0 FOREIGN KEY (id_destinataire) REFERENCES UTILISATEUR (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2A663FDADD688AE0 ON NOTIFICATION (id_destinataire)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE DEMANDE_TACHE');
        $this->addSql('DROP TABLE NOTIFICATION');
    }
}
