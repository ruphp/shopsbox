<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend auth codes with phone channel and verification metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql("ALTER TABLE auth_codes ADD phone VARCHAR(40) DEFAULT NULL");
        $this->addSql("ALTER TABLE auth_codes ADD channel VARCHAR(16) NOT NULL DEFAULT 'email'");
        $this->addSql('ALTER TABLE auth_codes ADD verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_codes ADD verified_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_codes ADD verified_user_agent VARCHAR(255) DEFAULT NULL');
        $this->addSql("ALTER TABLE auth_codes ADD provider VARCHAR(40) NOT NULL DEFAULT 'flash_dev'");
        $this->addSql('CREATE INDEX idx_auth_codes_phone_created ON auth_codes (phone, created_at)');
        $this->addSql('CREATE INDEX idx_auth_codes_channel_created ON auth_codes (channel, created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP INDEX idx_auth_codes_channel_created');
        $this->addSql('DROP INDEX idx_auth_codes_phone_created');
        $this->addSql('ALTER TABLE auth_codes DROP provider');
        $this->addSql('ALTER TABLE auth_codes DROP verified_user_agent');
        $this->addSql('ALTER TABLE auth_codes DROP verified_ip');
        $this->addSql('ALTER TABLE auth_codes DROP verified_at');
        $this->addSql('ALTER TABLE auth_codes DROP channel');
        $this->addSql('ALTER TABLE auth_codes DROP phone');
    }
}
