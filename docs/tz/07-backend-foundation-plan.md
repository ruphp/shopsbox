# Backend foundation: план первого технического шага

Документ фиксирует, что именно нужно сделать перед созданием первого кода приложения. Он нужен, чтобы переход от ТЗ к Symfony skeleton был понятным и проверяемым.

## Статус

Папка `D:\codex\shopsbox` после переноса проверена. Git-репозитория и кода приложения пока нет. Symfony skeleton, Docker Compose контур, миграции и `src/` еще не созданы.

Перед созданием кода нужно отдельно подтвердить старт технической реализации. Удаленные действия GitHub не выполнять без отдельного прямого подтверждения.

## Принятые решения для MVP

- Backend: PHP + Symfony.
- Runtime разработки: Docker Compose.
- База данных: PostgreSQL.
- Миграции: Doctrine Migrations.
- ORM: Doctrine ORM для обычных CRUD-сценариев.
- Быстрые и сложные запросы: Doctrine DBAL/native SQL внутри infrastructure-слоя, когда это реально нужно.
- Web MVP: Twig + Bootstrap, точечная интерактивность через Stimulus или простой JavaScript.
- Multi-tenancy: shared app + shared DB + `tenant_id` / `store_id`.
- Первый режим размещения: Managed Store.
- Own Infra: держать в архитектуре как будущий расширенный сценарий через защищенный API/agent.

## Что создать первым

1. Git-репозиторий в `D:\codex\shopsbox`, если владелец проекта подтвердит старт локальной разработки.
2. Task-ветку для этапа `01 MVP Foundation`, когда git уже будет инициализирован.
3. Symfony skeleton как backend-приложение.
4. Docker Compose контур для локального запуска.
5. Make-команды для запуска и проверки контура.
6. PostgreSQL service и конфигурацию подключения backend.
7. Doctrine Migrations.
8. Минимальную структуру модулей и слоев.
9. Первые миграции для `tenants`, `stores`, `users`, `roles`, `user_roles`.
10. Demo tenant/store/users для локальной разработки.
11. Базовые healthchecks для локального контура.

## Стартовая структура backend

```text
src/
  Tenant/
    Domain/
    Application/
    Infrastructure/
    Interface/
  Catalog/
    Domain/
    Application/
    Infrastructure/
    Interface/
  Order/
    Domain/
    Application/
    Infrastructure/
    Interface/
  Shared/
    Domain/
    Application/
    Infrastructure/
    Interface/
```

`Tenant` нужен первым, потому что он задает границу данных для магазинов, пользователей и ролей. `Catalog` и `Order` можно создать пустыми каркасами позже, когда начнется соответствующий этап, если Symfony skeleton проще держать минимальным.

## Минимальный Docker Compose контур

На первом шаге достаточно:

- `backend` - Symfony/PHP runtime;
- `web` - web server перед backend, если выбран отдельный nginx/apache контейнер;
- `postgres` - основная БД;
- `redis` - cache/session/locks и будущий transport очередей, можно подключить сразу или оставить как следующий шаг;
- `minio` - локальный S3-compatible storage для файлов и изображений, можно подключить сразу или оставить как следующий шаг, но storage interface заложить сразу;
- `worker` - Symfony Messenger worker, можно отложить до появления фоновых задач.
- `cron` / `scheduler-runner` - отдельный runner для регулярных задач, можно отложить, но не делать регулярные задачи случайными cron-записями на VPS.
- `backup` - на раннем dev-этапе не нужен как отдельный сервис; перед production-пилотом добавить make-команды/регламент для dump/restore и затем автоматизировать через scheduler.

Если отдельный `web`, `redis`, `minio` или `worker` усложняют первый запуск, допустимо начать с `backend + postgres`, но Makefile должен явно показывать, что это временный MVP-контур до добавления storage, очередей и healthchecks.

Первый этап - локальная разработка в Docker Compose. Мы не деплоим проект на VDS/VPS сразу, а моделируем будущий серверный контур локально: `backend + postgres + redis + minio` и позже `worker + scheduler-runner`. Для будущего production-пилота допустима схема `VPS/VDS или cloud VM + Docker Compose + backend + PostgreSQL`, но код не должен зависеть от одной машины. Файлы нужно проектировать через storage-абстракцию под local/S3-compatible storage, публичные URL - через отдельный builder/config. CDN не является критичным стартовым требованием для Twig + Bootstrap MVP; его стоит рассматривать как подключаемый слой для изображений и статики витрины, когда появится трафик.

Docker Compose project, containers, volumes and networks должны использовать префикс `shopsbox_`, чтобы локальные ресурсы не смешивались с другими проектами.

Demo tenant/store/users описаны в [Demo seed data](../development/02-demo-seed-data.md). Пароли оттуда являются dev-only и не должны использоваться в production.

## Make-команды

Первый технический PR должен расширить `Makefile` минимум такими командами:

- `make up` - поднять локальный контур.
- `make down` - остановить локальный контур.
- `make logs` - показать логи.
- `make backend-shell` - открыть shell backend-контейнера.
- `make migrate` - применить миграции.
- `make test` - запустить доступные проверки.

Прямые команды `docker compose`, `php`, `composer` и `symfony` вручную не использовать, если для операции можно добавить make-обертку.

## Первые GitHub issues

Перед полноценной реализацией стоит создать issues в milestone `01 MVP Foundation`:

- `Backend foundation: Symfony skeleton and Docker Compose`.
- `Tenant foundation: tenants, stores, users and roles`.
- `Local development: Make commands and healthchecks`.

Создание issues через GitHub CLI делать только через `make`-команды и UTF-8 файлы с текстом задачи. Если GitHub еще не подключен, эти задачи остаются в backlog документации.

## Критерии готовности этапа

- Проект запускается через `make up`.
- `make down` останавливает локальный контур.
- `make test` выполняет хотя бы документационные и базовые backend-проверки.
- Миграции создают первые таблицы foundation-слоя.
- В коде есть понятная структура слоев, но без лишних интерфейсов "на будущее".
- В документации объяснено, как запрос проходит через Symfony, application-слой, domain и infrastructure.

## Что не делать в этом шаге

- Не делать Kubernetes.
- Не делать React SPA.
- Не делать marketplace расширений.
- Не делать полный каталог и заказы.
- Не подключать платежные системы.
- Не усложнять multi-tenancy отдельными DB/schema до реальной необходимости.
- Не делать удаленные GitHub-действия без отдельного подтверждения владельца проекта.
