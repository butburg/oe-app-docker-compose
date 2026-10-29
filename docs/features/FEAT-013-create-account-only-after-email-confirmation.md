---
id: FEAT-013
title: Create the account only after the email is confirmed
status: backlog # backlog | todo | in-progress | review | done
priority: high # low | medium | high
category: feature # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - auth
  - registration
  - email
---

# Create the account only after the email is confirmed

## Overview
Submitting the register form no longer creates a user and no longer logs anyone in. It stores a pending registration, sends one confirmation mail and shows a "Check your email" page. Only when the link in the mail is opened is the account created (already verified) and the user logged in. If the user does nothing, no account ever exists.

## Problem Statement
Today registration creates the user immediately, logs them in and redirects to the gallery while the email is still unverified. This leaves unverified accounts in the database (e.g. spam sign-ups), gives unverified users access to the logged-in view (see FEAT-010), and offers no clean way out (see FEAT-012).

## User Flow
1. User fills in the register form and submits.
2. Page "Check your email": "We sent a link to **mail@example.com**. Open it to create your account." With small actions:
   - **Change email**: edit the address, sends a new mail to the new address; the old link becomes invalid.
   - **Resend**: send the mail again (throttled).
   - **Abort**: discard the pending registration.
3. User opens the link from the mail → it opens in a new tab (fine, the old tab may stay open) → account is created with `email_verified_at = now()`, user is logged in and lands on the gallery with a welcome message.
4. If the user leaves the page without doing anything: exactly one mail was sent; no account exists; the pending registration expires.

## Acceptance Criteria
- [ ] `POST /register` creates no row in `users` and does not log in; it stores a pending registration (name, email, hashed password, token hash, expiry) and sends one mail.
- [ ] The "Check your email" page offers change email, resend and abort, as described above. Resend and change email are throttled.
- [ ] The link is single-use and expires (e.g. 24 h). Opening it creates the user verified, logs them in, deletes the pending registration and redirects to the gallery.
- [ ] Expired, used or aborted links show a friendly page with a link back to register.
- [ ] A second registration for the same email replaces the pending one (the old link becomes invalid). Registering an email that already has an account does not reveal that fact (same "check your email" page; optionally send a "you already have an account" mail instead).
- [ ] Expired pending registrations are deleted by a scheduled prune (`model:prune` or a command).
- [ ] If the user is logged out between submit and click, the link still works (it does not depend on the session).
- [ ] The `verified` middleware and the existing verify page stay for users who change their email in the profile (FEAT-010, FEAT-012).
- [ ] Feature tests for: submit creates no user; link creates a verified user and logs in; expired/used/aborted link; change email invalidates the old link; resend throttle; no account enumeration. Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `app/Http/Controllers/Auth/RegisteredUserController.php`, new `PendingRegistration` model + migration, new mail/notification, `routes/auth.php`, `resources/views/auth/register.blade.php`, new "check your email" view, `tests/Feature/Auth/RegistrationTest.php`, scheduler (`routes/console.php`).
- **Security**: store only a hash of the token (like `password_reset_tokens`); compare with `hash_equals`. The pending state lives in the database (keyed by token), not only in the session, so the link works in the new tab and on another device.
- **Abort/change/resend** need to know which pending registration belongs to the current tab: keep its id in the session after submit.
- **Name/email uniqueness**: check `users` on submit and again on confirm (someone else may have confirmed the same email in between).
- **Related**: FEAT-010, FEAT-012; replaces the current "Thanks for signing up" verify-email screen for new registrations.
