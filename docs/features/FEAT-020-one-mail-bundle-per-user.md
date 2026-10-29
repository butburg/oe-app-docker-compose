---
id: FEAT-020
title: One mail bundle per user for all notifications and announcements
status: done # backlog | todo | in-progress | review | done
priority: medium # low | medium | high
category: feature # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - mail
  - notifications
---

# One mail bundle per user for all notifications and announcements

## Overview
All mails Weedy Gallery sends to a user about activity (comments on their images, new images of others, announcements of the admins) are collected and sent as one mail. Several comments and uploads within an hour mean one mail, not ten.

## Problem Statement
FEAT-018 sent one mail per comment, FEAT-019 bundled only the images of one uploader, FEAT-017 sent announcements right away. An active hour could fill an inbox with many small mails.

## Acceptance Criteria
- [x] Comments (FEAT-018), new images (FEAT-019) and announcements (FEAT-017) are stored per recipient (`pending_mails`) instead of being mailed.
- [x] Fixed window: the first item of a user starts the clock, after `MAIL_BUNDLE_MINUTES` (default 60) everything collected until then goes out in one mail. Items after that start a new window. No debounce, so constant activity cannot delay the mail forever.
- [x] Not bundled: account mails (registration, email change, account exists, password reset) and the admin's "Send test to me".
- [x] Everything is checked again when sending: deleted comments/posts/announcements/users drop out (foreign keys with cascade), posts made draft drop out, switched-off notifications and unverified accounts drop comment and image items. Nothing left: no mail.
- [x] Mail: announcements first (admin Markdown, placeholders per recipient), then "New comments on your images" grouped by post with excerpts and a link to the post, then "Fresh images in the gallery" grouped by uploader (max. 10 titles each). Button "Open the gallery" only when there are comments or images.
- [x] Subject from the parts, e.g. `Maintenance on Sunday · Ben commented on your image · Anna published 3 new images`.
- [x] Footer and `List-Unsubscribe` header with the settings link only when the mail has comments or images; announcements only keep the default footer (they cannot be switched off).
- [x] Rate limit: the queue worker takes at most 30 jobs per run (`--max-jobs=30`, cron job every minute), one job sends at most one mail. Replaces the spreading by `MAIL_PER_MINUTE`, which only held within one action.
- [x] Settings page and admin preview say that mails are bundled.
- [x] Tests (container): `tests/Feature/Mail/MailBundleTest.php`, `tests/Feature/Admin/AdminMailTest.php`.

## Technical Implementation Notes
- **Affected files / components**: `App\Actions\CollectMail` (entry point for all user mails), `App\Jobs\SendMailBundle` (one per user, delayed, cache lock per user), `App\Mail\MailBundle` + `resources/views/emails/bundle.blade.php`, `App\Models\PendingMail` + migration `create_pending_mails_table`, `CommentController::store`, `PostController::publish`, `Admin\MailController::send`, `config/mail.php` (`bundle_minutes`). Removed: `SendNewCommentMail`, `SendNewImagesMail`, `NewComment`, `NewImages`, `QueueMails`.
- **No cron**: delayed queue jobs. A job that runs too early (two items at the same moment) dispatches itself again for the oldest item. Items are deleted only after sending (a failed send is retried with the same items); items that arrived while sending get a new job.
- **Env**: `MAIL_NEW_IMAGES_QUIET_MINUTES` is renamed to `MAIL_BUNDLE_MINUTES`.
- **Preview**: `/dev/mail/bundle-everything`, `bundle-comments`, `bundle-new-images`, `bundle-announcement`. Screenshots: `docs/features/screenshots/FEAT-020-*.png`.
- **Related**: FEAT-016 (mail layout, now without any external resources), FEAT-017, FEAT-018, FEAT-019.
