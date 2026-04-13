# Текущий рабочий статус

Документ фиксирует локальную карточку текущей работы. Он нужен, пока реальная GitHub Project card еще не создана.

## Current card

- Title: `Backend foundation: Symfony skeleton and local Docker`
- Status: `In progress`
- Local branch: `master`
- Milestone: `01 MVP Foundation`
- Remote GitHub issue: create after initial push and branch protection
- Remote GitHub Project card: create after initial push and branch protection

## Почему карточка локальная

В папке `D:\codex\shopsbox` git-репозиторий только что инициализирован локально. Remote repository пока не привязан, поэтому нельзя честно создать или перевести реальную GitHub Project card.

До появления remote/repo рабочая карточка считается локальной. Черновики будущих GitHub issues лежат в [Черновиках issues для 01 MVP Foundation](01-mvp-foundation-issue-drafts.md).

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

## Следующий технический шаг

Создать Symfony skeleton и локальный Docker Compose контур через `make`-команды:

- `shopsbox_backend` / `shopsbox_web`;
- `shopsbox_postgres`;
- `shopsbox_redis`;
- `shopsbox_minio`;
- `shopsbox_worker` later;
- `shopsbox_scheduler` later.

Статус: базовый Symfony/Docker contour уже поднят. Следующий шаг - добавить Doctrine migrations, foundation entities и demo seed/fixtures.
