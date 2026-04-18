# Resource usage foundation

Документ фиксирует рабочее решение для задачи `#5 Основа учета ресурсов и лимитов`.

## Цель

Заложить минимальную основу учета ресурсов по tenant/store без полноценного billing engine.

ShopsBox не должен обещать безлимитный shared-контур. Даже на MVP нужно понимать, какие ресурсы потом считаются по магазину:

- файловое хранилище;
- количество файлов;
- backup storage;
- API requests;
- storefront requests;
- static egress;
- количество товаров и заказов;
- интеграционные вызовы позже.

## Архитектурное решение

Создана отдельная зона `ResourceUsage`.

Фактическая структура первого шага:

```text
backend/src/ResourceUsage/
  Infrastructure/
    Persistence/
      Doctrine/
        Entity/
```

`Domain` и `Application` пока не создаем, потому что сейчас нет самостоятельных бизнес-правил тарификации, списаний или блокировок. На этом шаге нужны только read/write models для будущего сбора метрик и лимитов.

Когда появятся правила вроде "при 80% лимита показать предупреждение" или "запретить загрузку нового файла после hard limit", тогда добавим application use cases и доменные правила.

## Таблицы

### resource_usage_daily

Дневная агрегация использования ресурса.

Поля:

- `id`;
- `tenant_id`;
- `store_id`, nullable для tenant-level метрик;
- `usage_date`;
- `resource_type`;
- `quantity`;
- `unit`;
- `source`;
- `created_at`.

Примеры `resource_type`:

- `file_storage_bytes`;
- `file_count`;
- `backup_storage_bytes`;
- `backup_restore_points`;
- `api_requests`;
- `api_errors`;
- `storefront_requests`;
- `static_egress_bytes`;
- `products_count`;
- `orders_count`;
- `integration_calls`.

`quantity` хранится как decimal, чтобы одной моделью покрыть bytes/count/milliseconds и будущие агрегаты.

### store_usage_limits

Лимиты магазина по типам ресурсов.

Поля:

- `id`;
- `store_id`;
- `plan_code`;
- `resource_type`;
- `soft_limit`;
- `hard_limit`;
- `unit`;
- `reset_period`;
- `created_at`;
- `updated_at`.

`soft_limit` нужен для предупреждения. `hard_limit` нужен только для дорогих или опасных ресурсов, например запрета загрузки новых файлов после превышения лимита storage.

Для критичных операций, например checkout, hard limit на старте не применяем: магазин не должен внезапно перестать принимать заказы из-за технической метрики.

## Tenant/store context

Значимые действия должны иметь контекст:

- `tenant_id`;
- `store_id`, если действие относится к конкретному магазину;
- источник метрики или операции.

На этом шаге не добавляем глобальный logger processor, потому что пока нет единого request tenant resolver и production logging stack. Правило фиксируется архитектурно: новые use cases и будущие middleware должны передавать tenant/store context в логи и usage metrics.

## Что не делаем сейчас

- Billing engine.
- Тарифные планы в коде.
- Списание денег.
- UI лимитов.
- Блокировку действий по hard limit.
- Сбор real-time метрик из middleware.
- Интеграцию с CDN/object storage usage API.

## Тестовое решение

Новые unit-тесты не нужны, потому что в задаче нет application/domain логики.

Проверки для этого шага:

- миграция применяется через `make migrate`;
- Doctrine mapping проходит через `make backend-check`;
- общий набор проверок проходит через `make test`.

Когда появятся use cases для записи usage metrics или проверки лимитов, тогда понадобятся unit-тесты на ветки:

- метрика записывается в правильный tenant/store;
- soft limit дает предупреждение;
- hard limit блокирует только разрешенные операции;
- неизвестный resource_type отклоняется или нормализуется выбранным правилом.

## Проверки текущей реализации

- `make migrate` - успешно, применена миграция `DoctrineMigrations\Version20260418105000`.
- `make backend-check` - успешно, Symfony container lint и Doctrine mapping проходят.
- `make test` - успешно, 11 tests / 32 assertions.
