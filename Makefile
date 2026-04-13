DOCKER_PROJECT=shopsbox
COMPOSE=docker compose -p $(DOCKER_PROJECT)

.PHONY: help docs-list docs-check git-status git-push-current git-switch-master git-pull-master backend-create composer-update up down logs ps backend-shell composer-install migrate test

help:
	@echo Цели make для ShopsBox:
	@echo Ежедневная работа:
	@echo   make docs-list          Показать файлы документации
	@echo   make docs-check         Проверить наличие ключевых документов
	@echo   make git-status         Показать статус git
	@echo   make git-push-current   Отправить текущую ветку в origin
	@echo Локальный контур:
	@echo   make backend-create     Создать каркас Symfony через Docker Composer
	@echo   make composer-update    Обновить зависимости Composer через Docker Composer
	@echo   make up                 Поднять локальный Docker Compose контур
	@echo   make down               Остановить локальный Docker Compose контур
	@echo   make logs               Показать логи локального Docker Compose контура
	@echo   make ps                 Показать сервисы локального Docker Compose контура
	@echo   make backend-shell      Открыть shell backend-контейнера
	@echo   make composer-install   Установить зависимости Composer в backend-контейнере
	@echo   make migrate            Запустить миграции Doctrine, когда backend готов
	@echo   make test               Запустить текущие проверки проекта
	@echo Редкие операции:
	@echo   make git-switch-master  Переключиться на master
	@echo   make git-pull-master    Обновить master из origin

docs-list:
	@dir /s /b docs\*.md

docs-check:
	@if not exist AGENTS.md exit /b 1
	@if not exist CONTEXT.md exit /b 1
	@if not exist compose.yaml exit /b 1
	@if not exist docker\php\Dockerfile exit /b 1
	@if not exist docs\README.md exit /b 1
	@if not exist docs\architecture\06-resource-usage-and-limits.md exit /b 1
	@if not exist docs\architecture\07-infrastructure-scaling-model.md exit /b 1
	@if not exist docs\tz\00-technical-spec-draft.md exit /b 1
	@if not exist docs\tz\07-backend-foundation-plan.md exit /b 1
	@if not exist docs\tz\08-tz-progress-map.md exit /b 1
	@if not exist docs\tz\09-roles-and-permissions.md exit /b 1
	@if not exist docs\workflow\00-github-workflow.md exit /b 1
	@if not exist docs\workflow\02-current-work-status.md exit /b 1
	@if not exist docs\workflow\03-conversation-map.md exit /b 1
	@if not exist docs\development\02-demo-seed-data.md exit /b 1
	@echo Документационный каркас на месте.

git-status:
	@git status --short --branch

git-push-current:
	@git push -u origin HEAD

git-switch-master:
	@git switch master

git-pull-master:
	@git pull --ff-only origin master

backend-create:
	@if exist backend\composer.json (echo Каркас backend уже существует.) else docker run --rm --name shopsbox_composer -v "$(CURDIR):/app" -w /app composer:2 composer create-project symfony/skeleton backend

composer-update:
	@docker run --rm --name shopsbox_composer -v "$(CURDIR):/app" -w /app/backend composer:2 composer update

up:
	@$(COMPOSE) up -d --build

down:
	@$(COMPOSE) down

logs:
	@$(COMPOSE) logs --tail=100

ps:
	@$(COMPOSE) ps

backend-shell:
	@$(COMPOSE) exec backend sh

composer-install:
	@$(COMPOSE) run --rm backend composer install

migrate:
	@$(COMPOSE) run --rm backend php bin/console doctrine:migrations:migrate --no-interaction

test: docs-check
	@echo Прикладные тесты пока не заведены.
