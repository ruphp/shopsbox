# Карта прогресса ТЗ

Документ нужен как навигация: что уже описано, что описано частично и куда смотреть, если нужно понять текущую точку проекта.

## Текущая точка

Мы находимся на этапе `01 MVP Foundation`.

Карточка `#1 Основа бэкенда: каркас Symfony и локальный Docker-контур` завершена. Локальная работа по карточке `#2 Основа арендаторов, магазинов, пользователей и ролей` идет в ветке `task/02-tenant-foundation`: подключены Doctrine ORM/Migrations/Fixtures, добавлены первые persistence-сущности, миграция tenant foundation и demo seed.

## Статусы

Статусы:

- `Готово` - документ можно использовать как основу для следующего шага.
- `Частично` - есть черновик, но перед реализацией нужно уточнить детали.
- `Не начато` - пока только идея в backlog.

## Этап 1: требования и границы

Статус: `Готово для текущего уровня`.

Где смотреть:

- [ТЗ: черновик](00-technical-spec-draft.md)
- [Продуктовая концепция](../product/00-product-concept.md)
- [Решения, которые берем в основу](../product/02-reference-decisions.md)
- [Бизнес-модель и взаимовыгода](../product/07-business-model-and-monetization.md)
- [Стратегия настройки витрины](../product/08-storefront-customization-strategy.md)

Что важно: ShopsBox ведем как managed store с будущим Own Infra через API/agent. Продуктовые решения берем из ecommerce/backoffice-практик и адаптируем под MVP.

## Этап 2: структура продукта

Статус: `Готово для backend foundation, частично для полного продукта`.

Где смотреть:

- [Карта страниц и модулей](../product/01-pages-and-modules.md)
- [Структура страниц: черновик](../product/03-page-structure-draft.md)
- [Прототип админки на основе аналогов](../product/05-admin-prototype-from-references.md)
- [Текстовые прототипы экранов админки](../product/06-admin-screen-text-prototypes.md)
- [User Stories](03-user-stories.md)
- [Роли и права: черновик MVP](09-roles-and-permissions.md)

Что уже сделано:

- Общая карта страниц и модулей описана.
- Ключевые страницы админки владельца магазина описаны подробнее.
- Для админки есть текстовые прототипы MVP-экранов.
- Публичная витрина описана на уровне основных страниц и задач.
- Супер-админка платформы описана на уровне разделов и операций.

Что еще уточнить:

- Роли и права доведены до отдельной MVP-матрицы.
- Публичную витрину можно детализировать до текстовых прототипов, как админку.
- Супер-админку можно детализировать после foundation-слоя, когда будут понятны первые операции с tenants/stores.

Ответ на вопрос "карта страниц сделана?": да, базовая карта страниц сделана; детальная проработка сделана прежде всего для админки владельца магазина. Витрина и супер-админка пока на уровне черновика.

## Этап 3: техническая архитектура

Статус: `Готово для старта foundation`.

Где смотреть:

- [Архитектура](../architecture/00-architecture-overview.md)
- [Доменная стратегия](../architecture/02-domain-strategy.md)
- [Модели размещения](../architecture/03-deployment-models.md)
- [Операционная ответственность и нагрузки](../architecture/04-operational-responsibility-and-loads.md)
- [Жизненный цикл магазина: демо, пилот, production](../architecture/05-store-lifecycle-demo-to-production.md)
- [Backend foundation: план первого технического шага](07-backend-foundation-plan.md)

Что уже принято:

- Backend: PHP + Symfony.
- Web MVP: Twig + Bootstrap.
- Runtime: Docker Compose через `make`.
- БД: PostgreSQL.
- Миграции: Doctrine Migrations.
- Multi-tenancy MVP: shared app + shared DB + `tenant_id` / `store_id`.
- Архитектура: модульный монолит с разделением Domain/Application/Infrastructure/Presentation.
- Инфраструктурный рост: старт с shared managed-контура, затем object storage/CDN/metrics, horizontal scale, Dedicated Managed и только потом Kubernetes/platform при реальной необходимости.
- Первый этап - локальная разработка в Docker Compose, где мы моделируем будущий VDS/VPS-контур. Production-пилот может позже стартовать как `VPS/VDS или cloud VM + Docker Compose + backend + PostgreSQL`. CDN для Twig + Bootstrap MVP не критичен и подключается позже по трафику витрины/изображений. Файлы, URL и окружения нужно проектировать так, чтобы потом вынести storage/БД/backend без глобальной переделки. S3-compatible storage/MinIO, Redis и cron/scheduler runner считаются плановыми расширениями, под которые нельзя закрывать дорогу в коде.
- Production-бэкапы не являются задачей самого раннего dev-этапа, но перед реальными клиентами нужно добавить backup/restore регламент и затем автоматизировать его через scheduler.

## Этап 4: спецификация разработки

Статус: `Частично готово`.

Где смотреть:

- [MVP-бэклог](01-mvp-backlog.md)
- [Модель данных: черновик](04-data-model-draft.md)
- [API endpoints: черновик](05-api-endpoints-draft.md)
- [Глоссарий](06-glossary.md)
- [Backend foundation: план первого технического шага](07-backend-foundation-plan.md)
- [Demo seed data](../development/02-demo-seed-data.md)

Что уже сделано:

- Есть MVP backlog по этапам.
- Есть черновик модели данных и ER-диаграмма.
- Есть черновик API endpoints.
- Есть глоссарий терминов.
- Есть GitHub milestones, labels и issues для планирования работ.
- Есть план первого backend foundation шага.
- Есть demo tenant/store/users для будущего локального сидера.

Что еще уточнить:

- Поддерживать GitHub issues в актуальном состоянии по мере развития MVP.
- Уточнить первую миграцию foundation-слоя.
- После подтверждения старта локальной разработки создать Symfony skeleton и Docker Compose контур через `make`-команды.

## Как ориентироваться дальше

Если вопрос про продукт: начинать с [ТЗ: черновик](00-technical-spec-draft.md), затем идти в [Карту страниц и модулей](../product/01-pages-and-modules.md) и [Текстовые прототипы экранов админки](../product/06-admin-screen-text-prototypes.md).

Если вопрос про архитектуру: начинать с [Архитектуры](../architecture/00-architecture-overview.md), затем смотреть [Доменную стратегию](../architecture/02-domain-strategy.md).

Если вопрос "что делать следующим": смотреть [Backend foundation: план первого технического шага](07-backend-foundation-plan.md).

Если вопрос "что уже сделано": смотреть этот документ и [MVP-бэклог](01-mvp-backlog.md).
