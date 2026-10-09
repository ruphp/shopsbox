<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add store plan and one-time trial period.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql("ALTER TABLE stores ADD plan VARCHAR(32) DEFAULT 'free' NOT NULL");
        $this->addSql('ALTER TABLE stores ALTER plan DROP DEFAULT');
        $this->addSql('ALTER TABLE stores ADD trial_started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD plan_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('ALTER TABLE stores DROP plan_expires_at');
        $this->addSql('ALTER TABLE stores DROP trial_started_at');
        $this->addSql('ALTER TABLE stores DROP plan');
    }
}
