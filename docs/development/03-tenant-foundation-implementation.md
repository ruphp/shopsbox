# Tenant foundation implementation

Документ фиксирует первый технический слой задачи `#2 Основа арендаторов, магазинов, пользователей и ролей`.

## Что добавлено

- Doctrine ORM и Doctrine Migrations подключены как persistence-инструменты Symfony.
- Doctrine Fixtures подключены только для `dev` и `test`, чтобы локально загружать demo tenant/store/users.
- Backend ведется как модульный монолит: один Symfony backend, доменные области разложены по модулям.
- В задаче `#2` создается только модуль `Tenant`, без заготовок под `Catalog`, `Order`, `Billing`.
- Структура модуля Tenant приведена к договоренному шаблону.
- Первые persistence-сущности лежат в `backend/src/Tenant/Infrastructure/Persistence/Doctrine/Entity`.
- Demo fixtures лежат в `backend/src/Tenant/Infrastructure/Persistence/Doctrine/Fixtures`.
- Добавлен первый use case `CreateTenant` со входным DTO, контрактами и инфраструктурными адаптерами.
- Первая миграция лежит в `backend/migrations`.

## Почему сущности лежат в Infrastructure

На этом шаге таблицы `tenants`, `stores`, `users`, `roles`, `user_roles` нужны как foundation-слой для будущих сценариев. Сложных доменных правил пока нет, поэтому богатую доменную модель не раздуваем заранее.

Doctrine-сущности сейчас описывают хранение данных и связи в базе внутри `Infrastructure`. Когда появятся реальные правила, например приглашение сотрудника, смена роли, блокировка пользователя или границы доступа между tenant/store, эти правила нужно выносить в `Domain` и `Application`, а Doctrine оставить адаптером хранения.

`Presentation` - место для HTTP-контроллеров, форм, Twig/API output и view models. Внутри `Presentation/Http` выделены `Controller` и `Form`. В текущем шаге этот слой пустой, потому что issue `#2` пока про foundation-таблицы и demo seed, а не про пользовательские сценарии.

## Структура модуля Tenant

```text
backend/src/Tenant/
  Domain/
  Application/
    Dto/
    Contracts/
    Exception/
    UseCase/
  Infrastructure/
    Persistence/
      Doctrine/
        Entity/
        Repository/
        Fixtures/
    Adapters/
  Presentation/
    Http/
      Controller/
      Form/
```

## Как читать папки модуля

Рабочая формулировка владельца проекта:

> Папка Application содержит сценарии приложения. Главный объект там - UseCase.
> UseCase описывает, что нужно сделать по шагам.
> Если use case нужно обратиться к БД, внешнему сервису или техническому действию, он делает это через интерфейс из Application/Contracts.
> Реализации этих интерфейсов лежат в Infrastructure.

Коротко по слоям:

- `Domain` - бизнес-правила, когда они становятся самостоятельными и не зависят от Symfony, HTTP или БД.
- `Application` - сценарии приложения: DTO, use cases и contracts.
- `Application/Dto` - входные и выходные данные use case.
- `Application/UseCase` - шаги сценария: что нужно сделать и в каком порядке.
- `Application/Contracts` - договоры к внешним действиям: подготовить entity к записи (`persist`), проверить наличие, отправить уведомление, сгенерировать id.
- `Application/Exception` - ошибки application-сценариев, например неверный input.
- `Infrastructure` - технические реализации contracts: Doctrine, адаптеры, внешние сервисы.
- `Presentation` - вход и выход приложения: контроллеры, формы, подготовка HTTP/Twig/API-ответа.

## Первый use case

Use case `CreateTenant` показывает базовый поток:

1. `Presentation/Http/Form` собирает DTO из запроса.
2. `Application/UseCase` оркестрирует сценарий.
3. `Application/Contracts` описывают требования к инфраструктурным действиям.
4. Реализации контрактов лежат в `Infrastructure/Adapters` и `Infrastructure/Persistence/Doctrine/Repository`.

Repository contracts в этом шаге используют имя `persist`, а не `save`, потому что Doctrine реально пишет изменения в БД только на `flush`. Поэтому поток читается как `persist tenant`, `persist store`, затем `flush`.

UUID генерируются через Symfony UID как UUID v7. Это time-ordered UUID: он остается распределенным идентификатором, но лучше подходит для индексов БД, чем полностью случайный UUID v4.

В use case уже есть базовые проверки входных данных и проверка уникальности домена магазина через `StoreRepository`. Проверка домена находится в application-сценарии, а конкретный запрос к БД живет в Doctrine repository.

## Как думать про тесты

Мы не пишем тесты ради галочки, но перед добавлением нового правила останавливаемся и выбираем уровень проверки.

- Unit tests нужны, когда правило можно проверить без БД и Symfony: например формат slug, разрешенные статусы, переходы состояний.
- Integration tests нужны, когда правило зависит от БД или repository: например уникальность домена магазина или поиск роли.
- Functional tests нужны, когда важно проверить HTTP-сценарий целиком: request, form, use case, response code и JSON.
- Migrations/Docker checks нужны, когда меняется схема БД, контейнеры или runtime-команды.

Для текущего правила уникальности домена чистый unit test даст мало пользы, потому что смысл правила связан с уже сохраненными stores. Когда добавим тестовый стек, первым полезным кандидатом будет integration/functional проверка: повторный `POST /tenants` с тем же `store_domain` должен вернуть `400`.

Первый unit-test пример добавлен для уже существующего use case `CreateTenant`.

Тесты используют fake-реализации application contracts внутри тестового файла. Это важно: ради теста не создаем лишние production-классы в `Domain`. Если правило пока живет в `Application/UseCase`, тестируем use case напрямую. Когда правило станет самостоятельной доменной концепцией, например полноценный `StoreStatus` с реальным жизненным циклом магазина, тогда можно вынести его в `Domain` и покрывать отдельными unit-тестами.

Сейчас unit-тесты проверяют:

- успешное создание tenant/store через use case;
- ошибки формата входных данных;
- ошибку занятого домена через fake `StoreRepository`.

Третья проверка из примеров, переходы состояний, пока не реализована в production-коде. Поэтому отдельный `StoreStatusTransitionTest` сейчас был бы лишним: он заставил бы создать доменную модель только ради демонстрации теста.

Команда: `make unit-test`.

## Make-команды

- `make migrate` применяет миграции.
- `make fixtures-load` загружает локальные demo-данные.
- `make backend-check` проверяет Symfony container и Doctrine mapping.
- `make test` запускает документационные и backend-проверки.

Demo-пароли остаются только dev-only и описаны в `docs/development/02-demo-seed-data.md`.

## Проверки и тестовое покрытие

На текущем шаге добавлены persistence-сущности, миграция и demo fixtures. Отдельные unit tests пока не добавлены, потому что сложных доменных правил и application use cases еще нет: нет сценария, который можно полезно протестировать как бизнес-логику.

Когда появятся сценарии приглашения пользователя, смены роли, блокировки пользователя или проверки границ tenant/store, для них нужны focused unit tests на `Domain` и `Application`.

Для текущего foundation-шага обязательны локальные проверки:

- `make composer-install`;
- `make backend-check`;
- `make migrate`;
- `make fixtures-load`;
- `make test`.
