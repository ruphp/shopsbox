# GitHub workflow

## Зачем

Для ShopsBox рабочий процесс ведем через GitHub, а не через Trello. GitHub должен стать единым местом для задач, обсуждения реализации, приемки и истории решений.

## Что используем

- GitHub Issues - задачи, баги, исследовательские вопросы.
- GitHub Pull Requests - кодовые изменения и проверка.
- GitHub Milestones - этапы: ТЗ, MVP backend, MVP storefront, операционная часть, mobile подготовка.
- GitHub Project - доска статусов.
- Labels - тип, приоритет и доменная область.
- Markdown-документация в репозитории - ТЗ, архитектура, решения, backlog.
- `make`-команды - единая точка запуска локальных и GitHub-операций.

## Make-only правило

- Любую повторяемую операцию оформлять как `make`-цель.
- PowerShell не использовать для проектной работы и передачи русского текста.
- PowerShell не использовать для heredoc, многострочного JSON и GitHub API payloads. Все такие payloads хранить в UTF-8 файлах и вызывать через `make`.
- Если нужна команда для Docker, GitHub CLI, Symfony, Composer, Gradle или проверок, сначала добавить `make`-цель.
- Русские тексты для GitHub issue, PR и комментариев передавать через UTF-8 файлы.
- Docker-контуры для локальной разработки создавать по мере необходимости, но запускать их через `make`.

## Статусы доски

- Backlog - идея или задача есть, но еще не готова к работе.
- Ready - задача описана достаточно, чтобы начать.
- In progress - задача выполняется.
- In review - открыт PR или идет проверка.
- Done - задача завершена и принята.

## Рекомендуемые labels

- `type: feature`
- `type: bug`
- `type: docs`
- `type: architecture`
- `type: research`
- `area: backend`
- `area: admin`
- `area: storefront`
- `area: mobile`
- `area: infra`
- `area: billing`
- `area: security`
- `priority: p0`
- `priority: p1`
- `priority: p2`
- `priority: p3`

## Milestones

- `00 TZ and Architecture` - текущий этап: документы, требования, модель данных, API-контуры.
- `01 MVP Foundation` - Symfony skeleton, Docker Compose, auth, tenants, roles.
- `02 Catalog and Orders` - каталог, товары, категории, корзина, заказы.
- `03 Storefront MVP` - публичная витрина и checkout.
- `04 Operations` - домены, SSL, бэкапы, healthchecks, журналы.
- `05 Mobile Preparation` - стабилизация API и первый контур KMP owner app.

## Правило удаленных действий

Удаленные действия выполняются только после отдельного прямого подтверждения:

- `push`;
- создание или обновление `PR`;
- `merge`;
- закрытие issue;
- перевод карточки в Done.

## Замена Figma и Trello

В пакете ТЗ "Максимум" для этого проекта:

- Figma заменяем на текстовые прототипы страниц, Mermaid-схемы и при необходимости HTML-прототипы в репозитории.
- Trello заменяем на GitHub Issues, Milestones и Project board.
- Google Docs можно заменить на Markdown-документы в репозитории; при необходимости позже экспортировать в PDF/Docs.
