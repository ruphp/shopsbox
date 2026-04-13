GITHUB_OWNER=ruphp
GITHUB_REPO=shopsbox
GITHUB_FULL_REPO=$(GITHUB_OWNER)/$(GITHUB_REPO)
GITHUB_PROJECT_NUMBER=2
GITHUB_PROJECT_ID=PVT_kwHOARNVz84BUg6H
GITHUB_PROJECT_STATUS_FIELD_ID=PVTSSF_lAHOARNVz84BUg6HzhBogZg
GITHUB_PROJECT_BACKLOG_VIEW_ID=41851759
GITHUB_PROJECT_STATUS_BACKLOG=92d9ded2
GITHUB_PROJECT_STATUS_IN_PROGRESS=fc3e6bc2
GITHUB_PROJECT_STATUS_DONE=1d39121f
GITHUB_PROJECT_ITEM_ISSUE_1=PVTI_lAHOARNVz84BUg6Hzgp0NUs
GITHUB_PROJECT_ITEM_ISSUE_2=PVTI_lAHOARNVz84BUg6Hzgp0NUk
GITHUB_PROJECT_ITEM_ISSUE_3=PVTI_lAHOARNVz84BUg6Hzgp0NZk
GITHUB_PROJECT_ITEM_ISSUE_4=PVTI_lAHOARNVz84BUg6Hzgp0NUU
GITHUB_PROJECT_ITEM_ISSUE_5=PVTI_lAHOARNVz84BUg6Hzgp0NUg
DOCKER_PROJECT=shopsbox
COMPOSE=docker compose -p $(DOCKER_PROJECT)

.PHONY: help docs-list docs-check github-auth-status github-repo-create github-labels-setup github-labels-russianize github-labels-polish github-default-labels-delete github-milestones-setup github-milestones-russianize github-project-create github-project-link-repo github-project-status-setup github-project-status-russianize github-project-items-status-setup github-project-current-issue-done github-project-next-issue-in-progress github-project-backlog-board-create github-project-backlog-board-ru-create github-project-backlog-board-russianize github-project-old-backlog-board-delete github-issues-create github-issues-russianize github-branch-protect-master github-ruleset-master git-status git-commit-workflow git-commit-management git-commit-gitignore git-branch-issue-2 git-push-master backend-create composer-update up down logs ps backend-shell composer-install migrate test

