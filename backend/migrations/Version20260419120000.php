<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product image metadata table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('CREATE TABLE product_images (id UUID NOT NULL, product_id UUID NOT NULL, storage_key VARCHAR(500) NOT NULL, public_url VARCHAR(700) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_product_images_product ON product_images (product_id)');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP TABLE product_images');
    }
}
