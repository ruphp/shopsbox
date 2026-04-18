# Основа учета ресурсов и лимитов

## Что сделано

- Добавлен foundation-модуль `ResourceUsage`.
- Добавлены Doctrine entities:
  - `ResourceUsageDaily`;
  - `StoreUsageLimit`.
- Добавлена миграция `DoctrineMigrations\Version20260418105000`.
- Добавлены таблицы:
  - `resource_usage_daily`;
  - `store_usage_limits`.
- Добавлены индексы по tenant/store/date/resource type.
- Добавлены связи с `tenants` и `stores`.
- Обновлена Doctrine mapping-конфигурация.
- Добавлена документация `docs/development/06-resource-usage-foundation.md`.
- Обновлены `CONTEXT.md` и `docs/workflow/02-current-work-status.md`.

## Архитектурное решение

Это не billing engine.

На этом шаге заложены только read/write models для будущего учета ресурсов:

- storage bytes;
- file count;
- backup storage;
- API requests;
- storefront requests;
- static egress;
- products/orders count;
- integration calls позже.

`Domain` и `Application` для `ResourceUsage` пока не создаются, потому что нет самостоятельных бизнес-правил тарификации, списаний, предупреждений или блокировок.

Когда появятся правила вроде soft/hard limit, предупреждений владельцу магазина или блокировки загрузки новых файлов, тогда добавим application use cases и unit-тесты на эти правила.

## Проверки

- `make migrate` - успешно, применена миграция `DoctrineMigrations\Version20260418105000`.
- `make backend-check` - успешно.
- `make test` - успешно, 11 tests / 32 assertions.

## Тестовое покрытие

Новые unit-тесты не добавлены, потому что в задаче нет новой domain/application логики.

Покрытие текущего шага - миграция и Doctrine mapping. Когда появятся use cases записи usage metrics или проверки лимитов, тогда нужны unit-тесты на ветки soft/hard limit и integration/functional checks на запись метрик в БД.

Closes #5