help:
	@echo ShopsBox make targets:
	@echo   make docs-list   List project documentation files
	@echo   make docs-check  Check documentation files exist
	@echo   make github-auth-status  Check GitHub CLI auth status
	@echo   make github-repo-create  Create private GitHub repo and set origin
	@echo   make github-labels-setup  Create GitHub labels
	@echo   make github-labels-russianize  Rename GitHub labels to Russian
	@echo   make github-labels-polish  Polish renamed GitHub labels
	@echo   make github-default-labels-delete  Delete unused default GitHub labels
	@echo   make github-milestones-setup  Create GitHub milestones
	@echo   make github-milestones-russianize  Rename GitHub milestones to Russian
	@echo   make github-project-create  Create GitHub Project
	@echo   make github-project-link-repo  Link GitHub Project to repository
	@echo   make github-project-status-setup  Set GitHub Project statuses
	@echo   make github-project-status-russianize  Set GitHub Project statuses in Russian
	@echo   make github-project-items-status-setup  Set GitHub Project item statuses
	@echo   make github-project-current-issue-done  Close current issue and set it Done
	@echo   make github-project-next-issue-in-progress  Set issue 2 In progress
	@echo   make github-project-backlog-board-create  Create Backlog board view
	@echo   make github-project-backlog-board-ru-create  Create Russian Backlog board view
	@echo   make github-project-backlog-board-russianize  Rename Backlog board view
	@echo   make github-project-old-backlog-board-delete  Delete old English Backlog board view
	@echo   make github-branch-protect-master  Protect master branch
	@echo   make github-ruleset-master  Protect master branch through ruleset
	@echo   make github-issues-create  Create MVP Foundation GitHub issues
	@echo   make github-issues-russianize  Rename MVP Foundation GitHub issues
	@echo   make git-status  Show git status
	@echo   make git-commit-workflow  Commit GitHub workflow updates
	@echo   make git-commit-management  Commit project management updates
	@echo   make git-commit-gitignore  Commit gitignore updates
	@echo   make git-branch-issue-2  Create task branch for issue 2
	@echo   make git-push-master  Push master to origin
	@echo   make backend-create  Create Symfony skeleton through Docker Composer
	@echo   make composer-update  Update backend Composer dependencies through Docker Composer
	@echo   make up          Start local Docker Compose contour
	@echo   make down        Stop local Docker Compose contour
	@echo   make logs        Show local Docker Compose logs
	@echo   make ps          Show local Docker Compose services
	@echo   make backend-shell  Open shell in backend container
	@echo   make migrate     Run Doctrine migrations when backend is ready
	@echo   make test        Placeholder for future project checks

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
	@if not exist docs\workflow\01-mvp-foundation-issue-drafts.md exit /b 1
	@if not exist docs\workflow\02-current-work-status.md exit /b 1
	@if not exist docs\workflow\03-conversation-map.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\01-backend-foundation.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\02-tenant-foundation.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\03-storage-foundation.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\04-local-operations.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\05-usage-limits-groundwork.md exit /b 1
	@if not exist docs\workflow\github-api\branch-protection-master.json exit /b 1
	@if not exist docs\workflow\github-api\master-ruleset.json exit /b 1
	@if not exist docs\workflow\github-api\update-project-status-field.graphql exit /b 1
	@if not exist docs\workflow\github-api\update-project-status-field-ru.graphql exit /b 1
	@if not exist docs\workflow\github-api\create-backlog-board-view.json exit /b 1
	@if not exist docs\workflow\github-api\rename-backlog-board-view-ru.json exit /b 1
	@if not exist docs\workflow\github-issue-titles\01-backend-foundation.txt exit /b 1
	@if not exist docs\workflow\github-issue-titles\02-tenant-foundation.txt exit /b 1
	@if not exist docs\workflow\github-issue-titles\03-storage-foundation.txt exit /b 1
	@if not exist docs\workflow\github-issue-titles\04-local-operations.txt exit /b 1
	@if not exist docs\workflow\github-issue-titles\05-usage-limits-groundwork.txt exit /b 1
	@if not exist docs\workflow\github-api\issue-01-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\issue-02-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\issue-03-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\issue-04-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\issue-05-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\milestone-01-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\milestone-02-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\milestone-03-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\milestone-04-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\milestone-05-title-ru.json exit /b 1
	@if not exist docs\workflow\github-api\milestone-06-title-ru.json exit /b 1
	@if not exist docs\development\02-demo-seed-data.md exit /b 1
	@echo Documentation skeleton is present.

github-auth-status:
	@gh auth status

github-repo-create:
	@gh repo view $(GITHUB_FULL_REPO) >NUL 2>NUL || gh repo create $(GITHUB_FULL_REPO) --private --description "ShopsBox managed ecommerce platform"
	@git remote get-url origin >NUL 2>NUL || git remote add origin https://github.com/$(GITHUB_FULL_REPO).git

github-labels-setup:
	@gh label create "type: feature" --repo $(GITHUB_FULL_REPO) --color 0E8A16 --description "Feature work" || echo label exists
	@gh label create "type: bug" --repo $(GITHUB_FULL_REPO) --color D73A4A --description "Bug" || echo label exists
	@gh label create "type: docs" --repo $(GITHUB_FULL_REPO) --color 0075CA --description "Documentation" || echo label exists
	@gh label create "type: architecture" --repo $(GITHUB_FULL_REPO) --color 5319E7 --description "Architecture decision" || echo label exists
	@gh label create "type: research" --repo $(GITHUB_FULL_REPO) --color C2E0C6 --description "Research" || echo label exists
	@gh label create "area: backend" --repo $(GITHUB_FULL_REPO) --color 1D76DB --description "Backend" || echo label exists
	@gh label create "area: admin" --repo $(GITHUB_FULL_REPO) --color 1D76DB --description "Admin UI" || echo label exists
	@gh label create "area: storefront" --repo $(GITHUB_FULL_REPO) --color 1D76DB --description "Storefront" || echo label exists
	@gh label create "area: mobile" --repo $(GITHUB_FULL_REPO) --color 1D76DB --description "Mobile" || echo label exists
	@gh label create "area: infra" --repo $(GITHUB_FULL_REPO) --color 1D76DB --description "Infrastructure" || echo label exists
	@gh label create "area: billing" --repo $(GITHUB_FULL_REPO) --color 1D76DB --description "Billing" || echo label exists
	@gh label create "area: security" --repo $(GITHUB_FULL_REPO) --color 1D76DB --description "Security" || echo label exists
	@gh label create "priority: p0" --repo $(GITHUB_FULL_REPO) --color B60205 --description "Critical" || echo label exists
	@gh label create "priority: p1" --repo $(GITHUB_FULL_REPO) --color D93F0B --description "High" || echo label exists
	@gh label create "priority: p2" --repo $(GITHUB_FULL_REPO) --color FBCA04 --description "Medium" || echo label exists
	@gh label create "priority: p3" --repo $(GITHUB_FULL_REPO) --color C5DEF5 --description "Low" || echo label exists

