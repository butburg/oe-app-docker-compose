---
name: local-preview-data
description: 'Diagnose or restore local Laravel preview content without confusing factory data, SQLite, MariaDB, or original user images.'
user-invocable: true
---

# Local Preview Data

## Check before changing data
- Inspect `src/.env` keys `DB_CONNECTION`, `DB_DATABASE`, `APP_URL`; inspect the running server and `docker compose ps`.
- This repo may run Laravel on host `:8000` against `src/database/database.sqlite` while Compose MariaDB (`mysql`) is empty. Never assume the web app uses the container DB.
- Count users/posts/images and inspect image-file paths before seeding or importing. Do not run `migrate:fresh`, truncate, or replace a database to fix an empty screen.
- Do not use factory posts as a substitute for requested original images; label factory/demo content if explicitly requested.

## Legacy image source
- Original import source: `src/old_image_posts/images.csv`; `file_data` contains raw binary bytes, not base64. Old `ConvertBlobImages` base64-decodes incorrectly.
- CSV MIME labels may disagree with actual bytes; inspect signatures / `finfo` before decoding.
- Preserve filename/title, original username, and valid dates; old migration maps legacy posts to published, non-sensitive, `user_id = null`. Confirm this policy before reusing it.
- Store image variants through `App\\Actions\\CreateImageVariants` / `StoreImage`; pass an accepted `UploadedFile` or `SplFileInfo`. Keep artwork proportions with `scaleDown`; do not crop.
- Check database counts and actual image bytes after import. If the source or mapping is ambiguous, stop and ask for the original DB/backup instead of inventing content.

## Test isolation gotcha
- `src/tests/Feature/Posts/PostControllerTest.php` uses `RefreshDatabase`.
- `src/phpunit.xml` leaves `DB_CONNECTION` and `DB_DATABASE` overrides commented; tests can target and reset the active SQLite file. Verify isolation or backup/restore the preview DB before running such tests; do not run a destructive suite after restoring preview content.
