<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012124000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Separate reusable store media files from product image attachments.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');
        $this->addSql('CREATE TABLE media_files (id UUID NOT NULL, store_id UUID NOT NULL, storage_key VARCHAR(500) NOT NULL, public_url VARCHAR(700) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_media_files_store ON media_files (store_id)');
        $this->addSql('ALTER TABLE media_files ADD CONSTRAINT fk_media_files_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql("INSERT INTO media_files (id, store_id, storage_key, public_url, original_name, mime_type, size, created_at, deleted_at) SELECT pi.id, p.store_id, pi.storage_key, pi.public_url, regexp_replace(pi.storage_key, '^.*/', ''), pi.mime_type, pi.size, pi.created_at, pi.deleted_at FROM product_images pi JOIN products p ON p.id = pi.product_id");
        $this->addSql('ALTER TABLE product_images ADD media_file_id UUID DEFAULT NULL');
        $this->addSql('UPDATE product_images SET media_file_id = id');
        $this->addSql('ALTER TABLE product_images ALTER media_file_id SET NOT NULL');
        $this->addSql('CREATE INDEX idx_product_images_media_file ON product_images (media_file_id)');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT fk_product_images_media_file FOREIGN KEY (media_file_id) REFERENCES media_files (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_images DROP CONSTRAINT fk_product_images_media_file');
        $this->addSql('DROP INDEX idx_product_images_media_file');
        $this->addSql('ALTER TABLE product_images DROP media_file_id');
        $this->addSql('DROP TABLE media_files');
    }
}
