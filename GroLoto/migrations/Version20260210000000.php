<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * MySQL-compatible migration for GroLoto schema
 */
final class Version20260210000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create GroLoto schema compatible with MySQL/MariaDB';
    }

    public function up(Schema $schema): void
    {
        // Drop existing incompatible SQLite migrations
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');

        // WEEKEND table
        if (!$schema->getTable('WEEKEND', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS WEEKEND (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(255) NOT NULL,
                date_debut DATE NOT NULL,
                date_fin DATE NOT NULL,
                task_offset_before INT DEFAULT 2,
                task_offset_after INT DEFAULT 3,
                cover_image VARCHAR(255),
                description LONGTEXT,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // ROLE table
        if (!$schema->getTable('ROLE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS ROLE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(180) NOT NULL UNIQUE,
                CONSTRAINT uk_ROLE_nom UNIQUE KEY (nom)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // UTILISATEUR table
        if (!$schema->getTable('UTILISATEUR', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS UTILISATEUR (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(180) NOT NULL UNIQUE,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                prenom VARCHAR(255),
                nom VARCHAR(255),
                telephone VARCHAR(20),
                avatar VARCHAR(255),
                actif BOOLEAN DEFAULT 1,
                date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT uk_UTILISATEUR_email UNIQUE KEY (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // BENEVOLE table
        if (!$schema->getTable('BENEVOLE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS BENEVOLE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT,
                remarque LONGTEXT,
                disponibilites LONGTEXT,
                actif BOOLEAN DEFAULT 1,
                FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id),
                CONSTRAINT uk_BENEVOLE_id_utilisateur UNIQUE KEY (id_utilisateur)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // MECENE table
        if (!$schema->getTable('MECENE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS MECENE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT,
                raison_sociale VARCHAR(255),
                adresse VARCHAR(255),
                codepostal VARCHAR(10),
                ville VARCHAR(255),
                site_web VARCHAR(255),
                date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // LOT table
        if (!$schema->getTable('LOT', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS LOT (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_mecene INT,
                titre VARCHAR(255) NOT NULL,
                description LONGTEXT,
                valeur_estime DECIMAL(10,2),
                quantite INT DEFAULT 1,
                montant_transaction DECIMAL(10,2),
                statut VARCHAR(50) DEFAULT "disponible",
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_mecene) REFERENCES MECENE(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // CONVENTION table
        if (!$schema->getTable('CONVENTION', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS CONVENTION (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_mecene INT NOT NULL,
                nom_modele VARCHAR(255),
                url_pdf VARCHAR(255),
                date_signature DATETIME,
                methode_signature VARCHAR(255) DEFAULT "aucune",
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_mecene) REFERENCES MECENE(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // INSCRIPTION_MECENE table
        if (!$schema->getTable('INSCRIPTION_MECENE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS INSCRIPTION_MECENE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_mecene INT NOT NULL,
                id_weekend INT NOT NULL,
                participation_type VARCHAR(50) DEFAULT "partenaire",
                montant_souscrit DECIMAL(10,2),
                statut VARCHAR(50) DEFAULT "confirmee",
                date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_mecene) REFERENCES MECENE(id),
                FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // EVENEMENT table
        if (!$schema->getTable('EVENEMENT', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS EVENEMENT (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_weekend INT,
                nom VARCHAR(255) NOT NULL,
                description LONGTEXT,
                date_debut DATE,
                date_fin DATE,
                lieu VARCHAR(255),
                heure_debut TIME,
                duree_minutes INT,
                image VARCHAR(255),
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // CRENEAU table
        if (!$schema->getTable('CRENEAU', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS CRENEAU (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_evenement INT NOT NULL,
                titre VARCHAR(255) NOT NULL,
                poste_requis VARCHAR(255),
                debut DATETIME NOT NULL,
                fin DATETIME NOT NULL,
                max_personnes INT NOT NULL,
                remarque LONGTEXT,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // TACHE table
        if (!$schema->getTable('TACHE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS TACHE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_evenement INT NOT NULL,
                titre VARCHAR(255) NOT NULL,
                description LONGTEXT,
                status VARCHAR(50) DEFAULT "assignee",
                priorite VARCHAR(20) DEFAULT "moyen",
                date_debut DATETIME,
                date_fin DATETIME,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id),
                INDEX idx_evenement (id_evenement)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // AFFECTATION_TACHE table
        if (!$schema->getTable('AFFECTATION_TACHE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS AFFECTATION_TACHE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_tache INT NOT NULL,
                id_benevole INT NOT NULL,
                id_utilisateur INT,
                date_affectation DATETIME NOT NULL,
                statut VARCHAR(20) NOT NULL,
                remarque LONGTEXT,
                FOREIGN KEY (id_tache) REFERENCES TACHE(id),
                FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
                FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // DEMANDE_TACHE table
        if (!$schema->getTable('DEMANDE_TACHE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS DEMANDE_TACHE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_tache INT NOT NULL,
                id_benevole INT NOT NULL,
                id_admin_reponse INT,
                date_demande DATETIME NOT NULL,
                statut VARCHAR(20) NOT NULL,
                message_benevole LONGTEXT,
                message_admin LONGTEXT,
                date_reponse DATETIME,
                FOREIGN KEY (id_tache) REFERENCES TACHE(id),
                FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
                FOREIGN KEY (id_admin_reponse) REFERENCES UTILISATEUR(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // DISPONIBILITE_BENEVOLE table
        if (!$schema->getTable('DISPONIBILITE_BENEVOLE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS DISPONIBILITE_BENEVOLE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_benevole INT NOT NULL,
                id_evenement INT NOT NULL,
                debut DATETIME,
                fin DATETIME,
                notes LONGTEXT,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
                FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // DISPONIBILITE_WEEKEND table
        if (!$schema->getTable('DISPONIBILITE_WEEKEND', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS DISPONIBILITE_WEEKEND (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_benevole INT NOT NULL,
                id_weekend INT NOT NULL,
                debut DATETIME,
                fin DATETIME,
                notes LONGTEXT,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
                FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // PLAGE_HORAIRE table
        if (!$schema->getTable('PLAGE_HORAIRE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS PLAGE_HORAIRE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_tache INT,
                debut DATETIME NOT NULL,
                fin DATETIME NOT NULL,
                status VARCHAR(50) DEFAULT "planifiee",
                FOREIGN KEY (id_tache) REFERENCES TACHE(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // STOCK table
        if (!$schema->getTable('STOCK', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS STOCK (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_weekend INT,
                nom VARCHAR(255) NOT NULL,
                quantite INT DEFAULT 0,
                prix_unitaire DECIMAL(10,2),
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_weekend) REFERENCES WEEKEND(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // HISTORIQUE_STOCK table
        if (!$schema->getTable('HISTORIQUE_STOCK', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS HISTORIQUE_STOCK (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_stock INT,
                ancien_quantite INT,
                nouveau_quantite INT,
                motif VARCHAR(255),
                date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_stock) REFERENCES STOCK(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // COMMUNICATION table
        if (!$schema->getTable('COMMUNICATION', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS COMMUNICATION (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_evenement INT,
                titre VARCHAR(255) NOT NULL,
                type VARCHAR(255) DEFAULT "post",
                date_prevue DATETIME,
                statut VARCHAR(255) DEFAULT "brouillon",
                budget DECIMAL(10,2) DEFAULT 0,
                notes LONGTEXT,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // HELLOASSO table
        if (!$schema->getTable('HELLOASSO', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS HELLOASSO (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_evenement INT,
                id_externe VARCHAR(255),
                prenom VARCHAR(255),
                nom VARCHAR(255),
                email VARCHAR(255),
                telephone VARCHAR(255),
                type_ticket VARCHAR(255),
                date_achat DATETIME,
                date_import DATETIME,
                FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // DEMANDE_ANNULATION table
        if (!$schema->getTable('DEMANDE_ANNULATION', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS DEMANDE_ANNULATION (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_affectation INT,
                id_benevole INT NOT NULL,
                id_admin_reponse INT,
                date_demande DATETIME NOT NULL,
                statut VARCHAR(20) NOT NULL,
                motif_benevole LONGTEXT,
                tache_titre VARCHAR(255),
                message_admin LONGTEXT,
                date_reponse DATETIME,
                CONSTRAINT fk_affectation FOREIGN KEY (id_affectation) REFERENCES AFFECTATION_TACHE(id) ON DELETE SET NULL,
                FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id),
                FOREIGN KEY (id_admin_reponse) REFERENCES UTILISATEUR(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // HISTORIQUE_EVENEMENT table
        if (!$schema->getTable('HISTORIQUE_EVENEMENT', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS HISTORIQUE_EVENEMENT (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_evenement INT NOT NULL,
                id_utilisateur INT,
                action VARCHAR(50) NOT NULL,
                description LONGTEXT,
                date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id),
                FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // CONTACT_MESSAGE table
        if (!$schema->getTable('CONTACT_MESSAGE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS CONTACT_MESSAGE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                repondu_par_id INT,
                parent_id INT,
                nom VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                destinataire VARCHAR(255),
                message LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL,
                lu BOOLEAN NOT NULL DEFAULT 0,
                reponse LONGTEXT,
                repondu_le DATETIME,
                cloturee BOOLEAN DEFAULT 0 NOT NULL,
                masquee_pour LONGTEXT,
                FOREIGN KEY (repondu_par_id) REFERENCES UTILISATEUR(id),
                FOREIGN KEY (parent_id) REFERENCES CONTACT_MESSAGE(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // NOTIFICATION table
        if (!$schema->getTable('NOTIFICATION', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS NOTIFICATION (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT NOT NULL,
                type VARCHAR(100) NOT NULL,
                titre VARCHAR(255),
                message LONGTEXT,
                lue BOOLEAN DEFAULT 0,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                date_lecture DATETIME,
                FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // RECU_FISCAL table
        if (!$schema->getTable('RECU_FISCAL', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS RECU_FISCAL (
                id INT AUTO_INCREMENT PRIMARY KEY,
                destinataire_id INT NOT NULL,
                envoye_par_id INT NOT NULL,
                type VARCHAR(20) NOT NULL,
                fichier VARCHAR(255) NOT NULL,
                annee INT NOT NULL,
                created_at DATETIME NOT NULL,
                FOREIGN KEY (destinataire_id) REFERENCES UTILISATEUR(id),
                FOREIGN KEY (envoye_par_id) REFERENCES UTILISATEUR(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // PARAMETRE table
        if (!$schema->getTable('PARAMETRE', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS PARAMETRE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cle VARCHAR(255) NOT NULL UNIQUE,
                valeur LONGTEXT,
                description LONGTEXT,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT uk_PARAMETRE_cle UNIQUE KEY (cle)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        // MESSENGER_MESSAGES table (for Symfony Messenger)
        if (!$schema->getTable('messenger_messages', true)) {
            $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                body LONGTEXT NOT NULL,
                headers LONGTEXT NOT NULL,
                queue_name VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                available_at DATETIME NOT NULL,
                delivered_at DATETIME,
                INDEX idx_queue_name (queue_name),
                INDEX idx_available_at (available_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('DROP TABLE IF EXISTS CONTACT_MESSAGE');
        $this->addSql('DROP TABLE IF EXISTS NOTIFICATION');
        $this->addSql('DROP TABLE IF EXISTS RECU_FISCAL');
        $this->addSql('DROP TABLE IF EXISTS PARAMETRE');
        $this->addSql('DROP TABLE IF EXISTS HISTORIQUE_EVENEMENT');
        $this->addSql('DROP TABLE IF EXISTS HELLOASSO');
        $this->addSql('DROP TABLE IF EXISTS COMMUNICATION');
        $this->addSql('DROP TABLE IF EXISTS DEMANDE_ANNULATION');
        $this->addSql('DROP TABLE IF EXISTS DEMANDE_TACHE');
        $this->addSql('DROP TABLE IF EXISTS AFFECTATION_TACHE');
        $this->addSql('DROP TABLE IF EXISTS PLAGE_HORAIRE');
        $this->addSql('DROP TABLE IF EXISTS DISPONIBILITE_BENEVOLE');
        $this->addSql('DROP TABLE IF EXISTS DISPONIBILITE_WEEKEND');
        $this->addSql('DROP TABLE IF EXISTS HISTORIQUE_STOCK');
        $this->addSql('DROP TABLE IF EXISTS STOCK');
        $this->addSql('DROP TABLE IF EXISTS CRENEAU');
        $this->addSql('DROP TABLE IF EXISTS TACHE');
        $this->addSql('DROP TABLE IF EXISTS INSCRIPTION_MECENE');
        $this->addSql('DROP TABLE IF EXISTS EVENEMENT');
        $this->addSql('DROP TABLE IF EXISTS LOT');
        $this->addSql('DROP TABLE IF EXISTS CONVENTION');
        $this->addSql('DROP TABLE IF EXISTS MECENE');
        $this->addSql('DROP TABLE IF EXISTS BENEVOLE');
        $this->addSql('DROP TABLE IF EXISTS UTILISATEUR');
        $this->addSql('DROP TABLE IF EXISTS ROLE');
        $this->addSql('DROP TABLE IF EXISTS WEEKEND');
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }
}
