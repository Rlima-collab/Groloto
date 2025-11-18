<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251118120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne remarque_acceptation à INSCRIPTION_MECENE';
    }

    public function up(Schema $schema): void
    {
        // SQLite accepte ADD COLUMN
        $this->addSql('ALTER TABLE INSCRIPTION_MECENE ADD COLUMN remarque_acceptation CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Pour revenir en arrière sous SQLite il faut recréer la table sans la colonne
        $this->addSql('CREATE TEMPORARY TABLE __temp__INSCRIPTION_MECENE AS SELECT id, id_mecene, id_evenement, description_don, montant_estime, statut, date_inscription, remarques, nom_don, categorie, quantite, valeur_unitaire, remarque_refus FROM INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE INSCRIPTION_MECENE');
        $this->addSql('CREATE TABLE INSCRIPTION_MECENE (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_mecene INTEGER NOT NULL, id_evenement INTEGER NOT NULL, description_don CLOB NOT NULL, montant_estime DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_inscription DATETIME NOT NULL, remarques CLOB DEFAULT NULL, nom_don VARCHAR(255) NOT NULL, categorie VARCHAR(50) NOT NULL, quantite INTEGER NOT NULL, valeur_unitaire DOUBLE PRECISION DEFAULT NULL, remarque_refus CLOB DEFAULT NULL, FOREIGN KEY (id_mecene) REFERENCES MECENE (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (id_evenement) REFERENCES EVENEMENT (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO INSCRIPTION_MECENE (id, id_mecene, id_evenement, description_don, montant_estime, statut, date_inscription, remarques, nom_don, categorie, quantite, valeur_unitaire, remarque_refus) SELECT id, id_mecene, id_evenement, description_don, montant_estime, statut, date_inscription, remarques, nom_don, categorie, quantite, valeur_unitaire, remarque_refus FROM __temp__INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE __temp__INSCRIPTION_MECENE');
    }
}