github-labels-russianize:
	@gh label edit "type: feature" --repo $(GITHUB_FULL_REPO) --name "тип: фича" --description "Новая возможность"
	@gh label edit "type: bug" --repo $(GITHUB_FULL_REPO) --name "тип: баг" --description "Ошибка"
	@gh label edit "type: docs" --repo $(GITHUB_FULL_REPO) --name "тип: документация" --description "Документация"
	@gh label edit "type: architecture" --repo $(GITHUB_FULL_REPO) --name "тип: архитектура" --description "Архитектурное решение"
	@gh label edit "type: research" --repo $(GITHUB_FULL_REPO) --name "тип: исследование" --description "Исследование"
	@gh label edit "area: admin" --repo $(GITHUB_FULL_REPO) --name "зона: админка" --description "Админка"
	@gh label edit "area: backend" --repo $(GITHUB_FULL_REPO) --name "зона: бэкенд" --description "Бэкенд"
	@gh label edit "area: storefront" --repo $(GITHUB_FULL_REPO) --name "зона: витрина" --description "Витрина магазина"
	@gh label edit "зона: mobile" --repo $(GITHUB_FULL_REPO) --name "зона: мобильные клиенты" --description "Мобильные клиенты"
	@gh label edit "area: infra" --repo $(GITHUB_FULL_REPO) --name "зона: инфраструктура" --description "Инфраструктура"
	@gh label edit "area: billing" --repo $(GITHUB_FULL_REPO) --name "зона: биллинг" --description "Тарифы, лимиты и учет ресурсов"
	@gh label edit "area: security" --repo $(GITHUB_FULL_REPO) --name "зона: безопасность" --description "Безопасность"
	@gh label edit "priority: p0" --repo $(GITHUB_FULL_REPO) --name "приоритет: p0" --description "Критично"
	@gh label edit "priority: p1" --repo $(GITHUB_FULL_REPO) --name "приоритет: p1" --description "Высокий"
	@gh label edit "priority: p2" --repo $(GITHUB_FULL_REPO) --name "приоритет: p2" --description "Средний"
	@gh label edit "priority: p3" --repo $(GITHUB_FULL_REPO) --name "приоритет: p3" --description "Низкий"

github-labels-polish:
	@gh label edit "зона: mobile" --repo $(GITHUB_FULL_REPO) --name "зона: мобильные клиенты" --description "Мобильные клиенты"

github-default-labels-delete:
	@gh label delete bug --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete documentation --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete duplicate --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete enhancement --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete "good first issue" --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete "help wanted" --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete invalid --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete question --repo $(GITHUB_FULL_REPO) --yes
	@gh label delete wontfix --repo $(GITHUB_FULL_REPO) --yes

github-milestones-setup:
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="00 TZ and Architecture" -f description="Docs, requirements, data model, API contours" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="01 MVP Foundation" -f description="Symfony skeleton, Docker Compose, auth, tenants, roles" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="02 Catalog and Orders" -f description="Catalog, products, categories, cart, orders" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="03 Storefront MVP" -f description="Public storefront and checkout" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="04 Operations" -f description="Domains, SSL, backups, healthchecks, audit logs" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="05 Mobile Preparation" -f description="API stabilization and KMP owner app preparation" >NUL 2>NUL || echo milestone exists

github-milestones-russianize:
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/milestones/1 --input docs\workflow\github-api\milestone-01-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/milestones/2 --input docs\workflow\github-api\milestone-02-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/milestones/3 --input docs\workflow\github-api\milestone-03-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/milestones/4 --input docs\workflow\github-api\milestone-04-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/milestones/5 --input docs\workflow\github-api\milestone-05-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/milestones/6 --input docs\workflow\github-api\milestone-06-title-ru.json

github-project-create:
	@gh project list --owner $(GITHUB_OWNER) --format json | findstr /C:"\"title\":\"ShopsBox\"" >NUL || gh project create --owner $(GITHUB_OWNER) --title "ShopsBox"

github-project-link-repo:
	@gh project link $(GITHUB_PROJECT_NUMBER) --owner $(GITHUB_OWNER) --repo $(GITHUB_REPO)

github-project-status-setup:
	@gh api graphql -F query=@docs\workflow\github-api\update-project-status-field.graphql

