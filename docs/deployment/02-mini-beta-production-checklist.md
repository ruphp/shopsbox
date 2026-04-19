# Mini-beta production: закрытый запуск ShopsBox

Документ фиксирует минимальный production-контур для закрытой мини-беты ShopsBox на домене `shopsbox.ru`.

Это еще не полноценный публичный SaaS-запуск. Цель этапа - иметь живой контур, который можно показать, проверить и развивать дальше без публичной регистрации клиентов.

## Текущий контур

- Домен: `shopsbox.ru`.
- HTTPS: включен.
- Backend: Symfony в контейнере `webserver_php_fpm`.
- Web: nginx в контейнере `webserver_nginx`.
- База данных: PostgreSQL в контейнере `webserver_pgsql`.
- Публичная главная: `https://shopsbox.ru/`.
- Healthcheck: `https://shopsbox.ru/health`.
- Демо-витрина: `https://shopsbox.ru/s/demo-store`.
- Список товаров демо-магазина: `https://shopsbox.ru/s/demo-store/products`.
- Карточка товара: `https://shopsbox.ru/s/demo-store/products/demo-hoodie`.

## Что должно быть закрыто перед показом

- Секреты не должны попадать в git.
- `.env`, `.env.local`, `.env.prod.local` и реальные ключи должны жить только на сервере или в защищенном хранилище.
- Перед миграциями и ручными изменениями данных нужно делать backup базы.
- Symfony cache чистить штатно через `bin/console`, а не удалением файлов руками.
- Если после cache clear отдается старый HTML, перезапускать только PHP-FPM контейнер для сброса OPcache.
- Nginx не трогать без отдельной причины.

## Backup перед изменениями

Перед миграциями или ручными изменениями данных:

```bash
cd /home/webserver/shopsbox
mkdir -p var/backups
docker exec webserver_pgsql pg_dump --clean --if-exists -U postgres -d shopsbox > var/backups/shopsbox-before-change.sql
```

Имя backup-файла должно содержать дату, время и причину изменения.

## Миграции

Проверка статуса миграций:

```bash
docker exec -u www-data webserver_php_fpm sh -lc 'cd /var/www/shopsbox/backend && php bin/console doctrine:migrations:status --no-interaction --env=prod'
```

Применение миграций:

```bash
docker exec -u www-data webserver_php_fpm sh -lc 'cd /var/www/shopsbox/backend && php bin/console doctrine:migrations:migrate --no-interaction --env=prod'
```

## Smoke-check после выкладки

Проверить:

```bash
curl -I https://shopsbox.ru/
curl -I https://shopsbox.ru/health
curl -I https://shopsbox.ru/s/demo-store
curl -I https://shopsbox.ru/s/demo-store/products
curl -I https://shopsbox.ru/s/demo-store/products/demo-hoodie
curl -I https://shopsbox.ru/robots.txt
curl -I https://shopsbox.ru/sitemap.xml
```

Ожидаем:

- `200 OK` на главной;
- `200 OK` на `/health`;
- `200 OK` на демо-витрине;
- `200 OK` на списке товаров;
- `200 OK` на карточке товара;
- нет `500 Internal Server Error`;
- нет публичной регистрации клиентов.

## Демо-данные

Для закрытой мини-беты нужен минимальный demo tenant/store:

- tenant: `Demo Tenant`;
- store: `Demo Store`;
- slug магазина: `demo-store`;
- категория: `Demo Category`;
- активные товары: `Demo Hoodie`, `Demo Mug`;
- draft-товар не должен отображаться на публичной витрине.

На текущем production-контуре `DoctrineFixturesBundle` может отсутствовать, поэтому fixtures-команда не считается обязательной частью prod-запуска. Если bundle не установлен, демо-данные можно внести отдельным проверенным SQL-скриптом после backup базы.

## Файлы и MinIO

MinIO можно использовать на одном сервере для разных приложений, но нельзя смешивать файлы в одной неуправляемой куче.

Рекомендуемая граница:

- отдельный bucket для ShopsBox, например `shopsbox-files`;
- отдельный bucket для другого приложения;
- отдельный access key и policy для каждого приложения;
- внутри bucket ShopsBox использовать префиксы: `products/`, `stores/`, `temp/`, `imports/`.

Так проще:

- не отдать ShopsBox доступ к чужим файлам;
- считать storage usage по проекту;
- делать backup/restore;
- позже перенести ShopsBox на отдельный S3-compatible storage.

Для закрытой мини-беты MinIO на production можно оставить как отложенный пункт, если публичная витрина пока не зависит от загрузки новых файлов.

## Что сознательно не открываем

- Публичную регистрацию клиентов.
- Боевые платежи.
- Самостоятельное создание магазинов клиентами.
- Пользовательские домены клиентов.
- Обещание SLA.

Эти пункты должны идти отдельными issues после авторизации владельца магазина и выбора модели размещения.

## Проверки и тестовое покрытие

Для этой задачи новые unit-тесты не нужны: она не добавляет бизнес-правила в Domain/Application слой.

Нужны ручные production-проверки:

- HTTP smoke-check основных страниц;
- проверка `/health`;
- проверка статуса миграций;
- проверка наличия demo tenant/store/products;
- проверка, что секреты не попали в git;
- проверка backup перед изменениями базы.

Functional-тесты публичной витрины лучше добавить позже, когда стабилизируем авторизацию, поддомены и расширенную модель каталога.
