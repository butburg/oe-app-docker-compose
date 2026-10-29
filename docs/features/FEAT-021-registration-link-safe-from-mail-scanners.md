---
id: FEAT-021
title: Registration and email change links safe from mail scanners
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

# Registration and email change links safe from mail scanners

## Overview
The links in the registration mail and in the email change mail only open a page with a button. The account is created, or the new email confirmed, when the user clicks it.

## Problem Statement
Outlook Safe Links, Defender and some mail gateways open links in incoming mails on their own. Since FEAT-013 the GET on `register/confirm/{token}` created the account and used up the link. The scanner got the account and the login, and the user then saw "This link does not work (any more)" although the account existed (review of PR #5, point 3).

## Acceptance Criteria
- [x] `GET register/confirm/{token}` only checks the token and shows a page with the name, the email and a button; it creates nothing.
- [x] `POST register/confirm/{token}` creates the account and logs in, as before.
- [x] The page welcomes the user by name and uses the site colours: the button has a gradient frame in primary, secondary and accent and fills on hover.
- [x] An invalid or expired link still shows the "link does not work" page on GET.
- [x] Email change: `GET profile/email/confirm/{user}/{hash}` (signed) only shows a page with the new email and a "Confirm my new email" button, the `POST` to the same signed URL switches the email.
- [x] A link that was already used says "Your email is already changed to …" instead of "was not changed".
- [x] Both pages use the same button (`x-glow-button`).
- [x] Tests and validation passed (container, 174 tests).

## Technical Implementation Notes
- **Affected files / components**: `RegisteredUserController::showConfirm()` (new) and `confirm()` (now POST), `routes/auth.php`, `resources/views/auth/register-confirm.blade.php`, `tests/Feature/Auth/RegistrationTest.php`.
- **Email change**: `ProfileController::showEmailChange()` (new) shows the page only while the link matches the pending email, otherwise it hands over to `confirmEmailChange()`, which then only answers with the matching message. The form posts to `$request->fullUrl()`, so the `signed` middleware checks the same signature on the POST. Tests: `tests/Feature/EmailChangeTest.php`.
- **Screenshots**: `docs/features/screenshots/FEAT-021-activate-account.png`, `FEAT-021-activate-account-mobile.png`, `FEAT-021-confirm-new-email.png`.
- **Related**: FEAT-013.
