<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user phone verification metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('ALTER TABLE users ADD verified_phone VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD phone_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD phone_verified_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD phone_verified_user_agent VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD phone_verification_provider VARCHAR(40) DEFAULT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_users_verified_phone ON users (verified_phone)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('DROP INDEX IF EXISTS idx_users_verified_phone');
        $this->addSql('ALTER TABLE users DROP phone_verification_provider');
        $this->addSql('ALTER TABLE users DROP phone_verified_user_agent');
        $this->addSql('ALTER TABLE users DROP phone_verified_ip');
        $this->addSql('ALTER TABLE users DROP phone_verified_at');
        $this->addSql('ALTER TABLE users DROP verified_phone');
    }
}
