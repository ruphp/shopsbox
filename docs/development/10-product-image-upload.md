# Product image upload

Документ фиксирует реализацию issue #15: первый пользовательский сценарий, который сохраняет файл через `FileStorage`.

## Что добавлено

Добавлен сценарий загрузки изображения товара:

```text
POST /admin/tenants/{tenantId}/stores/{storeId}/products/{productId}/image
```

Запрос принимает multipart file field:

```text
image
```

Контроллер находится в `Catalog/Presentation/Http/Controller`, но сам не сохраняет файл. Он только собирает DTO и вызывает use case.

## Как идет поток

```text
HTTP multipart request
  -> Presentation/Form читает UploadedFile
  -> Application/UseCase валидирует товар, mime type и размер
  -> FileStorage сохраняет бинарные данные
  -> ProductImageRepository сохраняет metadata
  -> EntityFlusher делает flush()
```

## Что хранится

Файл сохраняется через `App\FileStorage\Application\Contracts\FileStorage`.

В БД хранится только metadata:

- `id`;
- `product_id`;
- `storage_key`;
- `public_url`;
- `mime_type`;
- `size`;
- `created_at`.

Таблица:

```text
product_images
```

## Ограничения MVP

Разрешенные типы:

- `image/jpeg`;
- `image/png`;
- `image/webp`;
- `image/gif`.

Максимальный размер:

```text
5 MB
```

В этой задаче не делаем resize, CDN, drag-and-drop, галерею, сортировку изображений и сложную обработку картинок.

## Тестовое покрытие

Добавлен unit-тест `UploadProductImageUseCaseTest`:

- успешная запись файла через fake `FileStorage`;
- сохранение metadata через fake repository;
- запрет неподдержанного mime type.

Integration/functional проверка через HTTP endpoint пока не добавлена отдельным тестом, потому что в проекте еще нет изолированного HTTP test stack и тестовой БД для `WebTestCase`. Локально сценарий проверяется миграцией, контейнерными backend-check/unit-test и smoke-маршрутом.