github-project-status-russianize:
	@gh api graphql -F query=@docs\workflow\github-api\update-project-status-field-ru.graphql

github-project-items-status-setup:
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_1) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_IN_PROGRESS)
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_2) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_BACKLOG)
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_3) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_BACKLOG)
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_4) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_BACKLOG)
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_5) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_BACKLOG)

github-project-current-issue-done:
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_1) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_DONE)
	@gh issue close 1 --repo $(GITHUB_FULL_REPO) --reason completed

github-project-next-issue-in-progress:
	@gh project item-edit --project-id $(GITHUB_PROJECT_ID) --id $(GITHUB_PROJECT_ITEM_ISSUE_2) --field-id $(GITHUB_PROJECT_STATUS_FIELD_ID) --single-select-option-id $(GITHUB_PROJECT_STATUS_IN_PROGRESS)

github-project-backlog-board-create:
	@gh api -X POST users/$(GITHUB_OWNER)/projectsV2/$(GITHUB_PROJECT_NUMBER)/views --input docs\workflow\github-api\create-backlog-board-view.json

github-project-backlog-board-ru-create:
	@gh api -X POST users/$(GITHUB_OWNER)/projectsV2/$(GITHUB_PROJECT_NUMBER)/views --input docs\workflow\github-api\rename-backlog-board-view-ru.json

github-project-backlog-board-russianize:
	@gh api -X PATCH users/$(GITHUB_OWNER)/projectsV2/$(GITHUB_PROJECT_NUMBER)/views/$(GITHUB_PROJECT_BACKLOG_VIEW_ID) --input docs\workflow\github-api\rename-backlog-board-view-ru.json

github-project-old-backlog-board-delete:
	@gh api -X DELETE users/$(GITHUB_OWNER)/projectsV2/$(GITHUB_PROJECT_NUMBER)/views/$(GITHUB_PROJECT_BACKLOG_VIEW_ID)

github-branch-protect-master:
	@gh api -X PUT repos/$(GITHUB_FULL_REPO)/branches/master/protection --input docs\workflow\github-api\branch-protection-master.json

github-ruleset-master:
	@gh api -X POST repos/$(GITHUB_FULL_REPO)/rulesets --input docs\workflow\github-api\master-ruleset.json

github-issues-create:
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Backend foundation: Symfony skeleton and local Docker" --body-file docs\workflow\github-issue-bodies\01-backend-foundation.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: infra,priority: p1"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Tenant foundation: tenants, stores, users and roles" --body-file docs\workflow\github-issue-bodies\02-tenant-foundation.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: security,priority: p1"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Storage foundation: files via local/S3-compatible abstraction" --body-file docs\workflow\github-issue-bodies\03-storage-foundation.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: infra,priority: p2"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Local operations: scheduler, backups placeholders and healthchecks" --body-file docs\workflow\github-issue-bodies\04-local-operations.md --milestone "01 MVP Foundation" --label "type: feature,area: infra,area: backend,priority: p2"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Usage and limits groundwork" --body-file docs\workflow\github-issue-bodies\05-usage-limits-groundwork.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: billing,priority: p2"

github-issues-russianize:
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/issues/1 --input docs\workflow\github-api\issue-01-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/issues/2 --input docs\workflow\github-api\issue-02-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/issues/3 --input docs\workflow\github-api\issue-03-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/issues/4 --input docs\workflow\github-api\issue-04-title-ru.json
	@gh api -X PATCH repos/$(GITHUB_FULL_REPO)/issues/5 --input docs\workflow\github-api\issue-05-title-ru.json

git-status:
	@git status --short --branch

git-commit-workflow:
	@git add AGENTS.md Makefile docs\workflow
	@git commit -m "Configure GitHub workflow board"

git-commit-management:
	@git add CONTEXT.md Makefile docs\tz\00-technical-spec-draft.md docs\tz\07-backend-foundation-plan.md docs\workflow\02-current-work-status.md docs\workflow\03-conversation-map.md
	@git commit -m "Update project management status"

git-commit-gitignore:
	@git add .gitignore Makefile
	@git commit -m "Ignore local IDE files"

git-branch-issue-2:
	@git switch -c task/02-tenant-foundation

git-push-master:
	@git push -u origin master

backend-create:
	@if exist backend\composer.json (echo Backend skeleton already exists.) else docker run --rm --name shopsbox_composer -v "$(CURDIR):/app" -w /app composer:2 composer create-project symfony/skeleton backend

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
	@echo Application tests are not defined yet.
