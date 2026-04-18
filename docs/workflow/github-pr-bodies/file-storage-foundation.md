# Основа файлового хранилища

## Что сделано

- Добавлен модуль `FileStorage` без преждевременного доменного слоя.
- Добавлены application-контракты `FileStorage` и `FileUrlBuilder`.
- Добавлен DTO `StoredFile` для результата сохранения файла.
- Подключен Flysystem для единого интерфейса работы с local/S3-compatible storage.
- Добавлена инфраструктурная реализация `FlysystemFileStorage`.
- Добавлена фабрика `FlysystemFactory`, которая выбирает `local` или `s3` adapter по env/config.
- Добавлен `ConfiguredFileUrlBuilder` для сборки публичных URL.
- Добавлен dev/local endpoint `GET /files/{key}` для проверки отдачи файлов в local-режиме.
- Добавлена конфигурация env/services для local storage и будущего MinIO/S3-compatible режима.
- Обновлена документация по решению в `docs/development/04-file-storage-foundation.md`.

## Архитектурное решение

`FileStorage` сделан отдельным инфраструктурным модулем, а не частью `Tenant` или общего `Shared`.

На этом этапе `Domain` для файлов не создается, потому что пока нет самостоятельных бизнес-правил файла: lifecycle, статусов, прав доступа, владельца, checksum или привязки к товару/store/tenant. Эти правила появятся позже вместе с реальным сценарием загрузки.

Будущие use case должны зависеть от `App\FileStorage\Application\Contracts\FileStorage`, а не от Flysystem, local path, S3 SDK или Symfony-конфига.

## Проверки

- `make test` - успешно, 11 tests / 32 assertions.
- Docker-контур поднят через `make up`.
- Проверен живой local-цикл:
  - временный файл создан в `backend/var/storage/files/tenant/demo.txt`;
  - запрос `GET http://localhost:8080/files/tenant/demo.txt` вернул `200`;
  - тело ответа: `demo content from local storage`;
  - временный файл удален после проверки.

## Тестовое покрытие

- Unit/focused tests для `FlysystemFileStorage`: запись, чтение и удаление файла через local adapter.
- Unit/focused tests для `FlysystemFactory`: создание local filesystem и ошибка на неизвестный adapter.
- Unit/focused tests для `ConfiguredFileUrlBuilder`: корректная сборка URL без двойных слэшей.

Integration-тест с MinIO/S3 пока не добавлен осознанно: на этом шаге нет реального HTTP-сценария загрузки файла и metadata-модели. Его логичнее добавить вместе с первым полноценным сценарием загрузки файла.

Closes #3
