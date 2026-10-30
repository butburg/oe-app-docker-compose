---
id: FEAT-010
title: Unverified users see only the guest view, with a hint to verify
status: done # backlog | todo | in-progress | review | done
priority: high # low | medium | high
category: security # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - auth
  - gallery
  - comments
---

# Unverified users see only the guest view, with a hint to verify

## Overview
A logged-in user whose email is not verified must not see more than a guest: no protected (sensitive) posts, no comments. Wherever a guest sees "Log in to …", an unverified user sees a hint to verify their email instead, with a link to the verify page.

## Problem Statement
Registration logs the user in and redirects to the gallery (`RegisteredUserController.php`, `Auth::login` → `route('dashboard')`). The gallery `/` is not behind the `verified` middleware, and its checks only ask "logged in?":
- `PostController::gallery` hides sensitive posts only for `!Auth::check()`.
- `gallery.blade.php` shows comments inside `@auth`.
- `add-comment-form.blade.php` shows the form for `Auth::check()`; submitting it then redirects to the verify page.

So anyone can see protected images and all comments right after signing up with any email address, without ever verifying it. Old accounts that were never verified still exist, so this case stays relevant even after FEAT-013. (Originally an email change in the profile also made users unverified; that now uses a pending email instead, see below.)

## Who is logged in but unverified?
Since FEAT-013 this is rare, which is why the "Verify your email" hint is hard to see in the browser:
- Someone with a **pending registration** (submitted the form, not clicked the mail link yet) has **no account**. They are a guest and see "Log in to view and post comments".
- **Logged in and unverified** only happens for accounts that were already unverified before FEAT-013.
- An **email change in the profile does not make the user unverified**: the new address is stored as `pending_email` and gets a confirmation link (60 minutes). The old email stays active and verified until the link is opened, so a mistyped address never locks anyone out; the profile shows the pending address with "Resend link" and "Cancel email change" (`User::requestEmailChange`, `ProfileController::confirmEmailChange`).
- To see it locally: use the seeded unverified account `user-unverified@user.com` ("Developer Login (Unverified)" on the login page).

## Acceptance Criteria
- [x] Gallery: unverified users get the same post list as guests (no `is_sensitive` posts), also with search.
- [x] Gallery: unverified users do not see comments; instead of "Log in to view and post comments" they see "Verify your email to view and post comments" with a link to `verification.notice`.
- [x] The comment form is not shown to unverified users.
- [x] Navigation: for unverified users, My Images / Profile are replaced or accompanied by a visible "Verify email" hint (decide during implementation; links already redirect to the verify page).
- [x] Any other place that checks only `auth` for showing protected content is found and fixed (grep for `@auth`, `Auth::check()`, `auth()->check()` in views and controllers).
- [x] Verified users and admins see everything as before.
- [x] Feature tests: unverified user sees no sensitive posts and no comments; verified user does; guest unchanged. Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `app/Http/Controllers/PostController.php` (`gallery`), `resources/views/components/gallery/gallery.blade.php`, `resources/views/components/gallery/comment/add-comment-form.blade.php`, `resources/views/components/gallery/comment/show-comments.blade.php`, `resources/views/layouts/navigation.blade.php`, tests in `tests/Feature`.
- **Approach**: one check for "may see protected content", e.g. `Auth::user()?->hasVerifiedEmail()`. Expose it once (a `User` helper or a Blade `@verified` directive / `Gate`) instead of repeating the condition in every view.
- **Related**: FEAT-011 (the image files themselves are still reachable by URL), FEAT-013 (no unverified accounts from registration any more).
