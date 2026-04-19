# Поддомены магазинов и выбор store по host

Issue #24 добавляет host-based маршруты витрины.

## Что работает

Старый fallback остается:

- `/s/{storeSlug}`;
- `/s/{storeSlug}/products`;
- `/s/{storeSlug}/products/{productSlug}`.

Новые host-based маршруты:

- `https://{storeSlug}.shopsbox.ru/`;
- `https://{storeSlug}.shopsbox.ru/products`;
- `https://{storeSlug}.shopsbox.ru/products/{productSlug}`.

Для локальной разработки:

- `http://{storeSlug}.shopsbox.local/`;
- `http://{storeSlug}.shopsbox.local/products`;
- `http://{storeSlug}.shopsbox.local/products/{productSlug}`.

## Как выбирается store

На этом шаге `{storeSlug}` из host используется как slug магазина. Дальше use case витрины ищет активный store по slug и не отдает данные неактивного или несуществующего магазина.

Полноценные пользовательские домены клиентов пока не входят в задачу.

## DNS/HTTPS

Для production нужен wildcard DNS:

```text
*.shopsbox.ru -> production server
```

Для HTTPS нужен wildcard certificate или другой согласованный механизм выпуска сертификатов для поддоменов.

## Проверки и тестовое покрытие

Новые unit-тесты не добавлены по текущей договоренности.

Что нужно покрыть позже:

- host `demo-store.shopsbox.ru` открывает demo store;
- неизвестный subdomain возвращает 404;
- неактивный store не отдается;
- fallback `/s/demo-store` продолжает работать;
- локальный host `demo-store.shopsbox.local` работает в dev-контуре.
