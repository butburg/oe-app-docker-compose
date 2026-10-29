---
id: FEAT-011
title: Serve images through an access-checked route
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

# Serve images through an access-checked route

## Overview
Image files are delivered by a Laravel route that checks whether the viewer may see the post, instead of being public files under `/storage`. Protected (sensitive) and unpublished images are only delivered to verified users (sensitive) or to the owner and admins (drafts).

## Problem Statement
All image variants live on the `public` disk under predictable paths, e.g. `files/posts/images/s/{id}.jpeg`, linked via `storage:link`. Anyone, logged in or not, can open a protected or draft image by guessing or incrementing the id. The checks in the gallery (and FEAT-010) only hide the `<img>` tag, not the file.

## Acceptance Criteria
- [ ] Post image variants are stored on a non-public disk (e.g. `local`, `storage/app/private`) and are no longer reachable under `/storage/...`.
- [ ] A route like `GET /images/{variant}` streams the file after an access check:
  - published, not sensitive → everyone
  - published, sensitive → verified users only
  - draft → owner and admins only
  - profile images → everyone (or decide otherwise)
- [ ] Unauthorized requests get 404 (not 403) so ids of hidden images are not confirmed.
- [ ] URLs are not guessable or enumerable (route model binding on a random key/UUID, or signed URLs with expiry).
- [ ] Sensible caching headers: `private` for protected images, long `public` caching for public ones; ETag/Last-Modified supported.
- [ ] Migration moves the existing files on prod from the public to the private disk and updates the stored paths; old public paths stop working. Deployment steps documented.
- [ ] `image_or_placeholder`, gallery zoom, profile tiles, My Images, share links and the factory images keep working.
- [ ] Feature tests for every access case above. Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `app/Actions/CreateImageVariants.php`, `app/Actions/StoreImage.php` (disk/path), `app/Actions/DeleteUser.php`, `PostController` / `ProfileController` (delete uses `Storage::disk('public')`), `resources/views/components/image_or_placeholder.blade.php`, `ImageVariant` model, new controller + route, migration/command to move files, `database/factories/ImageVariantFactory.php`.
- **Approach**: a `Gate`/policy `view-image` shared with the gallery query logic so both stay in sync. `Storage::disk('private')->response($path)` for streaming.
- **Performance**: every image request now boots PHP. Option: nginx `X-Accel-Redirect` to an `internal` location so PHP only does the check and nginx sends the file (`dockerfiles/nginx/default.conf`). Measure before deciding.
- **Share/OpenGraph**: the share feature may need public preview images of non-sensitive posts; keep those public.
- **Related**: FEAT-009 (save deterrent, frontend only), FEAT-010.
