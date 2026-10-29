---
id: FEAT-014
title: Readable post anchors with slugs
status: done # backlog | todo | in-progress | review | done
priority: medium # low | medium | high
category: feature # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - posts
  - urls
  - sharing
---

# Readable post anchors with slugs

## Overview
Links to a post use an anchor made from its title instead of its id: `/dashboard?page=3#schoener-sonnenuntergang` instead of `/dashboard?page=3#post-42`. Shared links and bookmarks show something a person can recognise, and they no longer reveal post ids (and so the number of posts).

There is no page for a single post; posts stay in the gallery. Only the anchors and the links pointing to them change.

## Problem Statement
Posts are linked via their numeric id as anchor (`#post-{id}`): in the share button, "view in gallery" links in My Images and on the profile page, and as `id` of the post container in the gallery. The link says nothing about the post and exposes the id.

## Acceptance Criteria
- [x] `posts` gets a unique `slug` column, created from the title with `Str::slug($title, '-', 'de')` (lowercase, `-` instead of spaces, umlauts transliterated: `ä` → `ae`, maximum readable length).
- [x] Duplicates after trimming get a numeric suffix: `sonnenuntergang`, `sonnenuntergang-2`, `sonnenuntergang-3`. Uniqueness enforced by a DB unique index; a race on insert is retried.
- [x] Titles that give an empty slug (only emojis/special characters) fall back to a short random slug.
- [x] The slug is set on create and may still change while the post is a draft; it is fixed once the post is published, even if the title changes later. Shared links never break.
- [x] The post container in the gallery uses the slug as anchor id (`id="{slug}"` or `id="p-{slug}"`; choose one so slugs cannot collide with other element ids on the page).
- [x] Share button (`share.blade.php`), "view in gallery" links (`posts/index`, `profile/show`) build the URL with the slug anchor.
- [x] Only the anchor changes: comments, Alpine stores and admin/owner routes (`edit`, `publish`, `make-draft`, `toggle-sensitive`, `destroy`) keep using the id internally.
- [x] Old links with `#post-{id}` are not supported anymore (no fallback, no hidden second anchor); they open the page without scrolling to a post.
- [x] Migration fills slugs for existing posts (same duplicate rule, oldest post gets the plain slug).
- [x] Feature tests: slug generation incl. umlauts and duplicates, fixed after publish, share URL and gallery anchor contain the slug and no id. Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: migration (`slug` column + backfill), `app/Models/Post.php` (slug on `creating`/`updating`), `resources/views/components/gallery/gallery.blade.php` (anchor id), `resources/views/components/gallery/share.blade.php`, `resources/views/posts/index.blade.php`, `resources/views/profile/show.blade.php`, `database/factories/PostFactory.php`.
- **Dependencies or constraints**: own ~15 lines in the model are enough; `spatie/laravel-sluggable` is not needed.
- **Implemented**: anchor is `id="p-{slug}"` (`Post::anchor()`), links via `Post::galleryUrl()`; slug logic in `Post::uniqueSlug()` (max. 60 characters, cut at a word boundary), set in `Post::booted()` on `saving`, fixed once `published_at` is set (also after making it a draft again); a unique-index race is retried in `Post::save()`. `PostController::getPageForPost()` moved to `Post::galleryPage()`; it was one page off for the last post on a page (`ceil((position + 1) / perPage)`), now `ceil(position / perPage)`. Tests: `tests/Feature/Posts/PostSlugTest.php`.
- **Known limitation**: the link still contains `?page=N`, computed when the link is created. When new posts are published, the post moves to a later page and an old shared link opens the wrong page. Not part of this feature; a possible follow-up is `/dashboard?post={slug}`, where the dashboard computes the current page itself and redirects.
- **Related**: FEAT-011 (image links are separate from post links and expire; post links stay permanent).
