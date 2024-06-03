<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240603170729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la clé étrangère et de l’index pour user_id dans la table appointment';
    }

    public function up(Schema $schema): void
    {
        // Vérifier l'existence de la clé étrangère avant de l'ajouter
        $schemaManager = $this->connection->createSchemaManager();
        $foreignKeys = $schemaManager->listTableForeignKeys('appointment');

        $foreignKeyExists = false;
        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->getLocalColumns() === ['user_id']) {
                $foreignKeyExists = true;
                break;
            }
        }

        if (!$foreignKeyExists) {
            $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        }

        // Vérifier l'existence de l'index avant de l'ajouter
        $indexes = $schemaManager->listTableIndexes('appointment');

        if (!array_key_exists('IDX_FE38F844A76ED395', $indexes)) {
            $this->addSql('CREATE INDEX IDX_FE38F844A76ED395 ON appointment (user_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // Vérifier l'existence de la clé étrangère avant de la supprimer
        $schemaManager = $this->connection->createSchemaManager();
        $foreignKeys = $schemaManager->listTableForeignKeys('appointment');

        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->getLocalColumns() === ['user_id']) {
                $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY ' . $foreignKey->getName());
                break;
            }
        }

        // Vérifier l'existence de l'index avant de le supprimer
        $indexes = $schemaManager->listTableIndexes('appointment');

        if (array_key_exists('IDX_FE38F844A76ED395', $indexes)) {
            $this->addSql('DROP INDEX IDX_FE38F844A76ED395 ON appointment');
        }

        $this->addSql('ALTER TABLE appointment CHANGE user_id user_id INT DEFAULT NULL');
    }
}
