<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20261013000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronize products shown as published in the owner cabinet with storefront publication state.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');
        $this->addSql("UPDATE products SET publication_status = 'published', publication_reviewed_at = NOW(), publication_review_reason = NULL, updated_at = NOW() WHERE status = 'active' AND publication_status = 'draft'");
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Previously published products cannot be distinguished from synchronized products safely.');
    }
}
