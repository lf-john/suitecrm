# SuiteCRM v8 Docker Development Makefile

# Default environment file
ENV_FILE = .env
ifneq (,$(wildcard $(ENV_FILE)))
    include $(ENV_FILE)
    export
endif

# Default values
PROJECT_NAME ?= suitecrm-v8
HTTP_PORT ?= 8080
HTTPS_PORT ?= 8443

.PHONY: help build up down restart logs shell db-shell clean install backup restore

# Default target
help: ## Show this help message
	@echo "SuiteCRM v8 Docker Management"
	@echo "============================="
	@echo ""
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Build all Docker containers
	@echo "Building SuiteCRM v8 Docker containers..."
	docker-compose build

rebuild-suitecrm: ## Rebuild SuiteCRM container with updated scripts
	@echo "Rebuilding SuiteCRM container with updated entrypoint script..."
	docker-compose build  suitecrm
	@echo "✅ SuiteCRM container rebuilt successfully"
	@echo "🔄 Restarting SuiteCRM service..."
	docker-compose restart suitecrm
	@echo "✅ SuiteCRM service restarted with updated script"

up: ## Start all services
	@echo "Starting SuiteCRM v8 services..."
	docker-compose up -d
	@echo ""
	@echo "🎉 SuiteCRM v8 is starting up!"
	@echo "📊 Services will be available at:"
	@echo "   • SuiteCRM:      http://localhost:$(HTTP_PORT)"
	@echo "   • SuiteCRM SSL:  https://localhost:$(HTTPS_PORT)"
	@echo "   • PHPMyAdmin:    http://localhost:8083"
	@echo "   • Redis CMD:     http://localhost:8084"
	@echo "   • MySQL Port:    localhost:3306"
	@echo ""
	@echo "⏳ Please wait a few moments for all services to initialize..."

up-build: ## Build and start all services
	@echo "Building and starting SuiteCRM v8..."
	docker-compose up -d --build

down: ## Stop all services
	@echo "Stopping SuiteCRM v8 services..."
	docker-compose down

restart: ## Restart all services
	@echo "Restarting SuiteCRM v8 services..."
	docker-compose restart

stop: ## Stop all services without removing containers
	@echo "Stopping SuiteCRM v8 services..."
	docker-compose stop

logs: ## Show logs from all services
	docker-compose logs -f

logs-app: ## Show SuiteCRM application logs
	docker-compose logs -f suitecrm

logs-web: ## Show Nginx web server logs
	docker-compose logs -f nginx

logs-db: ## Show database logs
	docker-compose logs -f db

shell: ## Access SuiteCRM container shell
	docker-compose exec suitecrm bash

shell-root: ## Access SuiteCRM container shell as root
	docker-compose exec --user root suitecrm bash

db-shell: ## Access MySQL database shell
	docker-compose exec db mysql -u suitecrm_user -p suitecrm_db

redis-cli: ## Access Redis CLI
	docker-compose exec redis redis-cli

clean: ## Remove all containers, volumes and images
	@echo "⚠️  This will remove all containers, volumes, and data!"
	@read -p "Are you sure? [y/N] " -r; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker-compose down -v --rmi all; \
		docker system prune -f; \
		echo "✅ Cleanup completed"; \
	else \
		echo "❌ Cleanup cancelled"; \
	fi

install: ## Install SuiteCRM v8 (run after 'make up')
	@echo "Installing SuiteCRM v8..."
	chmod +x scripts/install-suitecrm-v8.sh
	./scripts/install-suitecrm-v8.sh

post-install: ## Run post-installation configuration
	@echo "Running post-installation setup..."
	chmod +x scripts/post-install-setup.sh
	./scripts/post-install-setup.sh

verify: ## Verify SuiteCRM installation
	@echo "Verifying SuiteCRM installation..."
	chmod +x scripts/verify-installation.sh
	./scripts/verify-installation.sh

full-install: up install post-install verify ## Complete installation process
	@echo "🎉 SuiteCRM v8 installation completed!"

