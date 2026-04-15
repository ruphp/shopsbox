GITHUB_OWNER=ruphp
GITHUB_REPO=shopsbox
GITHUB_FULL_REPO=$(GITHUB_OWNER)/$(GITHUB_REPO)
GITHUB_PROJECT_NUMBER=2
GITHUB_PROJECT_ID=PVT_kwHOARNVz84BUg6H
GITHUB_PROJECT_STATUS_FIELD_ID=PVTSSF_lAHOARNVz84BUg6HzhBogZg
GITHUB_PROJECT_STATUS_BACKLOG=92d9ded2
GITHUB_PROJECT_STATUS_IN_PROGRESS=fc3e6bc2
GITHUB_PROJECT_STATUS_DONE=1d39121f
GITHUB_PROJECT_ITEM_ISSUE_1=PVTI_lAHOARNVz84BUg6Hzgp0NUs
GITHUB_PROJECT_ITEM_ISSUE_2=PVTI_lAHOARNVz84BUg6Hzgp0NUk
DOCKER_PROJECT=shopsbox
COMPOSE=docker compose -p $(DOCKER_PROJECT)

.PHONY: help docs-list docs-check github-auth-status github-project-current-issue-done github-project-next-issue-in-progress github-pr-create-management github-pr-merge-current git-status git-commit git-push-current git-push-master git-switch-master git-pull-master backend-create composer-require composer-require-dev composer-update composer-update-lock up down logs ps backend-shell composer-install migrate fixtures-load backend-check unit-test test

help:
	@echo Цели make для ShopsBox:
	@echo Ежедневная работа:
	@echo   make docs-list   Показать файлы документации
	@echo   make docs-check  Проверить наличие ключевых документов
	@echo   make git-status  Показать статус git
	@echo   make git-push-current  Отправить текущую ветку в origin
	@echo   make backend-create  Создать каркас Symfony через Docker Composer
	@echo   make composer-require PACKAGES="vendor/package"  Добавить Composer-зависимости через backend-контейнер
	@echo   make composer-require-dev PACKAGES="vendor/package"  Добавить dev Composer-зависимости через backend-контейнер
	@echo   make composer-update  Обновить зависимости Composer через backend-контейнер
	@echo   make composer-update-lock  Обновить lock без Symfony scripts через backend-контейнер
	@echo   make up          Поднять локальный Docker Compose контур
	@echo   make down        Остановить локальный Docker Compose контур
	@echo   make logs        Показать логи локального Docker Compose контура
	@echo   make ps          Показать сервисы локального Docker Compose контура
	@echo   make backend-shell  Открыть shell backend-контейнера
	@echo   make migrate     Запустить миграции Doctrine, когда backend готов
	@echo   make fixtures-load  Загрузить demo fixtures в локальную БД
	@echo   make backend-check  Проверить Symfony container и Doctrine mapping
	@echo   make unit-test  Запустить unit-тесты backend
	@echo   make test        Запустить документационные и backend-проверки
	@echo Управление задачами GitHub:
	@echo   make github-auth-status  Проверить вход в GitHub CLI
	@echo   make github-project-current-issue-done  Закрыть текущую задачу и перевести карточку в Готово
	@echo   make github-project-next-issue-in-progress  Перевести задачу 2 в работу
	@echo   make github-pr-create-management  Создать PR с управленческими обновлениями
	@echo   make github-pr-merge-current  Слить текущий PR
	@echo   make git-commit COMMIT_MESSAGE="текст"  Закоммитить текущие изменения
	@echo Редкие административные операции:
	@echo   make git-switch-master  Переключиться на master
	@echo   make git-pull-master  Обновить master из origin
	@echo   make git-push-master  Отправить master в origin

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
	@if not exist docs\workflow\github-pr-bodies\project-management-and-gitignore.md exit /b 1
	@if not exist docs\development\02-demo-seed-data.md exit /b 1
	@if not exist docs\development\03-tenant-foundation-implementation.md exit /b 1
	@echo Documentation skeleton is present.

github-auth-status:
	@gh auth status

github-project-current-issue-done:
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_1) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_DONE)
	@gh issue close 1 --repo $(GITHUB_FULL_REPO) --reason completed

github-project-next-issue-in-progress:
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_2) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_IN_PROGRESS)

github-pr-create-management:
	@gh pr create --repo $(GITHUB_FULL_REPO) --base master --head task/02-tenant-foundation --title "Управленческие обновления перед задачей 2" --body-file docs\workflow\github-pr-bodies\project-management-and-gitignore.md

github-pr-merge-current:
	@gh pr merge --merge

git-status:
	@git status --short --branch

git-commit:
	@git add -A
	@git commit -m "$(COMMIT_MESSAGE)"

git-push-current:
	@git push -u origin HEAD

git-push-master:
	@git push -u origin master

git-switch-master:
	@git switch master

git-pull-master:
	@git pull --ff-only origin master

backend-create:
	@if exist backend\composer.json (echo Backend skeleton already exists.) else docker run --rm --name shopsbox_composer -v "$(CURDIR):/app" -w /app composer:2 composer create-project symfony/skeleton backend

composer-require:
	@$(COMPOSE) run --rm backend composer require $(PACKAGES)

composer-require-dev:
	@$(COMPOSE) run --rm backend composer require --dev $(PACKAGES)

composer-update:
	@$(COMPOSE) run --rm backend composer update

composer-update-lock:
	@$(COMPOSE) run --rm backend composer update --with-all-dependencies --no-scripts

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

fixtures-load:
	@$(COMPOSE) run --rm backend php bin/console doctrine:fixtures:load --no-interaction

backend-check:
	@$(COMPOSE) run --rm backend php bin/console lint:container
	@$(COMPOSE) run --rm backend php bin/console doctrine:schema:validate --skip-sync

unit-test:
	@$(COMPOSE) run --rm backend php bin/phpunit --testsuite "Project Test Suite"

test: docs-check backend-check unit-test
