# Основа каталога

## Что сделано

- Добавлен модуль `Catalog`.
- Добавлена структура слоев:
  - `Domain`;
  - `Application/Dto`;
  - `Application/Contracts`;
  - `Application/UseCase`;
  - `Infrastructure/Persistence/Doctrine/Entity`;
  - `Infrastructure/Persistence/Doctrine/Repository`;
  - `Infrastructure/Persistence/Doctrine/Fixtures`;
  - `Presentation`.
- Добавлен доменный enum `ProductStatus` со статусами:
  - `draft`;
  - `active`;
  - `archived`.
- Добавлены Doctrine entities:
  - `Category`;
  - `Product`.
- Добавлены Doctrine repositories:
  - `DoctrineCategoryRepository`;
  - `DoctrineProductRepository`.
- Добавлена миграция `DoctrineMigrations\Version20260418130000`.
- Добавлены таблицы:
  - `categories`;
  - `products`.
- Учтены `tenant_id` и `store_id` boundaries.
- Обновлена документация `docs/development/07-catalog-foundation.md`.

## Архитектурное решение

`Catalog` создан как отдельный бизнес-модуль модульного монолита.

На этом шаге не добавлены use cases, DTO сценариев, контроллеры и формы, потому что #12 ограничен foundation-слоем каталога. Админский CRUD, загрузка изображений и витрина вынесены в отдельные issues.

Doctrine entities пока достаточно для foundation CRUD-модели. Если в следующих задачах появятся сложные правила публикации, архивации, видимости товара или связки с категориями, их нужно будет вынести в Domain/Application и покрыть unit-тестами.

## Проверки

- `make migrate` - успешно, применена миграция `DoctrineMigrations\Version20260418130000`.
- `make backend-check` - успешно.
- `make test` - успешно, 11 tests / 32 assertions.

## Тестовое покрытие

Unit-тесты для `ProductStatus` не добавлены, потому что сейчас это нативный PHP enum без собственных правил переходов.

Тестировать наличие cases `draft`, `active`, `archived` сейчас было бы проверкой языка, а не бизнес-логики. Unit-тесты понадобятся в следующих задачах, когда появятся правила:

- можно ли публиковать товар из `draft`;
- можно ли возвращать товар из `archived`;
- какие статусы видны на витрине;
- какие переходы разрешены в admin CRUD.

Closes #12
