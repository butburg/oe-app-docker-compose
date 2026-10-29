---
id: FEAT-021
title: Registration link safe from mail scanners
status: done # backlog | todo | in-progress | review | done
priority: high # low | medium | high
category: bugfix # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - auth
  - registration
  - mail
---

# Registration link safe from mail scanners

## Overview
The link in the registration mail only opens a welcome page with an "Activate my account" button. The account is created when the user clicks the button.

## Problem Statement
Outlook Safe Links, Defender and some mail gateways open links in incoming mails on their own. Since FEAT-013 the GET on `register/confirm/{token}` created the account and used up the link. The scanner got the account and the login, and the user then saw "This link does not work (any more)" although the account existed (review of PR #5, point 3).

## Acceptance Criteria
- [x] `GET register/confirm/{token}` only checks the token and shows a page with the name, the email and a button; it creates nothing.
- [x] `POST register/confirm/{token}` creates the account and logs in, as before.
- [x] The page welcomes the user by name and uses the site colours: the button has a gradient frame in primary, secondary and accent and fills on hover.
- [x] An invalid or expired link still shows the "link does not work" page on GET.
- [x] Tests and validation passed (container, 173 tests).

## Technical Implementation Notes
- **Affected files / components**: `RegisteredUserController::showConfirm()` (new) and `confirm()` (now POST), `routes/auth.php`, `resources/views/auth/register-confirm.blade.php`, `tests/Feature/Auth/RegistrationTest.php`.
- **Email change link** (`profile.email.confirm`): stays a GET, the change itself is harmless when a scanner does it. A second click whose hash matches the current email now says "Your email is already changed to …" instead of "was not changed" (`ProfileController::confirmEmailChange()`, `tests/Feature/EmailChangeTest.php`).
- **Screenshots**: `docs/features/screenshots/FEAT-021-activate-account.png`, `FEAT-021-activate-account-mobile.png`.
- **Related**: FEAT-013.
