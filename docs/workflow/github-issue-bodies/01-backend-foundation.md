# Цель

Создать локальный backend foundation для ShopsBox: Symfony skeleton и Docker Compose контур для разработки.

# Контекст

Это первая рабочая задача milestone `01 MVP Foundation`. Проект стартует локально в Docker Compose и моделирует будущий VDS/VPS-контур без production-деплоя.

# Требования

- Создать Symfony backend skeleton.
- Настроить локальный Docker Compose.
- Сервисы должны использовать префикс `shopsbox_` для project/container/volume/network имен.
- Минимальный локальный контур: backend/web, PostgreSQL, Redis, MinIO.
- Worker и scheduler-runner можно добавить сразу или оставить как явные placeholders.
- Все проектные операции запускать через `make`.
- Не использовать прямые `docker compose`, `php`, `composer` вручную без make-обертки.

# Критерии приемки

- `make up` поднимает локальный контур.
- `make down` останавливает локальный контур.
- `make logs` показывает логи.
- `make backend-shell` открывает shell backend-контейнера.
- `GET /health` отвечает 200.
- Документация обновлена, если фактическая схема отличается от плана.
