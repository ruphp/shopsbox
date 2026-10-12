<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20261012123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronize all public demo catalog data with the Russian fixtures.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');

        $this->addSql("DELETE FROM products WHERE id IN ('66666666-0001-4666-8666-666666666666', '66666666-0002-4666-8666-666666666666', '66666666-0003-4666-8666-666666666666')");
        $this->addSql("DELETE FROM categories WHERE id = '55555555-0001-4555-8555-555555555555'");

        $this->addSql("UPDATE categories SET name = 'Одежда', updated_at = NOW() WHERE id = '55555555-0101-4555-8555-555555555555'");
        $this->addSql("UPDATE categories SET name = 'Дом и уют', updated_at = NOW() WHERE id = '55555555-0102-4555-8555-555555555555'");
        $this->addSql("UPDATE categories SET name = 'Цифровые товары', updated_at = NOW() WHERE id = '55555555-0103-4555-8555-555555555555'");
        $this->addSql("UPDATE categories SET name = 'Архив демо', updated_at = NOW() WHERE id = '55555555-0104-4555-8555-555555555555'");

        $this->addSql("UPDATE products SET name = 'Базовое худи ShopsBox', description = 'Базовое худи для демонстрации товара с размером, цветом и разницей цены у вариаций.', price = 4990.00, updated_at = NOW() WHERE id = '66666666-0101-4666-8666-666666666666'");
        $this->addSql("UPDATE products SET name = 'Кружка для витрины', description = 'Простой активный товар без вариаций: нужен как контрольный пример обычной карточки.', price = 790.00, updated_at = NOW() WHERE id = '66666666-0102-4666-8666-666666666666'");
        $this->addSql("UPDATE products SET name = 'Стартовый набор магазина', description = 'Товар с опцией комплектации: базовая и подарочная версия.', price = 2490.00, updated_at = NOW() WHERE id = '66666666-0103-4666-8666-666666666666'");
        $this->addSql("UPDATE products SET name = 'Гайд по запуску магазина', description = 'Цифровой товар без физических опций. Показывает, что каталог не ограничен одеждой.', price = 990.00, updated_at = NOW() WHERE id = '66666666-0104-4666-8666-666666666666'");
        $this->addSql("UPDATE products SET name = 'Черновик набора баннеров', description = 'Черновой пример: должен быть в данных, но не должен попадать на публичную витрину.', price = 1500.00, updated_at = NOW() WHERE id = '66666666-0105-4666-8666-666666666666'");
        $this->addSql("UPDATE products SET name = 'Архивный постер', description = 'Архивный пример: нужен для проверки статусов и будущей админки.', price = 500.00, updated_at = NOW() WHERE id = '66666666-0106-4666-8666-666666666666'");

        $this->addSql("UPDATE product_option_groups SET name = 'Размер' WHERE id = '77777777-0101-4777-8777-777777777777'");
        $this->addSql("UPDATE product_option_groups SET name = 'Цвет' WHERE id = '77777777-0102-4777-8777-777777777777'");
        $this->addSql("UPDATE product_option_groups SET name = 'Комплектация' WHERE id = '77777777-0103-4777-8777-777777777777'");

        $this->addSql("UPDATE product_option_values SET value = 'Графит' WHERE id = '77777777-0203-4777-8777-777777777777'");
        $this->addSql("UPDATE product_option_values SET value = 'Молочный' WHERE id = '77777777-0204-4777-8777-777777777777'");
        $this->addSql("UPDATE product_option_values SET value = 'Базовая' WHERE id = '77777777-0205-4777-8777-777777777777'");
        $this->addSql("UPDATE product_option_values SET value = 'Подарочная' WHERE id = '77777777-0206-4777-8777-777777777777'");

        $this->addSql("UPDATE product_attributes SET name = 'Материал', value = 'Хлопок 80%, полиэстер 20%' WHERE id = '99999999-0101-4999-8999-999999999999'");
        $this->addSql("UPDATE product_attributes SET name = 'Уход', value = 'Деликатная стирка при 30 градусах' WHERE id = '99999999-0102-4999-8999-999999999999'");
        $this->addSql("UPDATE product_attributes SET name = 'Состав набора', value = 'Коробка, открытка, демо-упаковка и инструкция' WHERE id = '99999999-0103-4999-8999-999999999999'");
        $this->addSql("UPDATE product_attributes SET name = 'Формат', value = 'PDF, 24 страницы' WHERE id = '99999999-0104-4999-8999-999999999999'");

        $this->addSql("UPDATE product_variants SET name = 'M / графит' WHERE id = '77777777-0301-4777-8777-777777777777'");
        $this->addSql("UPDATE product_variants SET name = 'L / графит' WHERE id = '77777777-0302-4777-8777-777777777777'");
        $this->addSql("UPDATE product_variants SET name = 'M / молочный' WHERE id = '77777777-0303-4777-8777-777777777777'");
        $this->addSql("UPDATE product_variants SET name = 'Базовая комплектация' WHERE id = '77777777-0304-4777-8777-777777777777'");
        $this->addSql("UPDATE product_variants SET name = 'Подарочная комплектация' WHERE id = '77777777-0305-4777-8777-777777777777'");
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('The removed legacy demo records cannot be restored safely.');
    }
}
