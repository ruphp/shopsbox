# Tenant foundation

## Что изменилось

- Добавлен модуль `Tenant` в стиле модульного монолита: `Domain`, `Application`, `Infrastructure`, `Presentation`.
- Добавлены Doctrine migration и persistence-entity для tenants, stores, users, roles и role assignments.
- Добавлены demo fixtures для локального tenant/store/users/roles.
- Перенесен healthcheck в `System/Presentation/Http/Controller`.
- Добавлен первый application use case `CreateTenant` и HTTP endpoint `POST /tenants`.
- Application слой очищен от прямой зависимости на Doctrine entity: Doctrine остается в Infrastructure.
- Добавлены unit-тесты для `CreateTenantUseCase`.

## Почему

Задача #2 закладывает основу tenant/store/user/role модели для ShopsBox без преждевременного создания других модулей. Код остается одним Symfony backend, но разложен по доменным модулям и слоям.

## Проверки

- `make composer-install`
- `make migrate`
- `make fixtures-load`
- `make test`
- `/health` проверен внутри backend-контейнера

Результат unit-тестов: `7 tests, 22 assertions`.

Integration/functional тесты для повторного `POST /tenants` с тем же `store_domain` пока не добавлены осознанно. Этот уровень тестов договорились разобрать позже на более живом пользовательском сценарии с тестовой БД и HTTP/API-контрактом.

