---
name: local-preview-data
description: 'Diagnose or restore local Laravel preview content with the static seed data, without confusing it with factory data or deleting local data by accident.'
user-invocable: true
---

# Local Preview Data

## Check before changing data
- Inspect `src/.env` keys `DB_CONNECTION`, `DB_DATABASE`, `APP_URL`, and `docker compose ps`. Local dev uses `src/database/database.sqlite`, production uses MySQL.
- Count users/posts/images before seeding. Do not run `migrate:fresh` or replace the database without asking, the user may have own test content in it.

## Seed data
- `artisan db:seed` (or `migrate:fresh --seed`) loads the static demo set from `src/database/seeders/data/`: `posts.json` + `posts/` (31 CC0 images), `profiles.json` + `profiles/` (2 profile images). Users, logins and counts are listed in the root `README.md`.
- Images go through `App\Actions\CreateImageVariants` like an upload, into `storage/app/public/files/posts/images` and `files/profiles`. Files are named by image id, so reseeding overwrites them.
- New seed images must be free to use (CC0/public domain), show no identifiable people, and get title, author, license and source in the JSON. Do not add real user uploads, the repo is public.
- Factories (`database/factories`, `files/images/factory_images`) are for tests only, not for preview data.

## Tests
- `src/phpunit.xml` runs tests on in-memory SQLite, they do not touch the preview database. Run them only in the container: `docker compose run --rm artisan test`.
