---
name: server-readonly-ssh
description: 'Read-only SSH access to the production server (lima-city). Use whenever production files, storage or DB must be inspected. Never write, never leak .env.'
user-invocable: true
---

# Server SSH (READ-ONLY)

## Connection
- host = `eweedy.lima-ssh.de`, user = `eweedy`, port 22, key-only auth (no password)
- key = `~/.ssh/id_rsa` (verified; offered + accepted). Other keys in `~/.ssh` (`id_ed25519*`, `deploy_kleinanz`, `aws-eb`) are NOT for this host
- no entry in `~/.ssh/config` → always pass explicitly:
  `ssh -o BatchMode=yes -o IdentitiesOnly=yes -o ConnectTimeout=10 -i ~/.ssh/id_rsa eweedy@eweedy.lima-ssh.de '<cmd>'`
- login prints a banner (lima-city text) → ignore, use `| tail`
- home = `/home/webpages/lima-city/eweedy`, app = `~/oe-app` (laravel root), uploads = `~/oe-app/storage/app/public/files/{posts/images/{s,m,l,xl},profiles/{xs,s,l}}`
- `mysql` + `php` exist on server; DB = MySQL, host localhost, name/user/password only from `~/oe-app/.env`
- connection fails → check `ssh -v` for "Offering public key: id_rsa"; if missing, `IdentitiesOnly`/`-i` was dropped. Do not try other keys or password

## Allowed (read only)
- `ls`, `find`, `du`, `stat`, `wc`, `head`/`tail` of non-secret files, `grep` in code
- DB: `SELECT`, `SHOW`, `DESCRIBE`, `COUNT` only
- credentials pass-through without printing: `e(){ grep "^$1=" .env | cut -d= -f2- | tr -d '"'; }; export MYSQL_PWD="$(e DB_PASSWORD)"; mysql -h $(e DB_HOST) -u $(e DB_USERNAME) $(e DB_DATABASE) -e '...'`

## Forbidden
- any write: `rm`, `mv`, `cp`, `>`/`>>`, `tee`, `chmod`, `rsync`, `scp` upload, `artisan` (migrate/tinker/anything), `composer`, `npm`, INSERT/UPDATE/DELETE/DROP/ALTER
- never `cat`/`less`/`grep` the whole `.env`, `env`, `printenv`, `artisan config:show`, `phpinfo`, `storage/logs` dumps with tokens. Read single keys only, and only non-secret ones (`APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`). Never print `APP_KEY`, `*_PASSWORD`, `*_SECRET`, `MAIL_*` creds, tokens, in output or in chat/docs/commits
- no pasting of personal data (emails, user rows) into docs; counts/ids are enough
- deletes/changes on server = user does manually (give exact command, run in `~/oe-app/storage/...`); auto mode blocks remote writes anyway

## Facts
- deploy (`.github/workflows/deploy-laravel-project.yml`): rsync `--delete-after` of `src/` → `oe-app/`, excludes `storage/`, `.env`, `vendor/`, `node_modules/` → deploy never touches server storage; `.env` is rewritten from secret `ENV_VARIABLES`
- `storage/.../factory_images` is git-tracked but never on server
