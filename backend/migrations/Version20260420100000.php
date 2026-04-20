<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product publication moderation fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE products ADD publication_status VARCHAR(32) NOT NULL DEFAULT 'draft'");
        $this->addSql('ALTER TABLE products ADD publication_submitted_by VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD publication_submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD publication_reviewed_by VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD publication_reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD publication_review_reason TEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_products_publication_status ON products (publication_status)');
        $this->addSql("UPDATE products SET publication_status = 'published' WHERE status = 'active'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_products_publication_status');
        $this->addSql('ALTER TABLE products DROP publication_status');
        $this->addSql('ALTER TABLE products DROP publication_submitted_by');
        $this->addSql('ALTER TABLE products DROP publication_submitted_at');
        $this->addSql('ALTER TABLE products DROP publication_reviewed_by');
        $this->addSql('ALTER TABLE products DROP publication_reviewed_at');
        $this->addSql('ALTER TABLE products DROP publication_review_reason');
    }
}
