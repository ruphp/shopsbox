# Контекст проекта ShopsBox

## Общее описание

ShopsBox - рабочее название SaaS/managed-платформы для интернет-магазинов. Смысл: готовый магазин в коробке. Основной сценарий: клиент получает магазин под ключ, а платформа берет на себя администрирование, обновления, бэкапы, SSL, домены и техническое сопровождение.

Evirox остается отложенным названием для будущих идей или более широкой платформы, но текущий ecommerce-продукт ведем как ShopsBox.

Дополнительный сценарий: Own Infra, где фронт, база или часть backend-сервисов находятся на стороне клиента, а наша SaaS-админка управляет ими через защищенный API/agent.

## Рабочий продуктовый фокус

Первым делаем не "конструктор всего", а управляемую платформу для запуска магазинов:

- managed hosting для простых клиентов;
- веб-админка владельца магазина;
- супер-админка платформы;
- простая витрина магазина;
- каталог, заказы, клиенты, роли, настройки;
- операционные функции: домен, SSL, бэкапы, статус сервисов.

Стартовая продуктовая стратегия собрана из лучших решений ecommerce/backoffice-продуктов: простота Shopify, ощущение владения данными как у self-hosted платформ, API-first подход headless commerce и control plane через agent/API как у backoffice-платформ.

Freepainters и CorporateHouse не считаются продуктовыми аналогами ShopsBox. Из них учитываем рабочие уроки: правила взаимодействия с AI, дисциплину документации, проблемы расползания задач и аккуратное ведение папок.

## Технологический стек

- Backend: PHP + Symfony.
- База данных: PostgreSQL как основной кандидат.
- Очереди: Symfony Messenger или совместимый инфраструктурный слой.
- Cache/session: Redis как кандидат.
- Web frontend MVP: Twig + Bootstrap, точечная интерактивность через Stimulus/vanilla JS.
- React + TypeScript: отложенный вариант для сложных экранов или будущей SPA-админки.
- Mobile/desktop: Kotlin Multiplatform рассматривается после стабилизации web API.
- Локальная инфраструктура: Docker Compose.
- Будущая инфраструктура: Kubernetes, когда появится практическая необходимость.
- Проектные операции: только через `make`-команды, чтобы не ловить проблемы PowerShell с кириллицей и многострочным текстом.

## Архитектурные ориентиры

- Clean Architecture.
- DDD.
- SOLID.
- Multi-tenancy.
- API-first для админки.
- Бизнес-логика в Domain/Application, не в контроллерах.
- Миграции для всех изменений схемы БД.
- Own Infra через API/agent, а не через прямое подключение SaaS к базе клиента.
- Локальные Docker-контуры для разработки добавляются по мере необходимости и запускаются через `make`.

## Документация

- `docs/README.md` - вход в документацию.
- `docs/product/00-product-concept.md` - продуктовая концепция.
- `docs/product/01-pages-and-modules.md` - карта страниц и модулей.
- `docs/product/05-admin-prototype-from-references.md` - прототип админки на основе аналогов.
- `docs/product/06-admin-screen-text-prototypes.md` - текстовые прототипы экранов админки.
- `docs/product/07-business-model-and-monetization.md` - бизнес-модель и взаимовыгода.
- `docs/product/08-storefront-customization-strategy.md` - стратегия настройки витрины.
- `docs/architecture/00-architecture-overview.md` - архитектура.
- `docs/architecture/03-deployment-models.md` - модели размещения.
- `docs/architecture/04-operational-responsibility-and-loads.md` - операционная ответственность и нагрузки.
- `docs/architecture/05-store-lifecycle-demo-to-production.md` - жизненный цикл магазина: демо, пилот, production.
- `docs/architecture/06-resource-usage-and-limits.md` - учет ресурсов, нагрузки и лимитов по клиентам.
- `docs/architecture/07-infrastructure-scaling-model.md` - инфраструктурная модель, поддомены, shared/dedicated контуры и Kubernetes как будущий этап.
- `docs/tz/00-technical-spec-draft.md` - черновик ТЗ.
- `docs/tz/01-mvp-backlog.md` - MVP-бэклог.
- `docs/tz/07-backend-foundation-plan.md` - план первого технического шага backend foundation.
- `docs/tz/08-tz-progress-map.md` - карта прогресса ТЗ и навигация по текущему состоянию.
- `docs/tz/09-roles-and-permissions.md` - черновик ролей и прав MVP.
- `docs/workflow/00-github-workflow.md` - процесс работы через GitHub.
- `docs/workflow/01-mvp-foundation-issue-drafts.md` - локальные черновики issues для `01 MVP Foundation`.
- `docs/workflow/02-current-work-status.md` - локальная карточка текущей работы и ее статус.
- `docs/development/01-move-to-shopsbox.md` - перенос проекта в папку `shopsbox`.
- `docs/development/02-demo-seed-data.md` - demo tenant/store/users и dev-only пароли для будущего сидера.
- `AGENTS.md` - обязательные правила работы.

## Текущий статус

Проект перешел от стадии формирования ТЗ и архитектуры к этапу `01 MVP Foundation`. Symfony skeleton создан в `backend/`, локальный Docker Compose контур поднят, healthcheck backend отвечает успешно.

Git-репозиторий в текущей рабочей папке инициализирован, GitHub-репозиторий `ruphp/shopsbox` привязан, первый push в `master` выполнен по отдельному разрешению владельца проекта.

Карточка `#1 Основа бэкенда: каркас Symfony и локальный Docker-контур` закрыта как выполненная и переведена в `Готово`. Локальная ветка сейчас `master`, потому что первый прямой push был разрешен отдельно. Следующий технический шаг - выбрать следующую задачу из бэклога, вероятнее всего `#2 Основа арендаторов, магазинов, пользователей и ролей`.
