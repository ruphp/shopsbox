# Admin Catalog CRUD

Документ фиксирует минимальную реализацию issue #13: управление товарами каталога через admin API.

## Что добавлено

Модуль `Catalog` получил сценарии для базового CRUD товара:

- список товаров магазина;
- создание товара;
- редактирование товара;
- публикация товара;
- скрытие товара;
- архивирование товара.

В MVP это JSON routes, а не Twig-страницы. Причина простая: текущий backend уже ведется API-first, а Twig/Form component пока не подключались как отдельный UI-слой.

## HTTP routes

```text
GET   /admin/tenants/{tenantId}/stores/{storeId}/products
POST  /admin/tenants/{tenantId}/stores/{storeId}/products
PATCH /admin/tenants/{tenantId}/stores/{storeId}/products/{productId}
POST  /admin/tenants/{tenantId}/stores/{storeId}/products/{productId}/publish
POST  /admin/tenants/{tenantId}/stores/{storeId}/products/{productId}/hide
POST  /admin/tenants/{tenantId}/stores/{storeId}/products/{productId}/archive
```

Контроллер находится в `backend/src/Catalog/Presentation/Http/Controller/AdminProductController.php`.

## Как идет поток по слоям

```text
HTTP request
  -> Presentation/Form собирает Application DTO
  -> Presentation/Controller вызывает UseCase
  -> Application/UseCase проверяет входные данные и правило статуса
  -> Application/Contracts описывают нужные действия
  -> Infrastructure/Doctrine реализует чтение и запись
  -> EntityFlusher делает flush()
```

Контроллер не содержит бизнес-логику. Он только переводит HTTP-запрос в DTO и ошибки use case в HTTP-ответы.

## Правило статусов товара

Статусы товара:

- `draft`;
- `active`;
- `archived`.

Разрешенные переходы:

- `draft -> active`;
- `draft -> archived`;
- `active -> draft`;
- `active -> archived`;
- переход в тот же статус разрешен.

`archived` считается конечным состоянием: из него нельзя вернуться в `draft` или `active`.

Правило лежит в `backend/src/Catalog/Domain/ProductStatus.php`, потому что это доменное правило каталога, а не деталь HTTP или Doctrine.

## Тестовое покрытие

Добавлены unit-тесты:

- `ProductStatusTest` проверяет разрешенные и запрещенные переходы статусов;
- `ChangeProductStatusUseCaseTest` проверяет публикацию товара и запрет публикации архивного товара.

Functional tests на HTTP routes пока не добавлены, потому что в проекте еще нет отдельного HTTP test stack (`symfony/browser-kit`, `WebTestCase`-сценарии и подготовка тестовой БД). Это стоит добавить отдельной задачей, когда начнем стабилизировать admin API как внешний контракт.