backup: ## Create backup of database and files
	@echo "Creating backup..."
	mkdir -p backups
	docker-compose exec -T db mysqldump -u suitecrm_user -psuitecrm_password suitecrm_db > backups/suitecrm_$(shell date +%Y%m%d_%H%M%S).sql
	docker-compose exec -T suitecrm tar -czf - /var/www/html/upload > backups/uploads_$(shell date +%Y%m%d_%H%M%S).tar.gz
	@echo "✅ Backup completed in backups/ directory"

restore: ## Restore from backup (usage: make restore BACKUP_DATE=20240101_120000)
	@if [ -z "$(BACKUP_DATE)" ]; then \
		echo "❌ Please specify BACKUP_DATE. Usage: make restore BACKUP_DATE=20240101_120000"; \
		exit 1; \
	fi
	@echo "Restoring from backup $(BACKUP_DATE)..."
	docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db < backups/suitecrm_$(BACKUP_DATE).sql
	docker-compose exec -T suitecrm tar -xzf - -C / < backups/uploads_$(BACKUP_DATE).tar.gz
	@echo "✅ Restore completed"

status: ## Show status of all services
	@echo "SuiteCRM v8 Service Status:"
	@echo "=========================="
	docker-compose ps

setup-env: ## Copy environment template
	@if [ ! -f .env ]; then \
		cp env.example .env; \
		echo "✅ Created .env file from template"; \
		echo "📝 Please review and update .env file with your settings"; \
	else \
		echo "⚠️  .env file already exists"; \
	fi

dev: setup-env up ## Quick development setup
	@echo "🚀 Development environment ready!"

prod: ## Production deployment setup
	@echo "Setting up production environment..."
	@echo "⚠️  Make sure to update .env with production settings first!"
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

update: ## Update all containers to latest versions
	@echo "Updating containers..."
	docker-compose pull
	docker-compose up -d

health: ## Check health of all services
	@echo "Checking service health..."
	@echo "Web Server (HTTP):"
	@curl -sSf http://localhost:$(HTTP_PORT) > /dev/null && echo "✅ HTTP OK" || echo "❌ HTTP Failed"
	@echo "Database:"
	@docker-compose exec -T db mysqladmin ping -h localhost -u suitecrm_user -psuitecrm_password && echo "✅ Database OK" || echo "❌ Database Failed"
	@echo "Redis:"
	@docker-compose exec -T redis redis-cli ping && echo "✅ Redis OK" || echo "❌ Redis Failed"

suitecrm-status: ## Check SuiteCRM installation status
	@echo "Checking SuiteCRM v8 status..."
	@echo "=============================="
	@if docker-compose exec -T suitecrm test -f /var/www/html/.suitecrm-ready; then \
		echo "✅ SuiteCRM container is ready"; \
	else \
		echo "❌ SuiteCRM container not ready"; \
	fi
	@if docker-compose exec -T suitecrm test -f /var/www/html/config.php; then \
		echo "✅ SuiteCRM is installed (config.php found)"; \
	else \
		echo "⚠️  SuiteCRM not yet installed - ready for web installation"; \
		echo "   Access: http://localhost:$(HTTP_PORT)/install.php"; \
	fi
	@if docker-compose exec -T suitecrm test -f /var/www/html/VERSION; then \
		echo "📋 SuiteCRM Version:"; \
		docker-compose exec -T suitecrm cat /var/www/html/VERSION; \
	else \
		echo "❌ SuiteCRM version file not found"; \
	fi

migrate: ## Run migration scripts (implementation in next task)
	@echo "Running migration scripts..."
	@echo "This will be implemented in the migration task..."

# Development helpers
composer-install: ## Install PHP dependencies
	docker-compose exec suitecrm composer install

npm-install: ## Install Node.js dependencies
	docker-compose exec suitecrm npm install

build-assets: ## Build frontend assets
	docker-compose exec suitecrm npm run build:prod

permissions: ## Fix file permissions
	docker-compose exec --user root suitecrm chown -R suitecrm:www-data /var/www/html
	docker-compose exec --user root suitecrm find /var/www/html -type f -exec chmod 644 {} \;
	docker-compose exec --user root suitecrm find /var/www/html -type d -exec chmod 755 {} \;
	docker-compose exec --user root suitecrm chmod -R 775 /var/www/html/cache /var/www/html/upload /var/www/html/tmp /var/www/html/logs
