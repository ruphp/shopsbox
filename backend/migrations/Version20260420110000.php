<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add store publication request fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stores ADD publication_owner_name VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD publication_email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD publication_phone VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE stores ADD public_subdomain VARCHAR(40) DEFAULT NULL');
        $this->addSql("ALTER TABLE stores ADD publication_status VARCHAR(32) NOT NULL DEFAULT 'draft'");
        $this->addSql('ALTER TABLE stores ADD publication_terms_accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_stores_public_subdomain ON stores (public_subdomain) WHERE public_subdomain IS NOT NULL');
        $this->addSql('CREATE INDEX idx_stores_publication_status ON stores (publication_status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_stores_public_subdomain');
        $this->addSql('DROP INDEX idx_stores_publication_status');
        $this->addSql('ALTER TABLE stores DROP publication_owner_name');
        $this->addSql('ALTER TABLE stores DROP publication_email');
        $this->addSql('ALTER TABLE stores DROP publication_phone');
        $this->addSql('ALTER TABLE stores DROP public_subdomain');
        $this->addSql('ALTER TABLE stores DROP publication_status');
        $this->addSql('ALTER TABLE stores DROP publication_terms_accepted_at');
    }
}
