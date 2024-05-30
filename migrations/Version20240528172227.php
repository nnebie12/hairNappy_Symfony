<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528172227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de colonnes et modification des colonnes pour permettre les valeurs NULL';
    }

    public function up(Schema $schema): void
    {
        // Permettre les valeurs NULL pour certaines colonnes
        $this->addSql('ALTER TABLE user MODIFY entreprise VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user MODIFY siret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user MODIFY genre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user MODIFY date_naissance VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert changes if necessary
        $this->addSql('ALTER TABLE user MODIFY entreprise VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user MODIFY siret VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user MODIFY genre VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user MODIFY date_naissance VARCHAR(255) NOT NULL');
    }
}