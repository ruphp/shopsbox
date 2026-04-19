<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add storefront theme settings to stores.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE stores ADD theme_settings JSON NOT NULL DEFAULT '{}'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stores DROP theme_settings');
    }
}
