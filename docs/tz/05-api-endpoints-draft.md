# API endpoints: черновик

API проектируется так, чтобы web-админка, mobile owner app и desktop-клиент могли использовать один backend.

## Auth

- `POST /api/auth/login`
- `POST /api/auth/code/request`
- `POST /api/auth/code/verify`
- `POST /api/auth/logout`
- `POST /api/auth/refresh`
- `GET /api/auth/me`

Для входа по одноразовому коду backend должен поддержать SMS/email delivery через application-интерфейс и infrastructure-адаптеры. В dev-режиме SMS/email можно имитировать и показывать код во flash-сообщении, но истекший или уже использованный код всегда должен отклоняться. Подробности: [Auth code delivery](10-auth-code-delivery.md).

## Platform admin

- `GET /api/platform/tenants`
- `POST /api/platform/tenants`
- `GET /api/platform/tenants/{tenantId}`
- `PATCH /api/platform/tenants/{tenantId}`
- `GET /api/platform/stores`
- `POST /api/platform/stores`
- `GET /api/platform/stores/{storeId}`
- `PATCH /api/platform/stores/{storeId}`
- `GET /api/platform/stores/{storeId}/health`
- `POST /api/platform/stores/{storeId}/backups`
- `POST /api/platform/stores/{storeId}/restore`

## Store admin: catalog

- `GET /api/stores/{storeId}/products`
- `POST /api/stores/{storeId}/products`
- `GET /api/stores/{storeId}/products/{productId}`
- `PATCH /api/stores/{storeId}/products/{productId}`
- `DELETE /api/stores/{storeId}/products/{productId}`
- `POST /api/stores/{storeId}/products/{productId}/images`
- `PATCH /api/stores/{storeId}/products/{productId}/inventory`

## Store admin: categories

- `GET /api/stores/{storeId}/categories`
- `POST /api/stores/{storeId}/categories`
- `GET /api/stores/{storeId}/categories/{categoryId}`
- `PATCH /api/stores/{storeId}/categories/{categoryId}`
- `DELETE /api/stores/{storeId}/categories/{categoryId}`

## Store admin: orders

- `GET /api/stores/{storeId}/orders`
- `GET /api/stores/{storeId}/orders/{orderId}`
- `PATCH /api/stores/{storeId}/orders/{orderId}/status`
- `POST /api/stores/{storeId}/orders/{orderId}/comment`

## Store admin: customers

- `GET /api/stores/{storeId}/customers`
- `GET /api/stores/{storeId}/customers/{customerId}`
- `PATCH /api/stores/{storeId}/customers/{customerId}`

## Store admin: settings

- `GET /api/stores/{storeId}/settings`
- `PATCH /api/stores/{storeId}/settings`
- `GET /api/stores/{storeId}/domains`
- `POST /api/stores/{storeId}/domains`
- `GET /api/stores/{storeId}/ssl`
- `POST /api/stores/{storeId}/ssl/renew`

## Store admin: storefront settings

- `GET /api/stores/{storeId}/storefront/settings`
- `PATCH /api/stores/{storeId}/storefront/settings`
- `GET /api/stores/{storeId}/storefront/theme`
- `PATCH /api/stores/{storeId}/storefront/theme`
- `POST /api/stores/{storeId}/storefront/publish`

Эти endpoints меняют конфигурацию витрины: логотип, цвета, меню, блоки, контакты, SEO и публикацию. Они не должны выполнять произвольный код и не должны напрямую редактировать CSS/JS файлы клиента без отдельного deployment-механизма.

## Storefront

- `GET /api/storefront/{storeSlug}`
- `GET /api/storefront/{storeSlug}/categories`
- `GET /api/storefront/{storeSlug}/products`
- `GET /api/storefront/{storeSlug}/products/{productSlug}`
- `POST /api/storefront/{storeSlug}/cart`
- `PATCH /api/storefront/{storeSlug}/cart/{cartId}`
- `POST /api/storefront/{storeSlug}/checkout`
- `GET /api/storefront/{storeSlug}/orders/{orderNumber}`

## Own Infra agent

- `POST /api/agent/register`
- `POST /api/agent/heartbeat`
- `POST /api/agent/sync/catalog`
- `POST /api/agent/sync/orders`
- `POST /api/agent/sync/storefront-settings`
- `POST /api/agent/commands`
- `POST /api/agent/events`

Agent endpoints требуют отдельной модели безопасности: подписи запросов, ограниченные токены, журналирование и rotate secrets.
