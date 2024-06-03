<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240601081349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de la colonne date_naissance de la table user';
    }

    public function up(Schema $schema): void
    {
        // Vérifier l'existence de la colonne date_naissance avant de la supprimer
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('user');

        if (array_key_exists('date_naissance', $columns)) {
            $this->addSql('ALTER TABLE user DROP COLUMN date_naissance');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD date_naissance DATE DEFAULT NULL');
    }
}
