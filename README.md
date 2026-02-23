# SuiteCRM v8 Docker Development Environment

## Project Overview
This project provides a complete Docker-based development environment for SuiteCRM v8, including all necessary services and tools for development, testing, and migration from SuiteCRM v7.

## Architecture Overview
- **SuiteCRM v8**: Latest version with Symfony framework
- **Database**: MySQL 8.0
- **Cache**: Redis 7
- **Search**: Elasticsearch 7.17.0
- **Web Server**: Nginx
- **PHP**: 8.1+
- **Container Orchestration**: Docker Compose
- **Management Tools**: PHPMyAdmin, Redis Commander

## Quick Start

### Prerequisites
- Docker and Docker Compose installed
- Make utility (optional but recommended)
- At least 4GB RAM available for containers

### 1. Environment Setup
```bash
# Clone the repository
git clone <repository-url>
cd suitecrm-custom

# Copy environment template
make setup-env
# or manually: cp env.example .env

# Review and update .env file with your settings
```

### 2. Start the Environment
```bash
# Quick development setup (recommended for first time)
make dev

# Or step by step:
make up          # Start all services
make install     # Install SuiteCRM v8
make post-install # Run post-installation setup
make verify      # Verify installation
```

### 3. Access Your SuiteCRM
- **SuiteCRM**: http://localhost:8080
- **SuiteCRM SSL**: https://localhost:8443
- **PHPMyAdmin**: http://localhost:8083
- **Redis Commander**: http://localhost:8084

## Makefile Commands

The project includes a comprehensive Makefile with the following commands:

### 🚀 Setup & Installation
| Command | Description |
|---------|-------------|
| `make help` | Show all available commands |
| `make setup-env` | Copy environment template to .env |
| `make dev` | Quick development setup (setup-env + up) |
| `make full-install` | Complete installation process |

### 🐳 Container Management
| Command | Description |
|---------|-------------|
| `make build` | Build all Docker containers |
| `make up` | Start all services |
| `make up-build` | Build and start all services |
| `make down` | Stop all services |
| `make restart` | Restart all services |
| `make stop` | Stop services without removing containers |
| `make clean` | Remove all containers, volumes and images |

### 📋 Installation & Setup
| Command | Description |
|---------|-------------|
| `make install` | Install SuiteCRM v8 (run after 'make up') |
| `make post-install` | Run post-installation configuration |
| `make verify` | Verify SuiteCRM installation |
| `make rebuild-suitecrm` | Rebuild SuiteCRM container with updated scripts |

### 📊 Monitoring & Logs
| Command | Description |
|---------|-------------|
| `make logs` | Show logs from all services |
| `make logs-app` | Show SuiteCRM application logs |
| `make logs-web` | Show Nginx web server logs |
| `make logs-db` | Show database logs |
| `make status` | Show status of all services |
| `make health` | Check health of all services |
| `make suitecrm-status` | Check SuiteCRM installation status |

### 🔧 Development Tools
| Command | Description |
|---------|-------------|
| `make shell` | Access SuiteCRM container shell |
| `make shell-root` | Access SuiteCRM container shell as root |
| `make db-shell` | Access MySQL database shell |
| `make redis-cli` | Access Redis CLI |
| `make permissions` | Fix file permissions |

### 📦 Backup & Restore
| Command | Description |
|---------|-------------|
| `make backup` | Create backup of database and files |
| `make restore BACKUP_DATE=20240101_120000` | Restore from backup |

### 🛠️ Development Helpers
| Command | Description |
|---------|-------------|
| `make composer-install` | Install PHP dependencies |
| `make npm-install` | Install Node.js dependencies |
| `make build-assets` | Build frontend assets |
| `make update` | Update all containers to latest versions |

## Environment Configuration

The `.env` file contains all configuration options. Key settings include:

### Application Settings
```bash
APP_ENV=prod                    # Environment (dev/prod)
SITE_URL=http://localhost:8080  # SuiteCRM URL
```

### Database Configuration
```bash
DB_HOST=db
DB_PORT=3306
DB_NAME=suitecrm_db
DB_USER=suitecrm_user
DB_PASSWORD=suitecrm_password
```

### Service Ports
```bash
HTTP_PORT=8080          # SuiteCRM HTTP
HTTPS_PORT=8443         # SuiteCRM HTTPS
MYSQL_PORT=3306         # MySQL
PHPMYADMIN_PORT=8083    # PHPMyAdmin
REDIS_COMMANDER_PORT=8084 # Redis Commander
```

## Service Architecture

### Core Services
- **suitecrm**: Main SuiteCRM v8 application
- **nginx**: Web server and reverse proxy
- **db**: MySQL 8.0 database
- **redis**: Redis cache server
- **elasticsearch**: Search engine

### Management Tools
- **phpmyadmin**: Database management interface
- **redis-commander**: Redis cache management interface

### Network Configuration
- All services run on a custom bridge network (`suitecrm_network`)
- Internal communication uses service names
- External access through mapped ports

## Development Workflow

### 1. Initial Setup
```bash
make dev                    # Quick setup
make full-install          # Complete installation
```

### 2. Daily Development
```bash
make up                    # Start services
make logs-app             # Monitor application logs
make shell                # Access container for development
```

### 3. Making Changes
```bash
make rebuild-suitecrm     # Rebuild after script changes
make permissions          # Fix permissions after file changes
```

### 4. Backup & Maintenance
```bash
make backup               # Create backup
make health               # Check service health
make clean                # Clean up (removes all data!)
```

## Troubleshooting

### Common Issues

1. **Port conflicts**: Update ports in `.env` file
2. **Permission issues**: Run `make permissions`
3. **Container not starting**: Check logs with `make logs`
4. **Database connection**: Verify database is running with `make status`

### Useful Commands
```bash
make health               # Check all services
make suitecrm-status      # Check SuiteCRM status
make logs-app             # View application logs
make shell                # Debug inside container
```

## Migration Phases
1. ✅ Environment Setup & Docker Configuration
2. ✅ Development Environment Ready
3. ⏳ Migration Framework Implementation
4. ⏳ Customization Migration
5. ⏳ Testing & Deployment

## Next Steps
- Complete SuiteCRM v8 installation using `make full-install`
- Begin migration framework development
- Test customizations in the new environment
