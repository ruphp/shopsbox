# Локальные операции

## Что сделано

- Добавлена команда `make health-check` для проверки `GET /health`.
- Добавлена команда `make db-dump` для локального dev dump PostgreSQL.
- Добавлена команда `make db-restore` для восстановления локальной dev-БД из dump-файла.
- Добавлен ignore для `var/backups/`, чтобы backup-файлы не попадали в git.
- Добавлена документация `docs/development/05-local-operations-foundation.md`.
- Обновлены `CONTEXT.md` и `docs/workflow/02-current-work-status.md`.

## Архитектурное решение

На этом этапе не поднимаем отдельный production backup-service и не добавляем scheduler container.

Причина: проект еще на dev/foundation-этапе, реальных регулярных задач пока нет. Вместо имитации production-эксплуатации добавлены простые make-входы для проверки здоровья и ручного dump/restore.

Scheduler-runner зафиксирован как направление:

- регулярные backup jobs;
- cleanup временных файлов;
- сбор usage metrics;
- healthchecks;
- будущие регулярные задачи через Symfony console/Scheduler.

Когда backend начнет масштабироваться в несколько replicas, регулярные задачи должны выполняться через один управляемый runner или блокировки, а не случайно запускаться в каждой копии backend.

## Проверки

- `make health-check` - успешно, `/health` вернул `{"service":"shopsbox_backend","status":"ok"}`.
- `make db-dump` - успешно, dev dump был создан в `var/backups/shopsbox-dev.sql`.
- `make db-restore` - успешно, локальная dev-БД восстановлена из dump.
- `make docs-check` - успешно.
- `make test` - успешно, 11 tests / 32 assertions.

Созданный во время проверки dump удален после прогона.

## Тестовое покрытие

Новые unit-тесты не добавлены, потому что в задаче нет новой доменной или application-логики.

Покрытие задачи - операционные проверки Docker-контура и make-команд. Если позже появятся Symfony console commands для backup/scheduler или модель backup-метаданных, для них понадобятся отдельные unit/integration tests.

Closes #4
