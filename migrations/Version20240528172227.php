<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240528172227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la relation user à appointment';
    }

    public function up(Schema $schema): void
    {
        // Vérifier l'existence de l'utilisateur avec l'ID 3
        $this->addSql('INSERT INTO user (id, email, password) SELECT 3, "default@example.com", "password" WHERE NOT EXISTS (SELECT 1 FROM user WHERE id = 3)');

        // Vérifier l'existence de la colonne user_id avant de la supprimer
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('appointment');

        if (array_key_exists('user_id', $columns)) {
            $this->addSql('ALTER TABLE appointment DROP COLUMN user_id');
        }

        // Ajouter la colonne user_id
        $this->addSql('ALTER TABLE appointment ADD user_id INT DEFAULT NULL');

        // Mettre à jour les lignes existantes pour définir une valeur par défaut pour user_id
        $this->addSql('UPDATE appointment SET user_id = 3 WHERE user_id IS NULL');

        // Modifier la colonne pour qu'elle soit non nullable
        $this->addSql('ALTER TABLE appointment MODIFY user_id INT NOT NULL');

        // Ajouter la clé étrangère
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY IF EXISTS FK_FE38F844A76ED395');
        $this->addSql('ALTER TABLE appointment DROP COLUMN IF EXISTS user_id');
    }
}
