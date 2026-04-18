# ShopsBox: документация проекта

ShopsBox - рабочее название SaaS/managed-платформы для интернет-магазинов. Смысл: готовый магазин в коробке.

Evirox оставляем для будущих идей или более широкой платформы, а текущий ecommerce-продукт ведем как ShopsBox.

Идея: клиент получает интернет-магазин под ключ, а техническая сложность остается на стороне сервиса. Для простых клиентов платформа продается как managed store: магазин, база, фронт, бэкапы, домен, SSL, обновления и поддержка. Для продвинутых клиентов можно дать вариант Own Infra: фронт и база на их ресурсах, а наша админка управляет магазином через защищенный API/agent.

## Документы

- [ТЗ: черновик](tz/00-technical-spec-draft.md)
- [Продуктовая концепция](product/00-product-concept.md)
- [Карта страниц и модулей](product/01-pages-and-modules.md)
- [Структура страниц: черновик](product/03-page-structure-draft.md)
- [Нейминг](product/04-naming.md)
- [Прототип админки на основе аналогов](product/05-admin-prototype-from-references.md)
- [Текстовые прототипы экранов админки](product/06-admin-screen-text-prototypes.md)
- [Бизнес-модель и взаимовыгода](product/07-business-model-and-monetization.md)
- [Стратегия настройки витрины](product/08-storefront-customization-strategy.md)
- [Решения, которые берем в основу](product/02-reference-decisions.md)
- [Архитектура](architecture/00-architecture-overview.md)
- [Доменная стратегия](architecture/02-domain-strategy.md)
- [Модели размещения](architecture/03-deployment-models.md)
- [Операционная ответственность и нагрузки](architecture/04-operational-responsibility-and-loads.md)
- [Жизненный цикл магазина: демо, пилот, production](architecture/05-store-lifecycle-demo-to-production.md)
- [Учет ресурсов, нагрузки и лимитов](architecture/06-resource-usage-and-limits.md)
- [Инфраструктурная модель и масштабирование](architecture/07-infrastructure-scaling-model.md)
- [MVP-бэклог](tz/01-mvp-backlog.md)
- [Пакет подготовки ТЗ](tz/02-service-package.md)
- [User Stories](tz/03-user-stories.md)
- [Модель данных: черновик](tz/04-data-model-draft.md)
- [API endpoints: черновик](tz/05-api-endpoints-draft.md)
- [Глоссарий](tz/06-glossary.md)
- [Backend foundation: план первого технического шага](tz/07-backend-foundation-plan.md)
- [Карта прогресса ТЗ](tz/08-tz-progress-map.md)
- [Роли и права: черновик MVP](tz/09-roles-and-permissions.md)
- [Анализ схем БД аналогов](research/01-ecommerce-schema-analysis.md)
- [Локальная разработка](development/00-local-development.md)
- [Перенос проекта в папку shopsbox](development/01-move-to-shopsbox.md)
- [Demo seed data](development/02-demo-seed-data.md)

## Принятые стартовые направления

- Веб-версия: PHP + Symfony.
- Frontend MVP: Twig + Bootstrap.
- Архитектура: Clean Architecture, DDD, SOLID.
- Инфраструктура на старте: Docker Compose.
- Дальнейшая цель: Kubernetes, когда появится нагрузка и операционная необходимость.
- Мобильные и desktop-приложения: рассмотреть Kotlin Multiplatform.
- Основной коммерческий фокус: managed hosting для простых клиентов, Own Infra как более дорогая и продвинутая опция.
- Прототипирование: без Figma на первом этапе; текстовые прототипы, Mermaid-схемы и при необходимости HTML-прототипы.
- Проектные операции: только через `make`, с Docker-контуром для локальной разработки по мере необходимости.
