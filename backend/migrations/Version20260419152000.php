<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419152000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add editable public store settings.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('ALTER TABLE stores ADD public_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD contact_email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD contact_phone VARCHAR(40) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.',
        );

        $this->addSql('ALTER TABLE stores DROP public_description');
        $this->addSql('ALTER TABLE stores DROP contact_email');
        $this->addSql('ALTER TABLE stores DROP contact_phone');
    }
}
