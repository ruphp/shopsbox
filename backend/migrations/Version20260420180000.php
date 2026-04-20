<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add public business settings to stores.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('ALTER TABLE stores ADD contact_city VARCHAR(160) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD contact_address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD seller_legal_name VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD seller_inn VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD seller_legal_text TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD delivery_text TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD payment_text TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('ALTER TABLE stores DROP payment_text');
        $this->addSql('ALTER TABLE stores DROP delivery_text');
        $this->addSql('ALTER TABLE stores DROP seller_legal_text');
        $this->addSql('ALTER TABLE stores DROP seller_inn');
        $this->addSql('ALTER TABLE stores DROP seller_legal_name');
        $this->addSql('ALTER TABLE stores DROP contact_address');
        $this->addSql('ALTER TABLE stores DROP contact_city');
    }
}
