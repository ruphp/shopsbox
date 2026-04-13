# Черновики issues для 01 MVP Foundation

Документ хранит локальные черновики GitHub Issues. Они еще не созданы в GitHub. Создавать удаленные issues можно только отдельной командой через `make` и UTF-8 файлы, когда владелец проекта прямо подтвердит удаленное действие.

## Issue 1: Backend foundation: Symfony skeleton and local Docker

Labels:

- `type: feature`
- `area: backend`
- `area: infra`
- `priority: p1`

Milestone:

- `01 MVP Foundation`

Цель:

Создать локальный backend foundation для ShopsBox: Symfony skeleton и Docker Compose контур для разработки.

Требования:

- Создать Symfony backend skeleton.
- Настроить локальный Docker Compose.
- Сервисы должны использовать префикс `shopsbox_` для project/container/volume/network имен.
- Минимальный локальный контур: backend/web, PostgreSQL, Redis, MinIO.
- Worker и scheduler-runner можно добавить сразу или оставить как явные placeholders.
- Все проектные операции запускать через `make`.
- Не использовать прямые `docker compose`, `php`, `composer` вручную без make-обертки.

Критерии приемки:

- `make up` поднимает локальный контур.
- `make down` останавливает локальный контур.
- `make logs` показывает логи.
- `make backend-shell` открывает shell backend-контейнера.
- Документация обновлена, если фактическая схема отличается от плана.

## Issue 2: Tenant foundation: tenants, stores, users and roles

Labels:

- `type: feature`
- `area: backend`
- `area: security`
- `priority: p1`

Milestone:

- `01 MVP Foundation`

Цель:

Создать базовый foundation-слой multi-tenant модели: tenants, stores, users, roles и user_roles.

Требования:

- Создать миграции для `tenants`, `stores`, `users`, `roles`, `user_roles`.
- Учесть `tenant_id` / `store_id` boundaries.
- Добавить seed-данные для локального demo tenant/store/users.
- Использовать роли из `docs/tz/09-roles-and-permissions.md`.
- Не делать enterprise ACL и UI-конструктор прав на этом этапе.

Критерии приемки:

- Миграции применяются через `make migrate`.
- Demo tenant и demo store создаются сидером или fixture.
- Demo users создаются только для локальной разработки.
- Пароли не используются как production-секреты.

## Issue 3: Storage foundation: files via local/S3-compatible abstraction

Labels:

- `type: feature`
- `area: backend`
- `area: infra`
- `priority: p2`

Milestone:

- `01 MVP Foundation`

Цель:

Заложить работу с файлами так, чтобы позже перейти с local storage на S3-compatible storage без переписывания каталога и витрины.

Требования:

- Добавить модель/таблицу `files`, если она нужна для первого storage-слоя.
- Работать с файлами через storage interface.
- Публичные URL строить через отдельный URL builder/config.
- В локальном Docker предусмотреть MinIO или явный placeholder под MinIO.
- Не хранить пользовательские файлы внутри контейнера приложения.

Критерии приемки:

- Код не зависит от локального пути VPS.
- Можно переключить storage adapter конфигурацией.
- Документация описывает local и S3-compatible режимы.

## Issue 4: Local operations: scheduler, backups placeholders and healthchecks

Labels:

- `type: feature`
- `area: infra`
- `area: backend`
- `priority: p2`

Milestone:

- `01 MVP Foundation`

Цель:

Подготовить операционный каркас без production-overkill на раннем dev-этапе.

Требования:

- Добавить healthcheck endpoint/команду для локального контура.
- Описать scheduler-runner как будущий отдельный сервис для регулярных задач.
- Не поднимать сложный production backup-service на dev-этапе.
- Перед production-пилотом предусмотреть make-команды или регламент для `dump/restore`.

Критерии приемки:

- Есть простой healthcheck.
- В документации понятно, какие операции dev-only, а какие нужны перед production-пилотом.
- Backup не забыт, но не усложняет ранний dev.

## Issue 5: Usage and limits groundwork

Labels:

- `type: feature`
- `area: backend`
- `area: billing`
- `priority: p2`

Milestone:

- `01 MVP Foundation`

Цель:

Заложить будущий учет ресурсов по tenant/store без полноценного billing engine.

Требования:

- Подготовить модель или backlog для `resource_usage_daily` и `store_usage_limits`.
- Логировать tenant/store context для значимых действий.
- Не показывать клиенту технические CPU/DB лимиты на MVP.
- Подготовить основу для будущих метрик: storage, API requests, static egress, backups.

Критерии приемки:

- В коде и документации понятно, как потом считать usage по store/tenant.
- Нет обещания безлимитного shared-контура.
