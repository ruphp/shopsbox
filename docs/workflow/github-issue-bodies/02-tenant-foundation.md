# Цель

Создать базовый foundation-слой multi-tenant модели: tenants, stores, users, roles и user_roles.

# Контекст

ShopsBox использует MVP multi-tenancy: shared app + shared DB + `tenant_id` / `store_id`. Foundation-слой должен задать границы данных до каталога и заказов.

# Требования

- Создать миграции для `tenants`, `stores`, `users`, `roles`, `user_roles`.
- Учесть `tenant_id` / `store_id` boundaries.
- Добавить seed-данные для локального demo tenant/store/users.
- Использовать роли из `docs/tz/09-roles-and-permissions.md`.
- Не делать enterprise ACL и UI-конструктор прав на этом этапе.

# Критерии приемки

- Миграции применяются через `make migrate`.
- Demo tenant и demo store создаются сидером или fixture.
- Demo users создаются только для локальной разработки.
- Пароли не используются как production-секреты.
