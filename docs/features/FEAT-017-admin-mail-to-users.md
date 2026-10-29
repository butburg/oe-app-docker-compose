---
id: FEAT-017
title: Admin page to send mails to users
status: done # backlog | todo | in-progress | review | done
priority: low # low | medium | high
category: feature # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - mail
  - admin
---

# Admin page to send mails to users

## Overview
Admins can write a mail (subject + Markdown text) in the admin area, preview it in the mail layout and send it to a chosen group of users, e.g. for announcements or maintenance notes.

## Problem Statement
There is no way to send mails to users. The app only sends auth mails (registration, email change, account exists); `TestMailController` is a commented-out test route.

## Acceptance Criteria
- [x] Admin-only page (same admin check as `/admin/posts`) with: subject, Markdown body, recipient group.
- [x] Recipient groups: all verified users, all users including unverified, a single user (any user, also unverified; search by name/email). The page shows the number of recipients before sending. (Changed 2026-10-29: "admins only" removed, "including unverified" added.)
- [x] "Send test to me" sends the mail only to the logged-in admin.
- [x] Preview renders the mail in the FEAT-016 layout (HTML), before sending.
- [x] Sending needs a confirmation step ("Send to 42 users?"); double submit does not send twice.
- [x] Mails are queued, one job per recipient (each user gets their own mail, no `To`/`CC` list with all addresses), with a rate limit per minute (config value) to stay within SMTP provider limits.
- [x] Every sent mail action is stored (`admin_mails` table: admin, subject, body, group, recipient count, created_at) and listed on the page; failed jobs are visible (`failed_jobs`).
- [x] Body is Markdown and escaped: no raw HTML from the form ends up in the mail.
- [x] Admin mails are announcements: they ignore the notification switches from FEAT-018/019 and cannot be unsubscribed, because they may contain security-relevant information. No "Change mail settings" link and no `List-Unsubscribe` header.
- [x] Mail footer: same footer as all other mails ("You get this mail because this address was used on Weedy Gallery."), no settings link. (Changed 2026-10-29: no extra "Important message …" note.)
- [x] Placeholders in subject and text, filled per recipient: `{name}`, `{email}`, `{posts}`, `{comments}`, `{website}`; listed under the text box. User values are inserted after the Markdown step and escaped; `{website}` (`APP_URL`) before it, so `{website}` and `[text]({website})` become links. Preview and test mail use the admin's own data.
- [x] Navigation for admins: "Users" (was "Admin", the user overview) and "Announcements"; page title "Announcements" / "Make an announcement". Admins see "Admin" under their name in the navigation.
- [x] Feature tests (`tests/Feature/Admin/AdminMailTest.php`): non-admins are redirected to `/` like on all admin pages (existing `Admin` middleware, not 403/404), recipient groups resolve correctly, users with both switches off still get the mail, no settings link in the mail, test mail goes only to the admin, mails are queued per recipient (`Mail::fake()`), body is escaped. Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: new `Admin\MailController`, routes in the admin group (`routes/web.php`), Blade view under `resources/views/admin/`, new mailable `AdminMessage` (Markdown, uses the FEAT-016 layout), migration `admin_mails`, link in the admin navigation; remove `TestMailController` and its commented route.
- **Queue worker**: needs a running `queue:work` (see FEAT-018, which adds the worker service to `docker-compose.yml`).
- **Decision**: the switches in the profile only cover mails about posts (FEAT-019) and comments (FEAT-018). Announcements always go to everyone in the chosen group; keep them rare.
- **Implemented**: `/admin/mails` (nav link "Announcements" for admins). Flow: form → `POST /admin/mails/preview` (rendered mail in a sandboxed iframe, buttons "Edit", "Send test to me", "Send to N users" with a browser confirm) → `POST /admin/mails`. Double submit: each preview gets a `send_token` (unique in `admin_mails`), the same token sends only once; the button is also disabled after the first click. Body: `Str::markdown()` with `html_input: escape` and no unsafe links. Rate limit: `App\Actions\QueueMails` spreads the queued mails over minutes (`MAIL_PER_MINUTE`, default 30) with a delay per mail, so no job is released or lost by a limiter (reused by FEAT-019). Single user: email field with a list of verified users (name shown). Removed `TestMailController`, `TestMail`, `emails/test.blade.php` and the `/send-test-email` route.
- **Queue worker**: added here already (service `queue` in `docker-compose.yml`, `queue:work --tries=3 --backoff=60`, `restart: unless-stopped`), because this feature is the first one that queues mails.
- **Screenshots**: `docs/features/screenshots/FEAT-017-*.png`.
- **Related**: FEAT-016 (layout), FEAT-018 (queue worker, notification settings).
