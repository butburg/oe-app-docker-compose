---
id: FEAT-012
title: Cancel registration from the verify page
status: done # backlog | todo | in-progress | review | done
priority: medium # low | medium | high
category: feature # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - auth
  - account
---

# Cancel registration from the verify page

## Overview
An unverified user can delete their account directly from the "verify your email" page, without opening the profile (which is blocked for them by the `verified` middleware).

## Problem Statement
`profile.destroy` sits behind `verified`, so an unverified user cannot delete their account at all; they can only log out. The account stays in the database forever (an admin has to remove it, see the admin delete button). Someone who typed a wrong email or changed their mind has no way out.

Once FEAT-013 is done, new registrations no longer create unverified accounts. This feature is still needed for existing unverified accounts; for them the action reads "Delete account" rather than "Cancel registration". An email change in the profile no longer makes a user unverified (the old email stays until the new one is confirmed, see FEAT-010), so those users never land here.

## Acceptance Criteria
- [x] The verify page shows a "Cancel registration / delete account" action next to "Resend" and "Log out".
- [x] It asks for confirmation (password, like the profile delete) before deleting.
- [x] Deletion uses `App\Actions\DeleteUser`; the user is logged out and redirected to the gallery with a short success message.
- [x] Only the logged-in unverified user can delete themselves through this route; verified users keep using the profile; admins are not affected.
- [x] Feature tests: unverified user can delete themselves; wrong password deletes nothing; nobody else is deleted; route is not usable by guests. Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `resources/views/auth/verify-email.blade.php`, `routes/auth.php` (new route in the `auth` group, not `verified`), a small controller method (or reuse `ProfileController::destroy` on a route without `verified`), `App\Actions\DeleteUser`.
- **Constraint**: log out before deleting (`Auth::logout()` saves a new remember token and would re-insert a deleted user; see `ProfileController::destroy`).
- **Related**: FEAT-013 (deferred account creation), FEAT-010.
