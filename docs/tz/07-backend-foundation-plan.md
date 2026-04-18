# Backend foundation: план первого технического шага

Документ фиксирует, что именно нужно сделать перед созданием первого кода приложения. Он нужен, чтобы переход от ТЗ к Symfony skeleton был понятным и проверяемым.

## Статус

Папка `D:\codex\shopsbox` после переноса проверена. Git-репозиторий создан, репозиторий GitHub `ruphp/shopsbox` привязан, первый push в `master` уже выполнен по отдельному разрешению владельца проекта.

Symfony skeleton создан в `backend/`, локальный Docker Compose контур поднят, healthcheck `GET /health` отвечает `200 {"service":"shopsbox_backend","status":"ok"}`. Следующий технический шаг - Doctrine Migrations, foundation entities и demo seed/fixtures.

Удаленные действия GitHub дальше не выполнять без отдельного прямого подтверждения.

## Принятые решения для MVP

- Backend: PHP + Symfony.
- Runtime разработки: Docker Compose.
- База данных: PostgreSQL.
- Миграции: Doctrine Migrations.
- ORM: Doctrine ORM для обычных CRUD-сценариев.
- Быстрые и сложные запросы: Doctrine DBAL/native SQL внутри infrastructure-слоя, когда это реально нужно.
- Архитектура backend: модульный монолит, один Symfony backend без микросервисов на MVP.
- Структура модуля: `Domain`, `Application`, `Infrastructure`, `Presentation`.
- Web MVP: Twig + Bootstrap, точечная интерактивность через Stimulus или простой JavaScript.
- Multi-tenancy: shared app + shared DB + `tenant_id` / `store_id`.
- Первый режим размещения: Managed Store.
- Own Infra: держать в архитектуре как будущий расширенный сценарий через защищенный API/agent.

## Что создать первым

Уже создано:

1. Git-репозиторий в `D:\codex\shopsbox`.
2. Symfony skeleton как backend-приложение.
3. Docker Compose контур для локального запуска.
4. Make-команды для запуска и проверки контура.
5. PostgreSQL, Redis и MinIO services в локальном контуре.
6. Базовый healthcheck для backend.

Следующий шаг:

1. Doctrine Migrations.
2. Минимальная структура модулей и слоев.
3. Первые миграции для `tenants`, `stores`, `users`, `roles`, `user_roles`.
4. Demo tenant/store/users для локальной разработки.

## Стартовая структура backend

```text
src/
  Tenant/
    Domain/
    Application/
    Infrastructure/
    Presentation/
```

`Tenant` нужен первым, потому что он задает границу данных для арендаторов, магазинов, пользователей и ролей. `Catalog`, `Order`, `Billing` и другие модули не создаем заранее: они появятся только в своих задачах.

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

Текущий `Makefile` должен оставаться единой точкой входа для проектных операций. Минимальный набор команд:

- `make up` - поднять локальный контур.
- `make down` - остановить локальный контур.
- `make logs` - показать логи.
- `make backend-shell` - открыть shell backend-контейнера.
- `make migrate` - применить миграции.
- `make test` - запустить доступные проверки.

Прямые команды `docker compose`, `php`, `composer` и `symfony` вручную не использовать, если для операции можно добавить make-обертку.

## Первые GitHub issues

Issues в milestone `01 MVP Foundation` уже заведены:

- `#1 Основа бэкенда: каркас Symfony и локальный Docker-контур` - `Готово`.
- `#2 Основа арендаторов, магазинов, пользователей и ролей` - `Бэклог`.
- `#3 Основа файлов: локальное и S3-совместимое хранилище` - `Бэклог`.
- `#4 Локальные операции: планировщик, заготовки бэкапов и проверки здоровья` - `Бэклог`.
- `#5 Основа учета ресурсов и лимитов` - `Бэклог`.

Новые issues, PR, push, merge и перевод карточек в `Done` делать только после отдельного прямого подтверждения владельца проекта. GitHub CLI вызывать через `make`-команды и UTF-8 файлы с текстом задачи.

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
