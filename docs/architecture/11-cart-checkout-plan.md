# Корзина и checkout

Issue #30 фиксирует план перед реализацией корзины и оформления заказа.

## Цель

Подготовить модель, которая учитывает:

- товары и вариации;
- guest/customer сценарии;
- цену на момент оформления;
- доступность товара;
- tenant/store boundary;
- будущие оплаты и доставки.

Платежи в этот этап не входят.

## Модули

Корзина и заказы должны быть отдельной доменной областью, например `Order` или `Checkout`, но создавать модуль нужно только в момент реализации.

Каталог отвечает за описание товара и вариаций. Checkout не должен менять товар напрямую.

## Cart

Корзина - временное состояние выбора покупателя.

Минимальные сущности:

- `carts`;
- `cart_items`.

Поля `carts`:

- `id`;
- `tenant_id`;
- `store_id`;
- `customer_id` nullable;
- `session_key` nullable;
- `status`: `active`, `converted`, `expired`;
- timestamps.

Поля `cart_items`:

- `id`;
- `cart_id`;
- `product_id`;
- `variant_id` nullable;
- `quantity`;
- snapshot названия товара;
- snapshot SKU;
- snapshot цены, когда цена появится;
- timestamps.

## Guest и customer

MVP-сценарий:

- гость получает cart по cookie/session key;
- после входа или оформления cart можно привязать к customer;
- cart всегда ограничен одним store;
- нельзя смешивать товары разных магазинов.

Если покупатель открывает другой магазин, это другая корзина.

## Вариации товара

Если товар имеет вариации, item должен ссылаться на `product_variant`.

Правило:

- товар без вариаций можно добавить без `variant_id`;
- товар с вариациями должен добавляться только с выбранной вариацией;
- inactive variant нельзя добавить в корзину;
- draft/archived product нельзя добавить в корзину.

Эти правила стоит держать в доменном/Application слое и покрыть unit-тестами.

## Цена и доступность

Цена на момент оформления должна фиксироваться snapshot-ом.

На MVP можно начать без полноценной цены, но модель должна быть готова:

- `unit_price_amount`;
- `currency`;
- `price_snapshot_source`;
- `price_snapshot_at`.

Перед созданием order checkout должен повторно проверить:

- товар активен;
- вариация активна;
- товар относится к тому же store;
- количество допустимо;
- цена пересчитана или подтверждена.

## Order

Order - зафиксированный результат checkout.

Минимальные сущности:

- `orders`;
- `order_items`;
- позже `order_events`.

Поля `orders`:

- `id`;
- `tenant_id`;
- `store_id`;
- `customer_id` nullable;
- `number`;
- `status`: `draft`, `placed`, `paid`, `cancelled`, `fulfilled`;
- customer contact snapshot;
- total amount/currency;
- timestamps.

Поля `order_items`:

- `id`;
- `order_id`;
- `product_id`;
- `variant_id` nullable;
- snapshot name/SKU/options;
- quantity;
- unit price;
- line total.

## Checkout flow

Первый flow:

1. Покупатель добавляет товар в cart.
2. Покупатель открывает cart.
3. Checkout проверяет cart и собирает контактные данные.
4. Checkout создает order со snapshot-ами.
5. Cart получает статус `converted`.
6. Покупатель видит страницу успешного заказа.

Оплата подключается позже отдельным issue.

## Статусы

Cart:

- `active`;
- `converted`;
- `expired`.

Order:

- `draft` - техническая подготовка;
- `placed` - заказ оформлен;
- `paid` - оплата подтверждена;
- `cancelled` - заказ отменен;
- `fulfilled` - заказ выполнен.

Переходы статусов должны быть явными и покрыты unit-тестами, когда появится код.

## Contracts

Application contracts будущего модуля:

- `CartRepository`;
- `OrderRepository`;
- `ProductAvailabilityChecker`;
- `PriceCalculator`;
- `OrderNumberGenerator`;
- позже `PaymentGateway` и `NotificationSender`.

Infrastructure реализует Doctrine, платежные провайдеры, email/SMS и другие внешние действия.

## Что не делать в первой реализации

- платежи;
- доставка;
- промокоды;
- сложные налоги;
- мультивалютные пересчеты;
- split order по складам;
- abandoned cart automations.

## Следующие issues

1. Основа корзины: cart/cart_items и добавление товара.
2. Страница корзины.
3. Checkout без оплаты.
4. Создание order.
5. Админка заказов.
6. Подключение оплаты.

## Проверки и тестовое покрытие

Для этого issue новые тесты не нужны: это архитектурный документ.

При реализации нужны:

- unit-тесты правил добавления товара и выбора вариации;
- unit-тесты переходов статусов cart/order;
- integration-тесты repository и tenant/store boundary;
- functional-тесты add-to-cart, cart page, checkout;
- миграционные проверки;
- smoke-check публичной витрины после добавления cart UI.
