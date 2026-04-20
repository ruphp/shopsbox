<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product image lifecycle fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_images ADD primary_image BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE product_images ADD position INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE product_images ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_product_images_deleted ON product_images (deleted_at)');
        $this->addSql('CREATE INDEX idx_product_images_position ON product_images (position)');
        $this->addSql('UPDATE product_images SET primary_image = true WHERE id IN (SELECT DISTINCT ON (product_id) id FROM product_images ORDER BY product_id, created_at ASC)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_product_images_deleted');
        $this->addSql('DROP INDEX idx_product_images_position');
        $this->addSql('ALTER TABLE product_images DROP primary_image');
        $this->addSql('ALTER TABLE product_images DROP position');
        $this->addSql('ALTER TABLE product_images DROP deleted_at');
    }
}
