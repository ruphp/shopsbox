# Demo seed data

Документ фиксирует локальные demo-данные для будущего сидера. Это не production-секреты и не реальные учетные записи.

## Важно про пароли

Пароли ниже нужны только для локальной разработки и демо-сценариев. Их нельзя использовать в production, на публичном сервере или в реальном клиентском магазине.

Когда появится приложение:

- demo-пароли должны попадать в локальный seed/fixture;
- production-пароли должны задаваться через безопасный reset/invite flow;
- реальные секреты должны храниться в `.env.local`, secret storage или password manager, а не в документации.

## Demo tenant and store

Tenant:

- name: `Demo Tenant`
- status: `active`
- billing_email: `billing@demo.shopsbox.local`

Store:

- tenant: `Demo Tenant`
- name: `Demo Store`
- slug: `demo-store`
- domain: `demo.shopsbox.local`
- status: `active`
- default_currency: `RUB`
- timezone: `Asia/Yekaterinburg`

## Demo users

| Role | Email | Password | Назначение |
| --- | --- | --- | --- |
| `platform_admin` | `platform-admin@shopsbox.local` | `dev-platform-admin-ChangeMe-2026` | Полный локальный доступ к платформе. |
| `platform_operator` | `platform-operator@shopsbox.local` | `dev-platform-operator-ChangeMe-2026` | Локальная проверка операций платформы. |
| `store_owner` | `owner@demo.shopsbox.local` | `dev-store-owner-ChangeMe-2026` | Владелец demo-магазина. |
| `store_manager` | `manager@demo.shopsbox.local` | `dev-store-manager-ChangeMe-2026` | Управляющий demo-магазином. |
| `order_manager` | `orders@demo.shopsbox.local` | `dev-orders-ChangeMe-2026` | Проверка сценариев заказов. |
| `catalog_manager` | `catalog@demo.shopsbox.local` | `dev-catalog-ChangeMe-2026` | Проверка сценариев каталога. |
| `inventory_manager` | `inventory@demo.shopsbox.local` | `dev-inventory-ChangeMe-2026` | Проверка сценариев остатков. |
| `support_agent` | `support@shopsbox.local` | `dev-support-ChangeMe-2026` | Проверка поддержки и просмотра техстатусов. |
| `customer` | `customer@demo.shopsbox.local` | `dev-customer-ChangeMe-2026` | Проверка покупательского профиля и заказов. |

## Seed rules

- Все demo users должны быть явно помечены как dev/demo.
- Для admin users нужен `tenant_id`; для store roles нужен `store_id` в `user_roles`.
- `customer` относится к demo store как покупатель и не должен получать доступ в admin routes.
- Пароли должны хэшироваться стандартным механизмом Symfony, как только появится код приложения.
- При переносе на production эти записи не создавать автоматически.
