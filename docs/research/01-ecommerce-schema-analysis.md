# Анализ схем БД и моделей аналогов

Цель: взять хорошие публичные идеи из ecommerce-платформ и собрать свою модель данных для ShopsBox. Мы не копируем закрытые схемы и не переносим чужую структуру один-в-один. Берем паттерны, которые понятны, объяснимы и подходят нашему MVP.

## Источники

- [Shopify Admin API: InventoryLevel](https://shopify.dev/docs/api/admin-rest/latest/resources/inventorylevel) - ProductVariant, InventoryItem, InventoryLevel, Location.
- [WooCommerce HPOS](https://woocommerce.com/document/high-performance-order-storage/) - отдельные таблицы для заказов, адресов, операционных данных и метаданных.
- [Medusa Inventory Concepts](https://docs.medusajs.com/resources/commerce-modules/inventory/concepts) - InventoryItem, InventoryLevel, ReservationItem, StockLocation.
- [Saleor Documentation](https://docs.saleor.io/) - core concepts для products, checkout, channels, promotions, payments.
- [Sylius Products](https://docs.sylius.com/the-book/products) - Symfony ecommerce-подход: products, variants, options, channels, orders.

## Shopify

Что полезно:

- У товара есть варианты. Даже простой товар можно мыслить как товар с одним вариантом.
- ProductVariant связывает SKU, цену, штрихкод, изображение, доставку и налоги.
- InventoryItem отделяет физический складской предмет от витринного ProductVariant.
- InventoryLevel связывает InventoryItem с конкретной Location и количеством.
- Location нужна для склада, магазина, fulfillment-точки.

Плюсы:

- Хорошая модель для нескольких складов.
- Вариант товара становится понятной единицей продажи.
- Складская логика отделена от описания товара.

Минусы для нас:

- Полностью повторять ограничения Shopify не надо.
- Не стоит сразу вводить все поля для таможни, fulfillment services, bundles, selling plans.
- Shopify-модель может быть сложновата для первого CRUD, если сделать ее без объяснения.

Что берем:

- `product_variants` как обязательную сущность.
- `inventory_items` как складскую сущность.
- `stock_locations` и `inventory_levels`.
- Не храним склад только в `products`.

## WooCommerce

Что полезно:

- Старый подход WooCommerce через `posts/postmeta` показывает проблему универсальных таблиц: сложно искать, масштабировать и объяснять.
- HPOS ушел к отдельным таблицам заказов и адресов, чтобы ускорить чтение/запись и упростить понимание.

Плюсы:

- Заказы должны жить в отдельных специализированных таблицах.
- Адреса заказа лучше отделять от самого заказа.
- Метаданные полезны как расширение, но не как основа доменной модели.

Минусы для нас:

- Не копировать WordPress-подход с универсальными `postmeta`.
- Не превращать `metadata` JSON в свалку вместо нормальных полей.

Что берем:

- отдельные таблицы `orders`, `order_items`, `order_addresses`;
- `order_status_history` для истории;
- `order_metadata` можно добавить позже только для интеграций.

## Medusa

Что полезно:

- Модульность по доменам: Product, Inventory, Order и другие контуры.
- InventoryItem не обязан быть только ProductVariant. Его можно использовать для комплектов или будущих складских единиц.
- InventoryLevel хранит stocked/reserved/incoming quantities.
- ReservationItem фиксирует зарезервированное количество после оформления заказа или во время checkout.
- Sales channel влияет на доступность витрины и складские источники.

Плюсы:

- Хорошая складская модель на будущее.
- Можно поддержать несколько складов и резервирование.
- Есть путь к bundles/multi-part products, но без обязательной реализации сразу.

Минусы для нас:

- Не надо сразу строить всю модульную платформу Medusa.
- Не надо сразу делать сложные workflows и rollback engine.

Что берем:

- `inventory_levels.stocked_quantity`, `reserved_quantity`, `incoming_quantity`;
- `inventory_reservations`;
- отделение `sales_channels` как будущий контур для web/mobile/marketplace.

## Saleor

Что полезно:

- Core concepts явно разделяют products, checkout, channels, promotions, payments.
- Channels важны для multi-region, разных валют, витрин и способов продажи.
- Apps/integrations отделяются от ядра.

Плюсы:

- Хорошо ложится на будущие разные витрины и мобильные приложения.
- Channel можно использовать как связку "где продаем": web, mobile app, marketplace, конкретная клиентская витрина.

Минусы для нас:

- GraphQL и очень широкий extension-подход не нужны в MVP.
- Слишком ранняя multi-region модель усложнит старт.

Что берем:

- `sales_channels` как простую сущность.
- Привязку товара/варианта к каналам можно добавить после базового каталога.
- Payment integrations держать отдельным контуром, а не смешивать с заказом.

## Sylius

Что полезно:

- Это Symfony ecommerce-ориентир: products, variants, options, channels, orders.
- Product должен иметь хотя бы один variant, чтобы продаваться.
- ProductOption и ProductOptionValue помогают строить варианты по размеру, цвету, упаковке.
- Channel используется для цен и контекста продаж.

Плюсы:

- Близко к нашему стеку Symfony.
- Удобно объяснять: Product - описание, Variant - продаваемая версия, Option - признаки вариации.
- Хороший ориентир для модульного монолита.

Минусы для нас:

- Не надо копировать Sylius Resource Layer и все фабрики/менеджеры.
- Не надо сразу делать plugin marketplace и всю гибкость Sylius.

Что берем:

- `products`, `product_variants`, `product_options`, `product_option_values`;
- `variant_option_values`;
- идею channel-specific pricing, но для MVP можно начать с простой цены на variant.

## Наша итоговая модель для MVP

Минимальный, но нормальный ecommerce-core:

- Tenant/Store: `tenants`, `stores`, `users`, `roles`, `user_roles`.
- Channel: `sales_channels`.
- Catalog: `products`, `product_variants`, `product_options`, `product_option_values`, `variant_option_values`, `categories`, `product_categories`, `product_images`.
- Inventory: `inventory_items`, `stock_locations`, `inventory_levels`, `inventory_reservations`.
- Customer: `customers`, `customer_addresses`.
- Order: `carts`, `cart_items`, `orders`, `order_items`, `order_addresses`, `order_status_history`.
- Payment/Delivery: `payments`, `deliveries`.
- Ops: `files`, `audit_logs`, `backups`.

## Что сознательно упрощаем

- В MVP цена хранится на `product_variants`, а `variant_channel_prices` можно добавить позже.
- В MVP один магазин может иметь один основной sales channel `web`.
- В MVP `inventory_items` можно создавать 1:1 к `product_variants`, но схема не должна ломаться, если позже понадобится kit/shared inventory.
- В MVP не делаем GraphQL.
- В MVP не делаем marketplace vendors.
- В MVP не делаем сложную промо-систему.

## Простое объяснение архитектурного решения

> В проекте товар и склад разделены по ответственности. `Product` хранит описание товара, `ProductVariant` является продаваемой единицей с ценой и SKU, а `InventoryItem` и `InventoryLevel` отвечают за складской учет. Заказы вынесены в отдельные таблицы, чтобы их было проще искать, обновлять и расширять. `SalesChannel` оставлен как простой контур для будущих web/mobile-витрин, но в MVP не усложняет основную логику.

Для собеседования достаточно говорить про свою модель и принятые инженерные решения. Перечислять аналоги не нужно, если об этом прямо не спрашивают.
