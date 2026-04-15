<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260414090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tenant foundation tables for tenants, stores, users, roles and role assignments.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('CREATE TABLE tenants (id UUID NOT NULL, name VARCHAR(160) NOT NULL, status VARCHAR(32) NOT NULL, billing_email VARCHAR(180) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE stores (id UUID NOT NULL, tenant_id UUID NOT NULL, name VARCHAR(160) NOT NULL, slug VARCHAR(120) NOT NULL, domain VARCHAR(255) NOT NULL, status VARCHAR(32) NOT NULL, default_currency VARCHAR(3) NOT NULL, timezone VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, tenant_id UUID DEFAULT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) NOT NULL, display_name VARCHAR(120) NOT NULL, status VARCHAR(32) NOT NULL, demo BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE roles (id UUID NOT NULL, code VARCHAR(64) NOT NULL, name VARCHAR(120) NOT NULL, scope VARCHAR(32) NOT NULL, permissions JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_roles (id UUID NOT NULL, user_id UUID NOT NULL, role_id UUID NOT NULL, tenant_id UUID DEFAULT NULL, store_id UUID DEFAULT NULL, assigned_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE UNIQUE INDEX uniq_stores_tenant_slug ON stores (tenant_id, slug)');
        $this->addSql('CREATE UNIQUE INDEX uniq_stores_domain ON stores (domain)');
        $this->addSql('CREATE UNIQUE INDEX uniq_users_email ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_roles_code ON roles (code)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_roles_scope ON user_roles (user_id, role_id, tenant_id, store_id)');
        $this->addSql('CREATE INDEX idx_stores_tenant ON stores (tenant_id)');
        $this->addSql('CREATE INDEX idx_users_tenant ON users (tenant_id)');
        $this->addSql('CREATE INDEX idx_user_roles_user ON user_roles (user_id)');
        $this->addSql('CREATE INDEX idx_user_roles_role ON user_roles (role_id)');
        $this->addSql('CREATE INDEX idx_user_roles_tenant ON user_roles (tenant_id)');
        $this->addSql('CREATE INDEX idx_user_roles_store ON user_roles (store_id)');

        $this->addSql('ALTER TABLE stores ADD CONSTRAINT fk_stores_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT fk_user_roles_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT fk_user_roles_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE stores');
        $this->addSql('DROP TABLE tenants');
    }
}
