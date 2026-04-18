<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418105000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create resource usage and store usage limits tables.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('CREATE TABLE resource_usage_daily (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID DEFAULT NULL, usage_date DATE NOT NULL, resource_type VARCHAR(80) NOT NULL, quantity NUMERIC(20, 4) NOT NULL, unit VARCHAR(32) NOT NULL, source VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE store_usage_limits (id UUID NOT NULL, store_id UUID NOT NULL, plan_code VARCHAR(64) NOT NULL, resource_type VARCHAR(80) NOT NULL, soft_limit NUMERIC(20, 4) DEFAULT NULL, hard_limit NUMERIC(20, 4) DEFAULT NULL, unit VARCHAR(32) NOT NULL, reset_period VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE INDEX idx_resource_usage_daily_tenant_date ON resource_usage_daily (tenant_id, usage_date)');
        $this->addSql('CREATE INDEX idx_resource_usage_daily_store_date ON resource_usage_daily (store_id, usage_date)');
        $this->addSql('CREATE INDEX idx_resource_usage_daily_type_date ON resource_usage_daily (resource_type, usage_date)');
        $this->addSql('CREATE INDEX idx_store_usage_limits_store ON store_usage_limits (store_id)');
        $this->addSql('CREATE INDEX idx_store_usage_limits_plan ON store_usage_limits (plan_code)');
        $this->addSql('CREATE UNIQUE INDEX uniq_store_usage_limits_resource ON store_usage_limits (store_id, resource_type)');

        $this->addSql('ALTER TABLE resource_usage_daily ADD CONSTRAINT fk_resource_usage_daily_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_usage_daily ADD CONSTRAINT fk_resource_usage_daily_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE store_usage_limits ADD CONSTRAINT fk_store_usage_limits_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP TABLE store_usage_limits');
        $this->addSql('DROP TABLE resource_usage_daily');
    }
}
