---
id: FEAT-011
title: Serve images through expiring, non-guessable links
status: backlog # backlog | todo | in-progress | review | done
priority: high # low | medium | high
category: security # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - images
  - security
  - storage
---

# Serve images through expiring, non-guessable links

## Overview
Images are no longer reachable under their file path. Every `<img>` points to `/img/{variant}/{token}`, where the token is an HMAC that changes by itself every week. The files stay where they are on the `public` disk and keep their names; only the links change. Someone who only knows or guesses an id gets 404, and a saved image link stops working after at most two weeks.

The access check stays where it already is: on the pages (gallery, profile, My Images, FEAT-010). Whoever may not see a post never gets its page, so never gets a valid token.

## Problem Statement
All image variants live on the `public` disk under predictable paths, e.g. `files/posts/images/s/{id}.jpeg`, linked via `storage:link`. Anyone, logged in or not, can open a sensitive or draft image by guessing or incrementing the id. The checks in the gallery (and FEAT-010) only hide the `<img>` tag, not the file. Saved image URLs also work forever, even after a post was made sensitive, unpublished or deleted.

## Decision
A per-request login check on every image (files on a private disk) was considered and rejected for now:
- every image request would need the session (`database` driver: one DB write per image, `url.intended` / `_previous.url` set to image URLs),
- files would have to move between disks or be migrated, which is error-prone.

An expiring link gives most of the benefit: no guessing, no permanent links. Accepted gap: a user who may see a post can pass on the image link for up to two weeks (they could also just save the image, see FEAT-009). Since all images go through one route, a real access check can be added there later without another rebuild.

## How the token works (no cron, no trigger)
```
window = floor(unix_time / 604800)          // current week number
token  = hmac_sha256(APP_KEY, "{variant_id}|{window}|{token_version}")  // shortened, e.g. 32 hex chars
url    = /img/{variant_id}/{token}
```
- **Generating:** when a page is rendered, the token for the current week is computed. Nothing is stored.
- **Checking:** the route recomputes the token for the current and the previous week and compares (`hash_equals`). If neither matches → 404.
- **Rotation happens by itself** because the week number is part of the calculation. When the week changes, every new page render gives new links, and links from two weeks ago no longer match. No cron job, no event, no DB update.
- **Lifetime of a link:** 7 to 14 days (current + previous window). The previous window is accepted so that a page opened shortly before the change does not show broken images while lazy loading. Window length is a config value (`images.link_window_days`, default 7).
- **Immediate invalidation:** `token_version` (column on `images`) is incremented when the image is replaced, the post is made sensitive, unpublished or deleted. All old links of that image are dead at once.
- Without the server's `APP_KEY` a token cannot be computed, so ids are useless on their own. (Rotating `APP_KEY` invalidates all image links, which is fine.)

## Acceptance Criteria
- [ ] nginx serves nothing under `/storage/files/` from outside (`internal` location), direct requests get 404. Files stay on the `public` disk under their current names; no file is moved or renamed.
- [ ] Route `GET /img/{variant}/{token}` checks the token for the current and previous window with `hash_equals`; on success it answers with `X-Accel-Redirect` so nginx sends the file. Wrong/expired token or unknown variant → 404.
- [ ] The route runs without session, cookies and CSRF (not in the `web` middleware group): no session write, no `url.intended` / `_previous.url` side effects.
- [ ] `token_version` on `images` is incremented on: image replaced (post update, profile image update), post toggled to sensitive, post unpublished/made draft, post or user deleted. Old links return 404 right after.
- [ ] Caching: `Cache-Control: private, max-age=` at most until the end of the link lifetime; `Last-Modified` / `ETag` with 304 support (nginx does this for the internal location).
- [ ] One helper generates image URLs (e.g. `ImageVariant::url()` or a `ImageLink` service); `image_or_placeholder`, gallery zoom, profile tiles, My Images and admin views use it. No more `asset('storage/...')` for uploaded images.
- [ ] `image_or_placeholder` no longer calls `file_exists(public_path(...))` on every render; it uses the DB row (or `Storage::disk('public')->exists()` where really needed).
- [ ] Profile images use the same links (decision: no exception, they are user uploads too).
- [ ] Static app images (`public/images/...`, placeholder) are not affected.
- [ ] Feature tests: valid token → 200 / X-Accel header; previous-window token → ok; token two windows old → 404; wrong token → 404; direct path → 404 (documented nginx check); `token_version` bump on each listed action invalidates the old link. Time is moved with `$this->travel()`.
- [ ] Performance comparison before/after (see "Performance test" below): baseline recorded **before** the first code change, same measurement after; results (table) added to this file. Acceptable: p95 per image request at most a few ms slower than the static baseline, gallery page render time not noticeably slower.
- [ ] Tests and validation passed (container).

## Performance test
A small, repeatable load test that runs entirely in Docker (no tools installed on the host) against the local nginx.
- **Script**: `scripts/perf/images.sh` (or similar). Steps:
  1. Seed a fixed data set (e.g. 50 published posts with images via factory/seeder) so before and after use the same data.
  2. Fetch one gallery page (`/dashboard`) and extract all `<img src>` URLs of uploaded images.
  3. Run a load generator in a throwaway container on the compose network (e.g. `docker run --rm --network <compose-net> williamyeh/hey` or `grafana/k6`): all image URLs, e.g. 2,000 requests, concurrency 10 and 50.
  4. Separately measure the gallery page itself (e.g. 200 requests, concurrency 10), because the HMAC is computed there for every image.
  5. Print p50 / p95 / p99 latency, requests per second and error count; optionally `docker stats` CPU of the `php` and `app` containers during the run.
- **Before** = current static `/storage/files/...` URLs, **after** = `/img/{variant}/{token}` with `X-Accel-Redirect`. Also measure "after" once with the `response()->file()` fallback (no X-Accel) to see what nginx saves.
- Run each scenario 3 times, take the median. Note the machine (WSL2, CPU) next to the results; numbers are only compared with each other, not absolute.
- Clean up afterwards: seeded data and containers removed, no leftover files.

## Technical Implementation Notes
- **Affected files / components**: new `ImageController` (or invokable `ServeImage`), route outside the `web` group in `routes/web.php` / `bootstrap/app.php`, `ImageVariant` model (URL helper), migration adding `token_version` to `images`, `resources/views/components/image_or_placeholder.blade.php`, `PostController` (update, toggle sensitive, make draft, destroy), `ProfileController` (image update), `app/Actions/DeleteUser.php`, `dockerfiles/nginx/default.conf`, `config/images.php` (window length).
- **nginx**:
  ```nginx
  location ^~ /storage/files/ {
      internal;
      alias /var/www/html/storage/app/public/files/;
  }
  ```
  The controller sends `X-Accel-Redirect: /storage/files/{path}` plus `Content-Type` and `Cache-Control`. PHP only checks the token, nginx streams the file. The local `php artisan serve` dev setup (if used) has no nginx, so fall back to `response()->file()` when not behind nginx (config flag).
- **Token length**: 32 hex chars (128 bit) of the HMAC are plenty.
- **Share/OpenGraph**: share links point to the post, not the image, so they are not affected. If OG preview images are added later, they need a separate long-lived public variant.
- **Deployment**: deploy nginx config together with the app; old `/storage/files/...` URLs (bookmarks, caches) stop working by design. No data migration besides the new column.
- **Related**: FEAT-009 (save deterrent, frontend only), FEAT-010 (guest view for unverified users), FEAT-014 (post slugs), FEAT-015 (strip EXIF/GPS on upload).
