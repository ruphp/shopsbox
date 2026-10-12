<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create customer orders and immutable order item snapshots.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');
        $this->addSql('CREATE TABLE customer_orders (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID NOT NULL, number VARCHAR(32) NOT NULL, status VARCHAR(32) NOT NULL, customer_name VARCHAR(120) NOT NULL, customer_email VARCHAR(180) NOT NULL, customer_phone VARCHAR(40) NOT NULL, comment TEXT DEFAULT NULL, total_minor INT NOT NULL, currency VARCHAR(3) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_customer_orders_store_number ON customer_orders (store_id, number)');
        $this->addSql('CREATE INDEX idx_customer_orders_tenant_created ON customer_orders (tenant_id, created_at)');
        $this->addSql('CREATE INDEX idx_customer_orders_store_created ON customer_orders (store_id, created_at)');
        $this->addSql('ALTER TABLE customer_orders ADD CONSTRAINT fk_customer_orders_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer_orders ADD CONSTRAINT fk_customer_orders_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE customer_order_items (id UUID NOT NULL, order_id UUID NOT NULL, product_id UUID NOT NULL, variant_id UUID DEFAULT NULL, product_name VARCHAR(180) NOT NULL, variant_name VARCHAR(120) DEFAULT NULL, unit_price_minor INT NOT NULL, quantity INT NOT NULL, line_total_minor INT NOT NULL, position INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_customer_order_items_order_position ON customer_order_items (order_id, position)');
        $this->addSql('ALTER TABLE customer_order_items ADD CONSTRAINT fk_customer_order_items_order FOREIGN KEY (order_id) REFERENCES customer_orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');
        $this->addSql('DROP TABLE customer_order_items');
        $this->addSql('DROP TABLE customer_orders');
    }
}
