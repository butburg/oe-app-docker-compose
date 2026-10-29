---
id: FEAT-018
title: Mail the uploader when their image gets a comment
status: done # backlog | todo | in-progress | review | done
priority: medium # low | medium | high
category: feature # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - mail
  - comments
  - notifications
  - profile
---

# Mail the uploader when their image gets a comment

## Overview
When someone comments on a post, the owner of the post gets a mail with the commenter's name, a short excerpt and a link to the post. Users can switch these mails off in their profile. This feature also adds the "Mail notifications" section in the profile and the queue worker that all notification mails need.

## Problem Statement
Uploaders only notice new comments when they visit the gallery. There are no notification mails, no notification settings and no queue worker, so every mail is sent synchronously inside the web request.

## Acceptance Criteria
- [x] When a comment is created, the post owner gets a mail, except when they commented on their own post.
- [x] Only verified owners with the switch "Comments on my images" on get it (default: on for new and existing users).
- [x] Mail content: commenter name, post title, comment excerpt (e.g. first 200 characters, escaped), button to the post in the gallery (slug anchor from FEAT-014, page computed at send time).
- [x] Mail is queued and sent after the DB commit; if the comment or post was deleted in the meantime, nothing is sent.
- [x] Profile gets a section "Mail notifications" with the switch "Comments on my images" (this feature) and room for "New images" (FEAT-019). Reachable under its own URL (e.g. `/profile/notifications` or `/profile?section=notifications`), not only an `#anchor`, because the anchor is lost when the user has to log in first.
- [x] Every notification mail has a footer link "Change mail settings" to that URL. Not logged in → login → back to the settings (`url.intended`).
- [x] Optional `List-Unsubscribe: <https://…/profile/notifications>` header (no one-click, login is required by decision).
- [x] Queue worker (added with FEAT-017 already): new service in `docker-compose.yml` running `php artisan queue:work --tries=3` (restarts with the stack); deployment notes updated. Failed jobs end up in `failed_jobs`.
- [x] Feature tests: mail queued for owner, not for own comment, not when switched off, not for unverified owner, settings page saves the switch, footer link present, guest is sent to login and back. Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `CommentController::store`, new mailable `NewComment` (Markdown, FEAT-016 layout), migration adding `notify_comments` (boolean, default true) to `users` (or a `notification_settings` JSON column shared with FEAT-019), `app/Models/User.php` (fillable/casts), new profile partial `resources/views/profile/partials/update-notification-settings-form.blade.php`, `ProfileController` (or a small `NotificationSettingsController`), `routes/web.php`, `docker-compose.yml` (queue worker), `dockerfiles/` if needed.
- **Implemented**: `CommentController::store` dispatches `App\Jobs\SendNewCommentMail` (after commit, with the comment id only). The job checks everything when it runs: comment, post and owner still exist, not the own post, owner verified, `notify_comments` on; otherwise it does nothing. Mailable `NewComment` (Markdown, FEAT-016 layout, `List-Unsubscribe` header). User text (names, title, comment excerpt of 200 characters) goes through `Mail\Concerns\EscapesMarkdown::plainText()`: HTML escaped and Markdown characters escaped, so a comment cannot put links or formatting into the mail. Settings: `User::NOTIFICATION_SETTINGS` (FEAT-019 adds its switch there), section "Mail notifications" in the profile (`#notifications`), `GET /profile/notifications` (own URL for the mail link, redirects to the section, survives the login) and `PATCH /profile/notifications`. Theme: blockquote style for the excerpt. README: queue service. Tests: `tests/Feature/Mail/NewCommentMailTest.php`. Checked end to end: real comment → worker → MailHog (then removed again).
- **Screenshots**: `docs/features/screenshots/FEAT-018-*.png`.
- **Many comments**: one mail per comment for now. If a post gets many comments quickly, bundling like in FEAT-019 can be added later.
- **Related**: FEAT-014 (slug anchors), FEAT-016 (layout), FEAT-017 (admin mails, uses the same worker), FEAT-019 (new image mails, second switch).
