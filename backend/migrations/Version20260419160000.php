<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product variants, options and attributes.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Migration can only be executed safely on PostgreSQL.');

        $this->addSql('CREATE TABLE product_option_groups (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID NOT NULL, product_id UUID NOT NULL, code VARCHAR(80) NOT NULL, name VARCHAR(120) NOT NULL, position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_option_values (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID NOT NULL, option_group_id UUID NOT NULL, code VARCHAR(80) NOT NULL, value VARCHAR(120) NOT NULL, position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_variants (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID NOT NULL, product_id UUID NOT NULL, name VARCHAR(120) NOT NULL, sku VARCHAR(120) NOT NULL, price_adjustment NUMERIC(12, 2) DEFAULT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_variant_option_values (id UUID NOT NULL, variant_id UUID NOT NULL, option_value_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_attributes (id UUID NOT NULL, tenant_id UUID NOT NULL, store_id UUID NOT NULL, product_id UUID NOT NULL, code VARCHAR(80) NOT NULL, name VARCHAR(120) NOT NULL, value TEXT NOT NULL, position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE UNIQUE INDEX uniq_product_option_groups_product_code ON product_option_groups (product_id, code)');
        $this->addSql('CREATE INDEX idx_product_option_groups_tenant ON product_option_groups (tenant_id)');
        $this->addSql('CREATE INDEX idx_product_option_groups_store ON product_option_groups (store_id)');
        $this->addSql('CREATE INDEX idx_product_option_groups_product ON product_option_groups (product_id)');

        $this->addSql('CREATE UNIQUE INDEX uniq_product_option_values_group_code ON product_option_values (option_group_id, code)');
        $this->addSql('CREATE INDEX idx_product_option_values_tenant ON product_option_values (tenant_id)');
        $this->addSql('CREATE INDEX idx_product_option_values_store ON product_option_values (store_id)');
        $this->addSql('CREATE INDEX idx_product_option_values_group ON product_option_values (option_group_id)');

        $this->addSql('CREATE UNIQUE INDEX uniq_product_variants_product_sku ON product_variants (product_id, sku)');
        $this->addSql('CREATE INDEX idx_product_variants_tenant ON product_variants (tenant_id)');
        $this->addSql('CREATE INDEX idx_product_variants_store ON product_variants (store_id)');
        $this->addSql('CREATE INDEX idx_product_variants_product ON product_variants (product_id)');

        $this->addSql('CREATE UNIQUE INDEX uniq_product_variant_option_values_pair ON product_variant_option_values (variant_id, option_value_id)');
        $this->addSql('CREATE INDEX idx_product_variant_option_values_variant ON product_variant_option_values (variant_id)');
        $this->addSql('CREATE INDEX idx_product_variant_option_values_value ON product_variant_option_values (option_value_id)');

        $this->addSql('CREATE UNIQUE INDEX uniq_product_attributes_product_code ON product_attributes (product_id, code)');
        $this->addSql('CREATE INDEX idx_product_attributes_tenant ON product_attributes (tenant_id)');
        $this->addSql('CREATE INDEX idx_product_attributes_store ON product_attributes (store_id)');
        $this->addSql('CREATE INDEX idx_product_attributes_product ON product_attributes (product_id)');

        $this->addSql('ALTER TABLE product_option_groups ADD CONSTRAINT fk_product_option_groups_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_option_groups ADD CONSTRAINT fk_product_option_groups_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_option_groups ADD CONSTRAINT fk_product_option_groups_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_option_values ADD CONSTRAINT fk_product_option_values_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_option_values ADD CONSTRAINT fk_product_option_values_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_option_values ADD CONSTRAINT fk_product_option_values_group FOREIGN KEY (option_group_id) REFERENCES product_option_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_variants ADD CONSTRAINT fk_product_variants_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_variants ADD CONSTRAINT fk_product_variants_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_variants ADD CONSTRAINT fk_product_variants_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_variant_option_values ADD CONSTRAINT fk_product_variant_option_values_variant FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_variant_option_values ADD CONSTRAINT fk_product_variant_option_values_value FOREIGN KEY (option_value_id) REFERENCES product_option_values (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT fk_product_attributes_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT fk_product_attributes_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT fk_product_attributes_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Migration can only be executed safely on PostgreSQL.');

        $this->addSql('DROP TABLE product_variant_option_values');
        $this->addSql('DROP TABLE product_attributes');
        $this->addSql('DROP TABLE product_variants');
        $this->addSql('DROP TABLE product_option_values');
        $this->addSql('DROP TABLE product_option_groups');
    }
}
