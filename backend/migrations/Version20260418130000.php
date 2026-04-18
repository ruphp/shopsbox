<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create catalog foundation tables for categories and products.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('CREATE TABLE categories (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID NOT NULL, name VARCHAR(160) NOT NULL, slug VARCHAR(120) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE products (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID NOT NULL, category_id UUID DEFAULT NULL, name VARCHAR(180) NOT NULL, slug VARCHAR(140) NOT NULL, description TEXT DEFAULT NULL, status VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE UNIQUE INDEX uniq_categories_store_slug ON categories (store_id, slug)');
        $this->addSql('CREATE INDEX idx_categories_tenant ON categories (tenant_id)');
        $this->addSql('CREATE INDEX idx_categories_store ON categories (store_id)');

        $this->addSql('CREATE UNIQUE INDEX uniq_products_store_slug ON products (store_id, slug)');
        $this->addSql('CREATE INDEX idx_products_tenant ON products (tenant_id)');
        $this->addSql('CREATE INDEX idx_products_store ON products (store_id)');
        $this->addSql('CREATE INDEX idx_products_category ON products (category_id)');
        $this->addSql('CREATE INDEX idx_products_status ON products (status)');

        $this->addSql('ALTER TABLE categories ADD CONSTRAINT fk_categories_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT fk_categories_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT fk_products_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT fk_products_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE categories');
    }
}
