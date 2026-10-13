<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add an aggregate view counter used to rank popular storefront products.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');
        $this->addSql('ALTER TABLE products ADD view_count INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX idx_products_store_views ON products (store_id, view_count)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_products_store_views');
        $this->addSql('ALTER TABLE products DROP view_count');
    }
}
