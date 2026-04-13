GITHUB_OWNER=ruphp
GITHUB_REPO=shopsbox
GITHUB_FULL_REPO=$(GITHUB_OWNER)/$(GITHUB_REPO)
DOCKER_PROJECT=shopsbox
COMPOSE=docker compose -p $(DOCKER_PROJECT)

.PHONY: help docs-list docs-check github-auth-status github-repo-create github-labels-setup github-milestones-setup github-project-create github-issues-create backend-create composer-update up down logs ps backend-shell composer-install migrate test

help:
	@echo ShopsBox make targets:
	@echo   make docs-list   List project documentation files
	@echo   make docs-check  Check documentation files exist
	@echo   make github-auth-status  Check GitHub CLI auth status
	@echo   make github-repo-create  Create private GitHub repo and set origin
	@echo   make github-labels-setup  Create GitHub labels
	@echo   make github-milestones-setup  Create GitHub milestones
	@echo   make github-project-create  Create GitHub Project
	@echo   make github-issues-create  Create MVP Foundation GitHub issues
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
	@if not exist docs\workflow\github-issue-bodies\01-backend-foundation.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\02-tenant-foundation.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\03-storage-foundation.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\04-local-operations.md exit /b 1
	@if not exist docs\workflow\github-issue-bodies\05-usage-limits-groundwork.md exit /b 1
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

github-milestones-setup:
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="00 TZ and Architecture" -f description="Docs, requirements, data model, API contours" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="01 MVP Foundation" -f description="Symfony skeleton, Docker Compose, auth, tenants, roles" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="02 Catalog and Orders" -f description="Catalog, products, categories, cart, orders" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="03 Storefront MVP" -f description="Public storefront and checkout" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="04 Operations" -f description="Domains, SSL, backups, healthchecks, audit logs" >NUL 2>NUL || echo milestone exists
	@gh api repos/$(GITHUB_FULL_REPO)/milestones -f title="05 Mobile Preparation" -f description="API stabilization and KMP owner app preparation" >NUL 2>NUL || echo milestone exists

github-project-create:
	@gh project list --owner $(GITHUB_OWNER) --format json | findstr /C:"\"title\":\"ShopsBox\"" >NUL || gh project create --owner $(GITHUB_OWNER) --title "ShopsBox"

github-issues-create:
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Backend foundation: Symfony skeleton and local Docker" --body-file docs\workflow\github-issue-bodies\01-backend-foundation.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: infra,priority: p1"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Tenant foundation: tenants, stores, users and roles" --body-file docs\workflow\github-issue-bodies\02-tenant-foundation.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: security,priority: p1"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Storage foundation: files via local/S3-compatible abstraction" --body-file docs\workflow\github-issue-bodies\03-storage-foundation.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: infra,priority: p2"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Local operations: scheduler, backups placeholders and healthchecks" --body-file docs\workflow\github-issue-bodies\04-local-operations.md --milestone "01 MVP Foundation" --label "type: feature,area: infra,area: backend,priority: p2"
	@gh issue create --repo $(GITHUB_FULL_REPO) --title "Usage and limits groundwork" --body-file docs\workflow\github-issue-bodies\05-usage-limits-groundwork.md --milestone "01 MVP Foundation" --label "type: feature,area: backend,area: billing,priority: p2"

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
