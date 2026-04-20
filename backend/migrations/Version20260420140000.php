<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add store publication moderation decision metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('ALTER TABLE stores ADD publication_reviewed_by VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD publication_reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD publication_review_reason TEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_stores_publication_status ON stores (publication_status)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP INDEX IF EXISTS idx_stores_publication_status');
        $this->addSql('ALTER TABLE stores DROP publication_review_reason');
        $this->addSql('ALTER TABLE stores DROP publication_reviewed_at');
        $this->addSql('ALTER TABLE stores DROP publication_reviewed_by');
    }
}
