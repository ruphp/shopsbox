<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Keep the public demo store publication status in sync with its live storefront.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE stores SET publication_status = 'published', publication_reviewed_by = 'system:demo-migration', publication_reviewed_at = NOW(), publication_review_reason = NULL, updated_at = NOW() WHERE id = '22222222-2222-4222-8222-222222222222' OR slug = 'demo-store'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE stores SET publication_status = 'draft', publication_reviewed_by = NULL, publication_reviewed_at = NULL, publication_review_reason = NULL, updated_at = NOW() WHERE id = '22222222-2222-4222-8222-222222222222' OR slug = 'demo-store'");
    }
}
