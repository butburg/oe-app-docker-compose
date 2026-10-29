---
id: FEAT-011
title: Serve images through expiring, non-guessable links
status: done # backlog | todo | in-progress | review | done
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
Images are no longer reachable under their file path. Every `<img>` points to `/img/{public_id}/{token}`, where the token is an HMAC that changes by itself every week. The files stay where they are on the `public` disk and keep their names; only the links change. Someone who only knows or guesses an id gets 404, and a saved image link stops working after at most two weeks.

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
url    = /img/{public_id}/{token}       // public_id: random 16 hex chars per variant, so links do not reveal how many images exist
```
- **Generating:** when a page is rendered, the token for the current week is computed. Nothing is stored.
- **Checking:** the route recomputes the token for the current and the previous week and compares (`hash_equals`). If neither matches → 404.
- **Rotation happens by itself** because the week number is part of the calculation. When the week changes, every new page render gives new links, and links from two weeks ago no longer match. No cron job, no event, no DB update.
- **Lifetime of a link:** 7 to 14 days (current + previous window). The previous window is accepted so that a page opened shortly before the change does not show broken images while lazy loading. Window length is a config value (`images.link_window_days`, default 7).
- **Immediate invalidation:** `token_version` (column on `images`) is incremented when the image is replaced, the post is made sensitive, unpublished or deleted. All old links of that image are dead at once.
- Without the server's `APP_KEY` a token cannot be computed, so ids are useless on their own. (Rotating `APP_KEY` invalidates all image links, which is fine.)

## Acceptance Criteria
- [ ] Direct requests to `/storage/files/...` get 404. **Open:** the nginx `internal` location only existed locally, production (lima-city) has no own nginx config, so it was removed. Files are still on the `public` disk and reachable under their counting names; moving them to the private disk follows separately.
- [x] Route `GET /img/{public_id}/{token}` (random `image_variants.public_id` instead of the counting id) checks the token for the current and previous window with `hash_equals`; on success PHP sends the file (`response()->file()`). Wrong/expired token or unknown variant → 404.
- [x] The route runs without session, cookies and CSRF (not in the `web` middleware group): no session write, no `url.intended` / `_previous.url` side effects.
- [x] `token_version` on `images` is incremented on: image replaced (post update, profile image update), post toggled to sensitive, post unpublished/made draft, post or user deleted. Old links return 404 right after.
- [x] Caching: `Cache-Control: private, max-age=` at most until the end of the link lifetime; `Last-Modified` with 304 support (`BinaryFileResponse`).
- [x] One helper generates image URLs (e.g. `ImageVariant::url()` or a `ImageLink` service); `image_or_placeholder`, gallery zoom, profile tiles, My Images and admin views use it. No more `asset('storage/...')` for uploaded images.
- [x] `image_or_placeholder` no longer calls `file_exists(public_path(...))` on every render; it uses the DB row (or `Storage::disk('public')->exists()` where really needed).
- [x] Profile images use the same links (decision: no exception, they are user uploads too).
- [x] Static app images (`public/images/...`, placeholder) are not affected.
- [x] Feature tests (`tests/Feature/Images/ImageLinkTest.php`): valid token → 200 with the file; previous-window token → ok; token two windows old → 404; wrong token → 404; `token_version` bump on each listed action invalidates the old link. Time is moved with `$this->travel()`.
- [x] Performance comparison before/after (see results; the "few ms" target holds per single request, under load php-fpm is the limit, see below) (see "Performance test" below): baseline recorded **before** the first code change, same measurement after; results (table) added to this file. Acceptable: p95 per image request at most a few ms slower than the static baseline, gallery page render time not noticeably slower.
- [x] Tests and validation passed (container).

## Performance test
A small, repeatable load test that runs entirely in Docker (no tools installed on the host) against the local nginx.
- **Script**: `scripts/perf/images.sh [label]` (k6 script `scripts/perf/images.js`). It uses the static seed data already in the DB instead of seeding extra posts, so the user's data stays untouched. Steps:
  1. Seed a fixed data set (e.g. 50 published posts with images via factory/seeder) so before and after use the same data.
  2. Fetch one gallery page (`/dashboard`) and extract all `<img src>` URLs of uploaded images.
  3. Run a load generator in a throwaway container on the compose network (e.g. `docker run --rm --network <compose-net> williamyeh/hey` or `grafana/k6`): all image URLs, e.g. 2,000 requests, concurrency 10 and 50.
  4. Separately measure the gallery page itself (e.g. 200 requests, concurrency 10), because the HMAC is computed there for every image.
  5. Print p50 / p95 / p99 latency, requests per second and error count; optionally `docker stats` CPU of the `php` and `app` containers during the run.
- **Before** = current static `/storage/files/...` URLs, **after** = `/img/{variant}/{token}` with `X-Accel-Redirect`. Also measure "after" once with the `response()->file()` fallback (no X-Accel) to see what nginx saves.
- Run each scenario 3 times, take the median. Note the machine (WSL2, CPU) next to the results; numbers are only compared with each other, not absolute.
- Clean up afterwards: seeded data and containers removed, no leftover files.

### Results (2026-10-29)
Machine: WSL2, AMD Ryzen 5 3600 (12 threads), local docker compose stack, `APP_DEBUG=true`, no config/route cache, php-fpm `pm.max_children = 5`. 24 image URLs (first gallery page as guest), median of 3 runs.

| Label | Scenario | p50 ms | p95 ms | p99 ms | req/s | failed |
|---|---|---|---|---|---|---|
| before (static nginx) | images c10 (2000 req) | 0.89 | 6.29 | 10.12 | 5865 | 0 |
| before (static nginx) | images c50 (2000 req) | 2.00 | 25.34 | 38.24 | 7603 | 0 |
| before (static nginx) | gallery page c10 (200 req) | 438.54 | 620.96 | 682.03 | 22.07 | 0 |
| after (X-Accel) | images c10 (2000 req) | 17.59 | 22.70 | 34.63 | 500 | 0 |
| after (X-Accel) | images c50 (2000 req) | 82.13 | 101.72 | 120.09 | 582 | 0 |
| after (X-Accel) | gallery page c10 (200 req) | 430.89 | 574.77 | 866.31 | 22.45 | 0 |

Single image request, one at a time (c1): about 7.7 ms p50 without caches, **5.4 ms p50 with `php artisan optimize`** (production setup), vs. about 1 ms static. With caches, c10 still gives ~17 ms p50 / ~580 req/s, because only 5 php-fpm workers run.

Reading:
- Gallery page render time is unchanged (HMAC per image costs nothing measurable; the removed `file_exists()` calls even save a bit).
- Every image now costs one PHP request (+4–5 ms each, single request). Throughput drops from ~6,000 to ~550 req/s; under load the php-fpm pool is the bottleneck, not the token check. The fallback without X-Accel (`IMAGES_X_ACCEL=false`) is about the same at this file size, X-Accel mainly saves PHP memory/time on large files.
- Counter effect: images now send `Cache-Control: private, max-age=<rest of link lifetime>` and the URL stays the same for a week, so returning visitors load them from the browser cache without any request (before: no Cache-Control, revalidation by heuristics).
- If needed later: raise `pm.max_children`, or validate the token in nginx itself (`secure_link`) so PHP is skipped.

## Technical Implementation Notes
- **Affected files / components**: new `ImageController` (or invokable `ServeImage`), route outside the `web` group in `routes/web.php` / `bootstrap/app.php`, `ImageVariant` model (URL helper), migration adding `token_version` to `images`, migration adding `public_id` to `image_variants`, `resources/views/components/image_or_placeholder.blade.php`, `PostController` (update, toggle sensitive, make draft, destroy), `ProfileController` (image update), `app/Actions/DeleteUser.php`, `config/images.php` (window length).
- **File delivery**: PHP checks the token and sends the file with `response()->file()` plus `Content-Type` and `Cache-Control: private`. An nginx `X-Accel-Redirect` variant was built first and removed again: production runs on lima-city without an own nginx config, and at our file sizes it was not faster (see performance test).
- **Token length**: 32 hex chars (128 bit) of the HMAC are plenty.
- **Share/OpenGraph**: share links point to the post, not the image, so they are not affected. If OG preview images are added later, they need a separate long-lived public variant.
- **Implemented**: `App\Models\Image::url()/token()/invalidateLinks()`, `ImageController`, `routes/images.php` (loaded in `bootstrap/app.php` via `then:`, outside the `web` group), `config/images.php` (`IMAGES_LINK_WINDOW_DAYS`), `token_version` bump in `Post::booted()` (sensitive / draft) and `CreateImageVariants` (replace, covers post and profile images). Deleted posts/users: the variant rows are gone, so links answer 404.
- **Deployment**: run the migrations (done by the GitHub deploy workflow).
- **Related**: FEAT-009 (save deterrent, frontend only), FEAT-010 (guest view for unverified users), FEAT-014 (post slugs), FEAT-015 (strip EXIF/GPS on upload).
