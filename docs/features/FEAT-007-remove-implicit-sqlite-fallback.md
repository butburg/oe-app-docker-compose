---
id: FEAT-007
title: Remove implicit SQLite creation, require explicit DB config
status: done # backlog | todo | in-progress | review | done
priority: medium # low | medium | high
category: enhancement # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-28
updated: 2026-10-28
tags:
  - database
  - deployment
  - mysql
---

# Remove implicit SQLite creation, require explicit DB config

## Overview
Dev uses SQLite via `src/.env`, production uses MySQL via the `ENV_VARIABLES` secret. Nothing should silently create or fall back to a SQLite file; a missing or wrong DB config must fail loudly.

## Problem Statement
`composer.json` touches `database/database.sqlite` automatically and `config/database.php` defaults to `sqlite`. If the prod `.env` lacks `DB_CONNECTION=mysql`, Laravel would silently use an empty SQLite file instead of erroring. The old SQLite file on the server is also stale (prod data lives in MySQL, visible in phpMyAdmin).

## Acceptance Criteria
- [x] Verify the `ENV_VARIABLES` secret sets `DB_CONNECTION=mysql` and valid MySQL credentials.
- [x] Delete `oe-app/database/database.sqlite` on the server (manual).
- [x] Remove the `touch('database/database.sqlite')` script from `src/composer.json` so `composer install` no longer creates the file.
- [x] Drop the implicit `sqlite` defaults (`config/database.php`, `config/queue.php`) so a missing `DB_CONNECTION` errors instead of falling back.
- [x] Keep `.env.example` / local dev working with SQLite (explicit `DB_CONNECTION=sqlite`), and document how to create the local file.
- [x] Keep the `old_sqlite` connection untouched (legacy migration skips when the file is absent).
- [x] Check the deploy workflow's `database/database.sqlite` rsync exclude is still sensible. (removed both `database/*.sqlite` excludes; the files are gitignored, and the next deploy deletes the stale `old_database.sqlite` on the server)
- [x] Verify the deploy still migrates successfully against MySQL. (`migrate --force` checked against MySQL 8.0)

## Technical Implementation Notes
- **Affected files**: `src/composer.json`, `src/config/database.php`, `src/config/queue.php`, `src/.env.example`, `README.md`, `.github/workflows/deploy-laravel-project.yml`.
- **Constraints**: Local dev must still work; do not touch `old_database.sqlite` (legacy migration `2024_08_15_164126` skips it when absent).
