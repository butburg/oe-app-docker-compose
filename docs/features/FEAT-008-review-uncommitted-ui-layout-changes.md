---
id: FEAT-008
title: Review uncommitted UI/layout changes and fix navigation issues
status: done # backlog | todo | in-progress | review | done
priority: high # low | medium | high
category: ui # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-28
updated: 2026-10-28
tags:
  - ui
  - navigation
  - review
---

# Review uncommitted UI/layout changes and fix navigation issues

## Overview
About 54 files with uncommitted UI refresh changes (new design tokens, `app-*` component classes, Figtree font, gallery search, avatar `scaleDown`) need a visual review before committing. Brand: VidiGallery = Weedy Gallery.

## Problem Statement
The refresh has regressions and inconsistencies found on first look.

## Acceptance Criteria
- [x] Login and Register pages show the explanatory block below the form again ("VidiGallery, The Ultimate Art Platform" / what the Weedy Gallery is). It is missing in `layouts/guest.blade.php`.
- [x] Navigation: the "VidiGallery" link is a normal nav item like Login/Register: plain text, white and underlined only when active, hover effect, and no longer styled as part of the logo.
- [x] Navigation: the "Search Gallery" item is vertically centered in the nav bar (currently sticks to the top).
- [x] Review the remaining uncommitted views page by page (auth, gallery, profile, admin, posts, impressum, pagination) on desktop and mobile.
- [x] Gallery search (`q`) is tested incl. umlauts and upper/lower case on MySQL (`like` behaviour). (feature tests green on SQLite and MySQL 8.0)
- [x] Confirm the avatar change in `StoreImage.php` (`coverDown` replaced by `scaleDown`) is intended. (not intended: reverted to original `coverDown`)
- [x] Check the modified `starfish.svg` is not an accidental change. (new color kept; moved to `public/images/` so it deploys)
- [x] Run `PostControllerTest` only against an isolated DB (it uses `RefreshDatabase`; `phpunit.xml` has no DB override). (`phpunit.xml` now uses SQLite `:memory:`)
- [x] Split the commits (UI, search, avatar processing).
- [x] Search field shows no native clear (X) button.
- [x] Gallery date popover is readable (dark text on cyan) and uses the European format `d.m.Y` (comments too).
- [x] Profile "About Me" heading matches its text tone and sits with the text, not with the stats.
- [x] Page headings are visually hidden (`sr-only`), the navigation shows the current page; "Create"/"Edit" heading lives in the form.
- [x] Logged in, the nav lists Weedy Gallery, My Images, Profile, Admin and Log Out directly; below `lg` everything moves into the hamburger menu with the user name small and grey at the bottom.

### Hidden reductions (VR) found in the review
- [x] VR1: gallery heading "Weedy Gallery" became "Gallery"; nav link is hard-coded "Gallery" instead of `config('app.name')` (both use `config('app.name')` again).
- [x] VR2: create button visible for guests again (nav, redirects to login; hidden on login/register/password pages).
- [x] VR3: profile image back to `h-80 w-80` (profile page and edit preview).
- [x] VR4: profile edit has alternating section backgrounds again (`app-surface-accent`, same as the login info block).
- [x] VR5: serif font Nanum Myeongjo removed from headings. Accepted: the font is designed for Korean, not Latin script.
- [x] VR6: My Images action buttons have their icons ▽ ✎ ✕ △ and the thick left border again (`app-button-bar`, new palette colors).

## Technical Implementation Notes
- **Affected files**: `src/resources/views/layouts/guest.blade.php`, `src/resources/views/layouts/navigation.blade.php`, `src/resources/views/components/nav-link.blade.php`, `src/resources/css/app.css`, `src/tailwind.config.js`, `src/app/Http/Controllers/PostController.php`, `src/app/Actions/StoreImage.php`, `src/resources/views/posts/index.blade.php`, `src/resources/views/profile/show.blade.php`, `src/resources/views/profile/partials/display-description.blade.php`, `src/resources/views/profile/partials/update-profile-image-form.blade.php`, `src/resources/views/components/gallery/gallery.blade.php`, `src/resources/views/components/gallery/comment/comment-creator.blade.php`.
- **Constraints**: After CSS/Tailwind changes rebuild with `docker compose run --rm npm run build`; local URL is `http://localhost` (`APP_URL`/`ASSET_URL`).
- **Related**: FEAT-005 (profile gallery UI refresh).
