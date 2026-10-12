<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012122000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Localize the demo storefront and add local product gallery images.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');

        $this->addSql("UPDATE tenants SET name = 'Демонстрационный проект' WHERE id = '11111111-1111-4111-8111-111111111111'");
        $this->addSql("UPDATE stores SET name = 'Демо Витрина' WHERE id = '22222222-2222-4222-8222-222222222222'");
        $this->addSql("UPDATE products SET name = 'Базовое худи ShopsBox' WHERE id = '66666666-0101-4666-8666-666666666666'");
        $this->addSql("UPDATE products SET description = 'Черновой пример: должен быть в данных, но не должен попадать на публичную витрину.' WHERE id = '66666666-0105-4666-8666-666666666666'");
        $this->addSql("UPDATE products SET description = 'Архивный пример: нужен для проверки статусов и будущей админки.' WHERE id = '66666666-0106-4666-8666-666666666666'");

        $images = [
            ['88888888-0101-4888-8888-888888888888', '66666666-0101-4666-8666-666666666666', 'demo/hoodie-1.jpg', '/assets/demo/hoodie-1.jpg', 122858, true, 10],
            ['88888888-0201-4888-8888-888888888888', '66666666-0101-4666-8666-666666666666', 'demo/hoodie-2.jpg', '/assets/demo/hoodie-2.jpg', 118125, false, 20],
            ['88888888-0102-4888-8888-888888888888', '66666666-0102-4666-8666-666666666666', 'demo/mug-1.jpg', '/assets/demo/mug-1.jpg', 97942, true, 10],
            ['88888888-0202-4888-8888-888888888888', '66666666-0102-4666-8666-666666666666', 'demo/mug-2.jpg', '/assets/demo/mug-2.jpg', 49646, false, 20],
            ['88888888-0103-4888-8888-888888888888', '66666666-0103-4666-8666-666666666666', 'demo/starter-kit-1.jpg', '/assets/demo/starter-kit-1.jpg', 173112, true, 10],
            ['88888888-0203-4888-8888-888888888888', '66666666-0103-4666-8666-666666666666', 'demo/starter-kit-2.jpg', '/assets/demo/starter-kit-2.jpg', 161324, false, 20],
            ['88888888-0104-4888-8888-888888888888', '66666666-0104-4666-8666-666666666666', 'demo/launch-guide-1.jpg', '/assets/demo/launch-guide-1.jpg', 109909, true, 10],
            ['88888888-0204-4888-8888-888888888888', '66666666-0104-4666-8666-666666666666', 'demo/launch-guide-2.jpg', '/assets/demo/launch-guide-2.jpg', 112538, false, 20],
        ];

        foreach ($images as [$id, $productId, $key, $url, $size, $primary, $position]) {
            $this->addSql(
                sprintf(
                    'INSERT INTO product_images (id, product_id, storage_key, public_url, mime_type, size, created_at, primary_image, position, deleted_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), %s, ?, NULL) ON CONFLICT (id) DO UPDATE SET storage_key = EXCLUDED.storage_key, public_url = EXCLUDED.public_url, mime_type = EXCLUDED.mime_type, size = EXCLUDED.size, primary_image = EXCLUDED.primary_image, position = EXCLUDED.position, deleted_at = NULL',
                    $primary ? 'true' : 'false',
                ),
                [$id, $productId, $key, $url, 'image/jpeg', $size, $position],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');

        $this->addSql("DELETE FROM product_images WHERE id IN ('88888888-0201-4888-8888-888888888888', '88888888-0202-4888-8888-888888888888', '88888888-0203-4888-8888-888888888888', '88888888-0204-4888-8888-888888888888')");
        $this->addSql("UPDATE product_images SET storage_key = 'demo-catalog/hoodie.jpg', public_url = 'https://picsum.photos/seed/shopsbox-hoodie/900/700', size = 120000, position = 0 WHERE id = '88888888-0101-4888-8888-888888888888'");
        $this->addSql("UPDATE product_images SET storage_key = 'demo-catalog/mug.jpg', public_url = 'https://picsum.photos/seed/shopsbox-mug/900/700', size = 120000, position = 0 WHERE id = '88888888-0102-4888-8888-888888888888'");
        $this->addSql("UPDATE product_images SET storage_key = 'demo-catalog/starter-kit.jpg', public_url = 'https://picsum.photos/seed/shopsbox-kit/900/700', size = 120000, position = 0 WHERE id = '88888888-0103-4888-8888-888888888888'");
        $this->addSql("UPDATE product_images SET storage_key = 'demo-catalog/guide.jpg', public_url = 'https://picsum.photos/seed/shopsbox-guide/900/700', size = 120000, position = 0 WHERE id = '88888888-0104-4888-8888-888888888888'");
        $this->addSql("UPDATE products SET name = 'Худи ShopsBox Core' WHERE id = '66666666-0101-4666-8666-666666666666'");
        $this->addSql("UPDATE stores SET name = 'Demo Store' WHERE id = '22222222-2222-4222-8222-222222222222'");
        $this->addSql("UPDATE tenants SET name = 'Demo Tenant' WHERE id = '11111111-1111-4111-8111-111111111111'");
    }
}
