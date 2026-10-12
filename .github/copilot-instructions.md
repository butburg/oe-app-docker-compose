# Oh Eweedy Development Guidelines

## Project Overview
This repository contains **Oh Eweedy** (Weedy Gallery), a collaborative image gallery built with Laravel 11 running on a Docker LEMP stack (`docker-compose.yml`).

## Documentation & Knowledge Base
- **Knowledge Base & Quick Commands**: Consult [docs/LaravelRemember.md](../docs/LaravelRemember.md) for Docker artisan shortcuts, CRUD patterns, and project learnings.
- **Changelog**: See [docs/CHANGELOG.md](../docs/CHANGELOG.md) for version release history.
- **Feature Backlog & Kanban**: All planned and active features are stored as individual Markdown files in [docs/features/](../docs/features/).

## Architecture & Code Conventions
- **PHP / Laravel 11**: Strict types where appropriate, Form Requests for validation, resource controllers, and clean separation of concerns.
- **Docker Usage**: Run artisan and composer commands via docker-compose when interacting with containers:
  `docker-compose run --rm artisan <command>`
- **Host Memory Constraints**: The production PHP host has strict memory limits. Heavy image processing (especially resizing 10MB+ images) must be offloaded client-side or tightly constrained in memory.
- **Image Pipeline**: Pre-upload preview and client-side compression before uploading to server.

## Feature Management & Kanban Workflow
When adding or updating features:
1. Every feature must live in `docs/features/FEAT-<id>-<slug>.md` using YAML frontmatter:
   ```yaml
   ---
   id: FEAT-XXX
   title: Feature Title
   status: backlog # backlog | todo | in-progress | review | done
   priority: medium # low | medium | high
   category: feature # feature | enhancement | bugfix | ui | security
   assignee: unassigned
   created: YYYY-MM-DD
   updated: YYYY-MM-DD
   tags:
     - tag1
   ---
   ```
2. Keep the status aligned with the Kanban board (`docs/kanban/index.html`).
3. Template is available at [docs/features/template.md](../docs/features/template.md).
