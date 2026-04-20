<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create moderation notification queue.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('CREATE TABLE moderation_notifications (id UUID NOT NULL, item_type VARCHAR(32) NOT NULL, item_id UUID NOT NULL, status VARCHAR(32) NOT NULL, reason VARCHAR(120) NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, failed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, error_message TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_moderation_notifications_active ON moderation_notifications (item_type, item_id, status)');
        $this->addSql('CREATE INDEX idx_moderation_notifications_status ON moderation_notifications (status)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP TABLE moderation_notifications');
    }
}
