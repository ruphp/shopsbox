# Текущий рабочий статус

Документ фиксирует текущую рабочую задачу и положение проекта на доске GitHub.

## Текущая карточка

- Название: `Основа файлов: локальное и S3-совместимое хранилище`
- Статус: `В работе локально`
- Локальная ветка: `task/03-file-storage-foundation`
- Этап: `01 Основа MVP`
- Удаленная задача: `https://github.com/ruphp/shopsbox/issues/3`
- Доска проекта: `https://github.com/users/ruphp/projects/2`
- Русский вид бэклога: `https://github.com/users/ruphp/projects/2/views/3`
- Защита ветки: заблокирована текущим тарифом GitHub для приватного репозитория; GitHub требует Pro или публичный репозиторий для защиты ветки (`branch protection`) и наборов правил (`rulesets`).

## Что заведено в GitHub

Репозиторий GitHub `ruphp/shopsbox` создан и привязан к локальному репозиторию.

Проект GitHub `ShopsBox` создан на уровне пользователя `ruphp` и привязан к репозиторию `ruphp/shopsbox`. Он доступен по ссылке `https://github.com/users/ruphp/projects/2`.

На доске создан вид `Бэклог` с раскладкой доски (`board`), как в Freepainters. GitHub API создает вид-доску, но не принимает параметр явной группировки по полю `Статус`; если GitHub UI не сгруппирует колонки автоматически, нужно один раз выставить в интерфейсе `Group by -> Status`.

В доске заведены статусы как в Freepainters:

- `Бэклог`;
- `Готово к работе`;
- `В работе`;
- `На проверке`;
- `Готово`.

Созданы задачи для этапа `01 Основа MVP`:

- `#1 Основа бэкенда: каркас Symfony и локальный Docker-контур` - `Готово`;
- `#2 Основа арендаторов, магазинов, пользователей и ролей` - `Готово`, PR `#8` смержен;
- `#3 Основа файлов: локальное и S3-совместимое хранилище` - `В работе локально` в ветке `task/03-file-storage-foundation`;
- `#4 Локальные операции: планировщик, заготовки бэкапов и проверки здоровья` - `Бэклог`;
- `#5 Основа учета ресурсов и лимитов` - `Бэклог`.

## Что считается начатым

- Локальный git-репозиторий создан.
- Основная ветка `master` используется для первого прямого push без PR по разрешению владельца проекта.
- План backend foundation описан.
- Роли и права описаны.
- Demo seed data описан.
- Инфраструктурная модель локального Docker-контура описана.
- Docker-ресурсы должны использовать префикс `shopsbox_`.
- Symfony skeleton создан в `backend/`.
- Локальный Docker Compose контур поднят.
- Healthcheck `GET /health` отвечает `200 {"service":"shopsbox_backend","status":"ok"}`.
- Репозиторий GitHub `ruphp/shopsbox` создан и первый push в `master` выполнен.
- Проект GitHub `ShopsBox` создан и привязан к репозиторию.
- В GitHub Project настроены статусы как в Freepainters: `Бэклог`, `Готово к работе`, `В работе`, `На проверке`, `Готово`.
- Создан вид `Бэклог` с раскладкой доски (`board`).
- Задача `#1` закрыта как выполненная и переведена в `Готово`.
- Задача `#2` закрыта как выполненная, PR `#8` смержен в `master`.
- Задача `#3` взята в локальную работу в ветке `task/03-file-storage-foundation`.
- Задачи `#4`-`#5` лежат в `Бэклог`.
- Защита ветки (`branch protection`) и набор правил (`ruleset`) для `master` не включились, потому что GitHub требует Pro или публичный репозиторий для этой возможности.

## Следующий технический шаг

Статус: локальная реализация `#3` начата в ветке `task/03-file-storage-foundation`.

Что уже завершено по `#2`:

- зафиксирована архитектура модульного монолита: один Symfony backend, модуль `Tenant`, слои `Domain`, `Application`, `Infrastructure`, `Presentation`;
- подключены Doctrine ORM, Doctrine Migrations, Security Bundle и Doctrine Fixtures;
- добавлена первая миграция для таблиц `tenants`, `stores`, `users`, `roles`, `user_roles`;
- добавлены Doctrine persistence-сущности tenant foundation в `Tenant/Infrastructure/Persistence/Doctrine/Entity`;
- добавлен demo seed в `Tenant/Infrastructure/Persistence/Doctrine/Fixtures`;
- добавлен первый use case `CreateTenant` и Presentation-контроллер `POST /tenants` с DTO и контрактами;
- в `CreateTenant` добавлены базовые проверки входных данных и проверка уникальности домена магазина через repository contract;
- добавлен первый набор unit-тестов на существующий `CreateTenantUseCase` без лишних production-классов ради тестов;
- добавлены make-команды `composer-require`, `composer-require-dev`, `composer-update-lock`, `fixtures-load`, `backend-check`.

Проверки локально:

- `make composer-install` - успешно;
- `make backend-check` - успешно;
- `make migrate` - успешно, миграция `DoctrineMigrations\Version20260414090000` применена;
- `make fixtures-load` - успешно, demo fixtures загружены;
- `make unit-test` - успешно, 7 tests / 22 assertions;
- `make test` - успешно, документационный скелет, Symfony container lint и Doctrine mapping проходят.

Следующий шаг по `#3` - сначала уточнить storage foundation в документации и спроектировать минимальную структуру модуля/слоя файлов без преждевременного усложнения. Требования issue: не привязывать код к локальному пути контейнера/VPS, работать через storage interface, предусмотреть local и S3-compatible режимы, публичные URL строить через отдельный builder/config, пользовательские файлы не хранить внутри контейнера приложения.
