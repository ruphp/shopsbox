# 1С-совместимое расширение каталога

Issue #47 фиксирует следующий шаг модели каталога после базовых изображений и медиатеки.

## Главный принцип

`products` нельзя превращать в плоскую таблицу со всеми временными полями.

Не добавляем напрямую в `products`:

- цену;
- остаток;
- склад;
- внешний GUID;
- единицу измерения строкой;
- 1С-специфичные поля без слоя external ids.

Каталог должен разделять карточку товара, продаваемую единицу, цену, остаток и внешнюю идентичность.

## Целевая модель

Минимальные сущности следующего расширения:

- `products` - общая карточка товара;
- `product_variants` - продаваемая единица/SKU;
- `price_types` - типы цен;
- `product_prices` - регистр цен;
- `stock_locations` - склады;
- `inventory_levels` - остатки по складам;
- `external_entity_links` - связь с 1С и будущими интеграциями.

У товара без размера/цвета все равно должен быть один вариант. Это упрощает цены, остатки, корзину и 1С-синхронизацию.

## External ids

Внешние идентификаторы нужно хранить отдельно.

`external_entity_links`:

- `id`;
- `tenant_id`;
- `store_id`;
- `source_code`, например `1c`, `moysklad`, `manual_import`;
- `entity_type`, например `product`, `product_variant`, `price_type`, `stock_location`, `unit`;
- `entity_id`;
- `external_id`;
- `external_code` nullable;
- `external_payload_hash` nullable;
- `synced_at`;
- unique `(store_id, source_code, entity_type, external_id)`.

Внутренний UUID ShopsBox остается главным ключом. GUID 1С не должен становиться primary key.

## Product

`products` хранит:

- название;
- slug;
- описание;
- статус;
- категорию;
- признак `has_variants`;
- базовую единицу измерения как ссылку на справочник, когда он появится.

`products` не хранит остаток и цену как бизнес-истину.

## Variant / SKU

`product_variants` хранит:

- `product_id`;
- `sku`;
- `barcode` nullable;
- `name`;
- `status`;
- `position`;
- набор option values;
- служебный price adjustment только как временный MVP-мост, пока нет регистра цен.

После появления `product_prices` поле `priceAdjustment` не должно развиваться в полноценную цену.

## Prices

Цены нужно вынести в регистр.

`price_types`:

- `id`;
- `tenant_id`;
- `store_id`;
- `code`;
- `name`;
- `currency`;
- `is_default`.

`product_prices`:

- `id`;
- `tenant_id`;
- `store_id`;
- `product_id`;
- `variant_id`;
- `price_type_id`;
- `amount`;
- `currency`;
- `starts_at`;
- `ends_at` nullable;
- `updated_at`.

Для MVP достаточно одного типа цены `retail`, но модель не должна ломать будущие оптовые цены и интеграции.

## Inventory

Остатки нужно хранить отдельно от товара.

`stock_locations`:

- `id`;
- `tenant_id`;
- `store_id`;
- `name`;
- `code`;
- `active`.

`inventory_levels`:

- `id`;
- `tenant_id`;
- `store_id`;
- `variant_id`;
- `stock_location_id`;
- `available_quantity`;
- `reserved_quantity`;
- `incoming_quantity`;
- `synced_at` nullable.

Корзина и checkout должны читать доступность через отдельный контракт, а не напрямую из `products`.

## Порядок внедрения

После базовых изображений и медиатеки:

1. Добавить обязательный default variant для каждого товара.
2. Добавить `external_entity_links`.
3. Добавить `price_types` и `product_prices`.
4. Добавить `stock_locations` и `inventory_levels`.
5. Перевести витрину и checkout на чтение цены/остатка из новых read models.
6. Только после этого начинать 1С-import/export.

## Что не делать в MVP

- не копировать всю модель 1С;
- не делать 1С GUID главным ключом;
- не хранить цену и остаток в `products`;
- не смешивать импорт 1С с ручным CRUD без журнала обмена;
- не делать сложные комплекты и спецификации раньше цен/остатков.

## Проверки и тестовое покрытие

На этом этапе тесты не нужны: это архитектурный документ.

При реализации нужны:

- unit-тест: товар с вариациями требует выбранный variant;
- unit-тест: товар без вариаций имеет default variant;
- integration-тест unique external id в рамках store/source/type;
- integration-тест цена выбирается из активного price type;
- integration-тест остаток считается по variant и складу;
- functional smoke-check витрины после перевода на регистр цен.
