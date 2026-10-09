<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the requester IP for authentication code rate limiting.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_codes ADD request_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_auth_codes_request_ip_created ON auth_codes (request_ip, created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_auth_codes_request_ip_created');
        $this->addSql('ALTER TABLE auth_codes DROP request_ip');
    }
}
