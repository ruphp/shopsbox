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
BACKUP_DIR=var\backups
BACKUP_FILE=$(BACKUP_DIR)\shopsbox-dev.sql
DOCKER_PROJECT=shopsbox
COMPOSE=docker compose -p $(DOCKER_PROJECT)

.PHONY: help docs-list docs-check github-auth-status github-project-current-issue-done github-project-next-issue-in-progress github-pr-create-current github-pr-create-management github-pr-merge-current git-status git-commit git-push-current git-push-master git-switch-master git-pull-master backend-create composer-require composer-require-dev composer-update composer-update-lock up down logs ps backend-shell health-check db-dump db-restore composer-install migrate fixtures-load backend-check unit-test test

help:
	@echo Make targets for ShopsBox:
	@echo   make docs-list
	@echo   make docs-check
	@echo   make git-status
	@echo   make git-commit COMMIT_MESSAGE="message"
	@echo   make git-push-current
	@echo   make github-auth-status
	@echo   make github-pr-create-current HEAD_BRANCH="branch" PR_TITLE="title" PR_BODY_FILE="path"
	@echo   make github-pr-merge-current
	@echo   make composer-install
	@echo   make composer-require PACKAGES="vendor/package"
	@echo   make composer-require-dev PACKAGES="vendor/package"
	@echo   make composer-update
	@echo   make composer-update-lock
	@echo   make migrate
	@echo   make fixtures-load
	@echo   make backend-check
	@echo   make unit-test
	@echo   make test
	@echo   make up
	@echo   make down
	@echo   make logs
	@echo   make ps
	@echo   make backend-shell
	@echo   make health-check
	@echo   make db-dump
	@echo   make db-restore BACKUP_FILE="var\backups\shopsbox-dev.sql"
	@echo   make git-switch-master
	@echo   make git-pull-master
	@echo   make git-push-master

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
	@if not exist docs\workflow\github-pr-bodies\tenant-foundation.md exit /b 1
	@if not exist docs\development\02-demo-seed-data.md exit /b 1
	@if not exist docs\development\03-tenant-foundation-implementation.md exit /b 1
	@if not exist docs\development\04-file-storage-foundation.md exit /b 1
	@if not exist docs\development\05-local-operations-foundation.md exit /b 1
	@echo Documentation skeleton is present.

github-auth-status:
	@gh auth status

github-project-current-issue-done:
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_1) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_DONE)
	@gh issue close 1 --repo $(GITHUB_FULL_REPO) --reason completed

github-project-next-issue-in-progress:
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_2) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_IN_PROGRESS)

github-pr-create-current:
	@gh pr create --repo $(GITHUB_FULL_REPO) --base master --head "$(HEAD_BRANCH)" --title "$(PR_TITLE)" --body-file "$(PR_BODY_FILE)"

github-pr-create-management:
	@gh pr create --repo $(GITHUB_FULL_REPO) --base master --head task/02-tenant-foundation --title "Management updates before task 2" --body-file docs\workflow\github-pr-bodies\project-management-and-gitignore.md

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

health-check:
	@curl -fsS http://localhost:8080/health

db-dump:
	@if not exist $(BACKUP_DIR) mkdir $(BACKUP_DIR)
	@$(COMPOSE) exec -T postgres pg_dump --clean --if-exists -U shopsbox -d shopsbox > $(BACKUP_FILE)
	@echo Database dump written to $(BACKUP_FILE)

db-restore:
	@if not exist "$(BACKUP_FILE)" (echo BACKUP_FILE not found: $(BACKUP_FILE) && exit /b 1)
	@$(COMPOSE) exec -T postgres psql -U shopsbox -d shopsbox < "$(BACKUP_FILE)"
	@echo Database restored from $(BACKUP_FILE)

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
