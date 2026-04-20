<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add platform moderator role and moderation action log.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql("INSERT INTO roles (id, code, name, scope, permissions, created_at) VALUES ('33333333-0010-4333-8333-333333333333', 'platform_moderator', 'Platform Moderator', 'platform', '[\"platform.moderation.view\",\"platform.moderation.review\"]', NOW()) ON CONFLICT (code) DO NOTHING");
        $this->addSql('CREATE TABLE moderator_action_logs (id UUID NOT NULL, moderator_id VARCHAR(80) NOT NULL, item_type VARCHAR(32) NOT NULL, item_id UUID NOT NULL, decision VARCHAR(32) NOT NULL, reason TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_moderator_action_logs_moderator ON moderator_action_logs (moderator_id)');
        $this->addSql('CREATE INDEX idx_moderator_action_logs_item ON moderator_action_logs (item_type, item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP TABLE moderator_action_logs');
        $this->addSql("DELETE FROM roles WHERE code = 'platform_moderator'");
    }
}
