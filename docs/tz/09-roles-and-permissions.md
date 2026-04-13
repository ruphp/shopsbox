# Роли и права: черновик MVP

Документ фиксирует стартовую матрицу ролей для ShopsBox. Это не финальная ACL-модель, а практичная основа для первого backend foundation.

## Подход

Для MVP берем не сложную enterprise-модель, а понятные роли:

- владелец магазина управляет своим магазином;
- сотрудники получают ограниченный доступ по зонам работы;
- оператор платформы управляет tenants/stores и операционными задачами;
- покупатель не имеет доступа в админку.

Ориентиры по конкурентам:

- Shopify использует роли как наборы granular permissions для работы staff в разных областях админки: products, orders, customers, files, online store, settings, finance.
- WooCommerce разделяет customer и shop manager; shop manager может управлять магазином без полного admin-доступа WordPress.

Для ShopsBox это означает: не давать всем роль "админ", а разделять права по рабочим сценариям.

## Роли MVP

| Роль | Где работает | Смысл |
| --- | --- | --- |
| `platform_admin` | Супер-админка | Полный доступ к платформе, настройкам, tenants/stores, тарифам и техническим операциям. |
| `platform_operator` | Супер-админка | Операционная работа: клиенты, магазины, статус, бэкапы, обращения, без финансовых и опасных системных настроек. |
| `store_owner` | Админка магазина | Владелец магазина: полный доступ в пределах своего store. |
| `store_manager` | Админка магазина | Управляющий магазином: заказы, товары, клиенты, остатки, базовые настройки, без тарифов и опасных техопераций. |
| `order_manager` | Админка магазина | Обработка заказов, статусов, комментариев, покупателей. |
| `catalog_manager` | Админка магазина | Товары, категории, изображения, публикация. |
| `inventory_manager` | Админка магазина | Остатки, склады, резервы, SKU/barcode в пределах складского сценария. |
| `support_agent` | Супер-админка или админка магазина | Поддержка: просмотр статусов и обращений, без изменения бизнес-данных без отдельного права. |
| `customer` | Публичная витрина | Покупатель: профиль, корзина, свои заказы и адреса. |

## Матрица прав

Обозначения:

- `R` - просмотр.
- `W` - изменение.
- `A` - административные/опасные действия.
- `-` - нет доступа.

| Зона | platform_admin | platform_operator | store_owner | store_manager | order_manager | catalog_manager | inventory_manager | support_agent | customer |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Tenants | A | R/W | - | - | - | - | - | R | - |
| Stores | A | R/W | R/W own | R own | - | - | - | R | - |
| Users and roles | A | R/W platform | R/W own store | R own store | - | - | - | R | - |
| Products | - | R | A own store | R/W own store | R | R/W own store | R | R | R published |
| Categories | - | R | A own store | R/W own store | R | R/W own store | R | R | R published |
| Files/images | - | R | A own store | R/W own store | - | R/W own store | - | R | R published |
| Inventory | - | R | A own store | R/W own store | R | R | R/W own store | R | R availability |
| Orders | - | R | A own store | R/W own store | R/W own store | - | R | R | R own |
| Customers | - | R | A own store | R/W own store | R/W own store | - | - | R | R/W own profile |
| Storefront settings | - | R | A own store | R/W own store | - | R/W content | - | R | R published |
| Store technical status | A | R/W ops | R own store | R own store | - | - | - | R | - |
| Backups and restore | A | R/W ops | Request/R own store | Request/R own store | - | - | - | R | - |
| Billing and plan | A | R | A own store | R own store | - | - | - | - | - |
| Audit log | A | R | R own store | R own store | - | - | - | R | - |
| Support tickets | A | R/W | R/W own store | R/W own store | R/W own store | R/W own store | R/W own store | R/W | - |

## MVP permission codes

Стартовые коды можно держать простыми:

- `platform.tenants.manage`
- `platform.stores.manage`
- `platform.ops.manage`
- `store.settings.manage`
- `store.users.manage`
- `store.products.manage`
- `store.inventory.manage`
- `store.orders.manage`
- `store.customers.manage`
- `store.storefront.manage`
- `store.files.manage`
- `store.backups.request`
- `store.billing.manage`
- `store.audit.view`
- `support.tickets.manage`

На первом этапе можно хранить роли в БД, а permissions описать в коде или seed-конфигурации. Не нужно сразу делать сложный UI-конструктор прав.

## Ограничения MVP

- Не делать полноценную enterprise ACL.
- Не делать nested roles и inheritance.
- Не давать `store_manager` доступ к тарифам, платежным данным ShopsBox и опасным техническим операциям.
- Не давать `support_agent` изменение заказов и товаров без отдельного явного права.
- Не смешивать `customer` и admin users в интерфейсе, даже если физически они позже окажутся в одной таблице.

## Источники и адаптация

- Shopify roles and store permissions: https://help.shopify.com/en/manual/your-account/users/roles и https://help.shopify.com/en/manual/your-account/users/roles/permissions/store-permissions
- WooCommerce roles and capabilities: https://woocommerce.com/document/roles-capabilities/

Вывод для ShopsBox: берем granular permissions по рабочим зонам как у Shopify, но оставляем простые предустановленные роли как у малого ecommerce MVP. Из WooCommerce берем идею "manager без полного admin-доступа", но не копируем WordPress-роли.
