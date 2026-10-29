---
id: FEAT-019
title: Bundled mail to all users when new images are published
status: done # backlog | todo | in-progress | review | done
priority: medium # low | medium | high
category: feature # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - mail
  - notifications
  - posts
---

# Bundled mail to all users when new images are published

## Overview
When a user publishes new images, all other users get a mail like "Anna published 4 new images". Several publications in a short time are bundled into one mail. Users can switch these mails off in their profile (default: on); the mail links directly to that setting.

## Problem Statement
Users only see new images when they open the gallery. A mail per image would flood inboxes when someone publishes ten images in a row.

## Acceptance Criteria
- [x] Trigger is **publishing** (not uploading a draft). A post that was published, made draft and published again does not trigger a second mail (`posts.notified_at` set once).
- [x] Bundling per uploader: after a publication, wait until the uploader has published nothing for 1 hour (config value), then send one mail per recipient covering all their posts published since the last mail.
- [x] Recipients: verified users with the switch "New images" on, except the uploader.
- [x] Mail content: uploader name, number of new images, titles as plain text (max. e.g. 10, then "and 5 more"), one button to the main page (`route('dashboard')`). **No other links** in the content (no post anchors, no profile of the uploader). **No images in the mail**: keeps the mail small, does not leak sensitive images into inboxes, and FEAT-011 image links would expire anyway.
- [x] Sensitive posts are counted/listed only by title (no thumbnail anyway); drafts and deleted posts are left out. If all bundled posts were deleted or made draft before sending, no mail is sent.
- [x] Switch "New images" in the profile section "Mail notifications" (from FEAT-018), default on for new and existing users; footer link "Change mail settings" in every such mail (login required, then back to the settings).
- [x] Mails are queued, one job per recipient, with a rate limit per minute (config value).
- [x] No cron needed: bundling works with delayed queue jobs.
- [x] Feature tests (time with `$this->travel()`, `Mail::fake()`/`Queue::fake()`): three publications within 1 hour → one mail per recipient listing three posts; publication after the window → new mail; uploader gets none; switch off → none; republish → no second mail; all posts made draft before sending → none. Tests and validation passed (container).

## Technical Implementation Notes
- **Changed by FEAT-020**: these mails are no longer sent on their own but collected in the user's mail bundle (`CollectMail`, `SendMailBundle`, `MailBundle`); the classes named below were replaced.
- **Affected files / components**: `PostController` (store/update when published, `publish`), new job `SendNewImagesMail` (or `NotifyNewImages`), new mailable `NewImages` (Markdown, FEAT-016 layout), migration adding `notified_at` to `posts` and `notify_new_images` (boolean, default true) to `users` (or the shared settings column from FEAT-018), profile notification partial from FEAT-018.
- **Bundling without cron**: on each publication, dispatch the job with `->delay(1 hour)` for the uploader and store `last_published_at` (e.g. in cache or on the user). When the job runs, it checks: was there a newer publication by this uploader within the window? Then do nothing, the later job will send. Otherwise collect all published posts of the uploader with `notified_at = null`, send, set `notified_at`. A cache lock per uploader prevents two jobs sending at the same time.
- **Implemented**: `PostController::publish` dispatches `App\Jobs\SendNewImagesMail::forPublication()` only when `notified_at` is null, delayed by `MAIL_NEW_IMAGES_QUIET_MINUTES` (default 60). The job carries uploader id and `published_at`; when it runs and a pending post was published later, it does nothing. Otherwise it takes all published posts with `notified_at = null` (drafts and deleted posts drop out by themselves), queues one `NewImages` mail per recipient via `QueueMails` (rate limit from FEAT-017) and sets `notified_at`. A cache lock per uploader prevents two jobs sending at once. No cron, no cache entry needed. Migration: posts that are already published get `notified_at` (no mails for old posts); the seeder sets it too. Mail: max. 10 titles plus "and N more", escaped as plain text, casual text ("Hey! Anna just uploaded 3 new images to Weedy Gallery … Go check them out"), button to the gallery, short footer "You get this mail because mails about new images are switched on. Don't want them? Change your mail settings", `List-Unsubscribe` header, only image is the logo. Tests: `tests/Feature/Mail/NewImagesMailTest.php`. Checked end to end with the worker: 2 mails to the two other verified users, uploader and unverified account none (test data restored afterwards).
- **Found on the way**: Laravel puts public mailable properties into the view after the `with` data, so a `with` key with the same name as a property is silently replaced. The escaped titles were replaced by the raw ones (a title `[x](https://…)` became a link). The view variables now have their own names; the test covers it.
- **Screenshots**: `docs/features/screenshots/FEAT-019-*.png`.
- **Queue worker**: provided by FEAT-018 (`queue:work` service); delayed jobs work with the `database` queue driver.
- **Related**: FEAT-011 (image links expire, so no images in mails), FEAT-016 (layout), FEAT-018 (settings section, worker).
