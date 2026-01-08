# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Jira Service Desk is a self-hosted web portal that connects to the Jira Cloud API, allowing users to interact with Jira issues through a simplified interface without needing direct Jira access. The application supports project browsing, issue management, kanban boards, favorites, and real-time notifications via Jira webhooks.

## Tech Stack

- **Backend**: Symfony 7.4 (PHP 8.4+), Doctrine ORM 3.5, FrankenPHP
- **Frontend**: TypeScript, Stimulus.js 3.2, Turbo, Bootstrap 5.3, Vite 6.3, Quill editor
- **Database**: MariaDB/MySQL
- **Jira Integration**: lesstif/jira-cloud-restapi library, webhook receiver at `/webhook/jira`

## Development Commands

All commands use the Makefile:

```bash
# Setup & Environment
make start              # Full setup: config, build, up, vendor, assets
make up                 # Start Docker containers
make stop               # Stop containers

# Dependencies
make vendor             # Install Composer dependencies
make npm                # Install Yarn dependencies

# Assets
make assets             # Build dev assets
make assets-build       # Build production assets
make watch              # Watch assets (Vite dev server)

# Database
make db-migrate         # Run migrations
make db-diff            # Generate migration
make db-reset           # Drop and recreate database
make db-reload          # Reset and load fixtures

# Testing & Quality
make tests              # Run quality checks + PHPUnit tests
make phpunit            # Run unit tests only
make phpunit FILTER=TestName  # Run specific test
make quality            # Run Easy Coding Standard
make ecs                # Run and fix code style
make lint               # Lint container, translations, Twig, YAML
make infection          # Mutation testing
```

## Architecture

### Request Flow
HTTP Request → `/public/index.php` → Symfony Kernel → Router → Controller → (optional Message dispatch) → Twig template or JSON response

### Key Directories

```
/src
├── Controller/         # HTTP handlers (Admin/, App/, BrowseIssue/, Security/)
├── Entity/             # Doctrine entities (User, Project, Notification, Favorite, etc.)
├── Repository/         # Data access layer
├── Service/            # Business logic
├── Message/            # Async processing (Command/, Event/, Query/)
├── Form/               # Symfony form types
├── Security/           # Auth & authorization (UserChecker, ProjectVoter)
└── Enum/               # PHP enums for LogEntry and Notification types

/assets
├── stimulus/           # Stimulus.js controllers (TypeScript)
└── styles/             # SCSS stylesheets

/templates              # Twig templates with components
/config                 # Symfony bundle configurations
/migrations             # Doctrine database migrations
/tests/Unit             # PHPUnit unit tests
```

### Async Message Processing
The application uses Symfony Messenger with Doctrine transport for async jobs:
- **Command messages**: Actions (CreateProject, EditIssue, SendNotification)
- **Event messages**: Webhook events (IssueCreated, IssueUpdated, CommentCreated)
- **Query messages**: Data retrieval

### Security
- Form-based authentication
- Project-level authorization via `ProjectVoter`
- Password reset with token-based flow

## Code Style

- **PHP**: Symfony Coding Standards via Easy Coding Standard (ECS), config in `ecs.php`
- **TypeScript/JS**: ESLint with TypeScript support, config in `.eslintrc.yml`
- **Indentation**: 4 spaces for PHP/YAML, 2 spaces for JS/TS/JSON

## CI/CD

GitHub Actions runs on PRs and pushes:
- PHP 8.4, Node 22
- PHPUnit tests
- Symfony linting (container, translations, Twig, YAML)
- Easy Coding Standard checks

## Environment Configuration

- `.env`: Default environment variables
- `.env.local`: Local overrides (git-ignored)
- Key variables: `DATABASE_URL`, `JIRA_HOST`, `JIRA_USER`, `JIRA_PASS`, `MAILER_DSN`
