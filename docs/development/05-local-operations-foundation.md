# Local operations foundation

Документ фиксирует рабочее решение для задачи `#4 Локальные операции: планировщик, заготовки бэкапов и проверки здоровья`.

## Цель

Подготовить минимальный операционный каркас для локального Docker-контура без production-overkill.

На этом этапе ShopsBox еще не обслуживает реальных клиентов, поэтому не поднимаем отдельный production backup-service, monitoring stack или scheduler-контейнер. Но заранее оставляем понятные входы через `make`, чтобы перед production-пилотом не вспоминать операции с нуля.

## Что добавлено

В `Makefile` добавлены команды:

- `make health-check` - проверяет `GET http://localhost:8080/health`.
- `make db-dump` - создает dev dump PostgreSQL в `var/backups/shopsbox-dev.sql`.
- `make db-restore BACKUP_FILE="var\backups\shopsbox-dev.sql"` - восстанавливает БД из указанного dump-файла.

`make test` продолжает включать:

- проверку документационного скелета;
- lint Symfony container;
- проверку Doctrine mapping;
- unit/focused tests.

## Healthcheck

Существующий endpoint:

```text
GET /health
```

возвращает:

```json
{"service":"shopsbox_backend","status":"ok"}
```

Для локального контура этого достаточно: команда `make health-check` проверяет, что backend доступен с хоста через порт `8080`.

Позже для production-пилота healthcheck можно расширить отдельными проверками:

- доступность PostgreSQL;
- доступность Redis;
- доступность object storage;
- готовность миграций;
- состояние очередей и scheduler-runner.

Но сейчас не смешиваем простой HTTP healthcheck с полноценным мониторингом зависимостей.

## Backup и restore

На dev-этапе добавлены ручные команды:

```text
make db-dump
make db-restore BACKUP_FILE="var\backups\shopsbox-dev.sql"
```

Это не production backup policy. Это заготовка для локальной проверки dump/restore и будущего регламента.

Перед production-пилотом нужно будет отдельно определить:

- где хранить backup-файлы;
- сколько restore points держать;
- как часто делать backup;
- как проверять восстановление;
- кто получает alert, если backup не прошел;
- как учитывать размер бэкапов по tenant/store.

Для production нельзя считать backup готовым, пока не было хотя бы одного проверенного восстановления.

## Scheduler-runner

Отдельный scheduler-runner пока не поднимаем.

Причина: сейчас нет реальных регулярных задач, которые должны выполняться по расписанию. Если добавить контейнер заранее, он будет имитировать зрелую эксплуатацию без полезной нагрузки.

Направление фиксируем такое:

```text
Symfony console command
  -> один управляемый scheduler-runner
  -> регулярные задачи
```

Будущие задачи scheduler-runner:

- регулярный backup БД;
- cleanup временных файлов;
- сбор usage metrics;
- проверка healthchecks;
- очистка старых логов или служебных записей;
- обслуживание очередей, если это будет нужно архитектурно.

Важное правило: когда backend начнет масштабироваться в несколько replicas, регулярная задача не должна случайно запускаться в каждой копии backend. Для этого нужен один управляемый runner или блокировки.

## Границы MVP

Сейчас делаем:

- простую проверку здоровья backend;
- ручной dev dump/restore через `make`;
- документацию будущего scheduler-runner;
- документацию условий, которые нужны перед production-пилотом.

Сейчас не делаем:

- отдельный backup-service;
- отдельный scheduler container;
- production retention policy;
- алерты;
- автоматические ночные backup jobs;
- backup файлового storage;
- мониторинг Redis/PostgreSQL/MinIO как production-системы.

## Тестовое решение

Новые unit-тесты не нужны, потому что в задаче нет новой доменной или application-логики.

Проверять нужно операционные команды и Docker-контур:

- `make health-check`;
- `make db-dump`;
- `make db-restore`;
- `make test`.

Если позже появится код scheduler-команд или backup-метаданные, для них уже понадобятся отдельные unit/integration tests.

## Проверки текущей реализации

- `make health-check` - успешно, `/health` вернул `{"service":"shopsbox_backend","status":"ok"}`.
- `make db-dump` - успешно, dev dump был создан в `var/backups/shopsbox-dev.sql`.
- `make db-restore` - успешно, локальная dev-БД восстановлена из созданного dump.
- `make test` - успешно, 11 tests / 32 assertions.

Созданный во время проверки dump удален после прогона. Каталог `var/backups/` добавлен в `.gitignore`, потому что backup-файлы не должны попадать в репозиторий.
