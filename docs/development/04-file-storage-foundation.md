# File storage foundation

Документ фиксирует рабочее решение для задачи `#3 Основа файлов: локальное и S3-совместимое хранилище`.

## Цель

Заложить слой работы с пользовательскими файлами так, чтобы будущие модули каталога, витрины и админки не зависели от локального пути контейнера или конкретного S3-провайдера.

## Архитектурное решение

Для задачи `#3` создаем отдельный модуль `FileStorage`.

Причина: хранение файлов - не часть `Tenant`, `Catalog` или `Order`, а инфраструктурная возможность продукта, которую позже будут использовать разные доменные модули. При этом не кладем код в безымянный общий слой `Shared`, чтобы не получить свалку утилит.

Фактическая структура первого шага:

```text
backend/src/FileStorage/
  Application/
    Contracts/
    Dto/
  Infrastructure/
    Storage/
    Url/
  Presentation/
    Http/
      Controller/
```

`Domain` пока не создаем, если нет самостоятельных бизнес-правил файла. Если позже появятся правила жизненного цикла файла, статусы проверки, привязки к store/tenant или права доступа, тогда добавим `Domain`.

## Границы MVP

Делаем:

- `Storage` interface для записи/чтения/удаления файлов.
- DTO результата сохранения файла.
- Flysystem adapter, который работает через один application contract.
- local режим, который пишет в `var/storage/files` на смонтированном volume проекта, а не во внутренний слой контейнера.
- S3-compatible режим через Flysystem AWS S3 adapter и env/config для MinIO/S3.
- URL builder, который строит публичный URL отдельно от storage adapter.
- env/config для выбора storage adapter.

Не делаем сейчас:

- UI загрузки файлов.
- Каталог товаров и изображения товаров.
- CDN.
- Сложный lifecycle файлов.
- Антивирус, ресайзинг, очереди обработки изображений.
- Биллинг и учет storage usage.

## Правила

- Бизнес-код не должен знать локальный путь файла.
- Пользовательские файлы нельзя хранить внутри контейнера backend.
- Публичный URL строится через отдельный URL builder/config, а не руками в контроллере.
- Для будущего S3/MinIO используем S3-compatible модель: bucket, key, endpoint, public endpoint.
- `local` режим нужен для простого dev/pilot, но должен иметь тот же application contract, что и S3.

## Что добавлено в коде

- `Application/Contracts/FileStorage` - application contract для `write`, `read`, `delete`.
- `Application/Contracts/FileUrlBuilder` - contract для построения публичного URL.
- `Application/Dto/StoredFile` - DTO результата сохранения.
- `Infrastructure/Storage/FlysystemFileStorage` - реализация storage contract через Flysystem.
- `Infrastructure/Storage/FlysystemFactory` - выбор local или S3-compatible adapter по env/config.
- `Infrastructure/Url/ConfiguredFileUrlBuilder` - URL builder от базового публичного URL.
- `Presentation/Http/Controller/ServeFileController` - dev/local endpoint `GET /files/{key}` для отдачи файла по публичному URL local-режима.

Конфигурация:

- `FILE_STORAGE_ADAPTER=local` по умолчанию.
- `FILE_STORAGE_LOCAL_PATH=var/storage/files`.
- `FILE_STORAGE_PUBLIC_BASE_URL=http://localhost:8080/files`.
- `S3_ENDPOINT`, `S3_BUCKET`, `S3_ACCESS_KEY`, `S3_SECRET_KEY`, `S3_REGION`, `S3_PATH_STYLE` подготовлены для MinIO/S3-compatible режима.

Таблицу `files` пока не создаем. Сейчас нет реального сценария загрузки и привязки файла к товару, store или tenant. Таблица появится, когда станет понятно, какие metadata нужны: owner, visibility, mime type, size, checksum, lifecycle/status.

`GET /files/{key}` нужен только как минимальный local/dev способ проверить публичный URL. В будущем для production/S3/CDN этот endpoint может не использоваться: URL builder сможет отдавать URL object storage или CDN.

## Тестовое решение

На первом шаге добавлены focused tests для `FlysystemFileStorage` на local adapter через временную директорию и для `ConfiguredFileUrlBuilder`.

Integration-тест с MinIO/S3 можно отложить до сценария реальной загрузки файла через HTTP, чтобы не усложнять foundation раньше времени.

Проверки:

- `make test` - успешно, 9 tests / 27 assertions.
