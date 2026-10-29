---
id: FEAT-016
title: Branded, lightweight mail layout
status: done # backlog | todo | in-progress | review | done
priority: medium # low | medium | high
category: ui # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - mail
  - design
  - branding
---

# Branded, lightweight mail layout

## Overview
All mails share one layout in the Weedy Gallery look: logo, two-part wordmark ("Weedy" in the accent colour, "Gallery" in the text colour), the app's colours and button style. Only the content in the middle changes per mail. The CSS is kept as short as possible so mails stay small.

## Problem Statement
The mails (`ConfirmRegistration`, `ConfirmNewEmail`, `AccountAlreadyExists`) already use Laravel's Markdown mail components (`<x-mail::message>`, `<x-mail::button>`), but with Laravel's default theme: grey Laravel look, no logo, no brand colours, and a long CSS file with a long font stack. Laravel inlines this CSS into every element, so it inflates every mail.

## Acceptance Criteria
- [x] Mail components are published (`php artisan vendor:publish --tag=laravel-mail`) to `resources/views/vendor/mail`; files that are not changed are deleted again so they keep following the framework.
- [x] Header: logo (small PNG, absolute URL, fixed `width`/`height`, `alt` = app name) and the two-part wordmark as **text** (same split as `x-app-wordmark`: first word in accent colour), so the brand shows even when images are blocked.
- [x] Footer: app name, link to the app, short note why the mail was sent; slot for a per-mail line (e.g. link to mail settings, see FEAT-018/019).
- [x] Own theme `resources/views/vendor/mail/html/themes/weedy.css`, set in `config/mail.php` (`markdown.theme`). Only the rules that are actually used (body, wrapper, header, content, button, footer, links, headings, paragraph). Target: well under the default theme's size.
- [x] Font: exactly one web font name and one fallback: `font-family: Figtree, sans-serif;`. No `@import` / `<link>` of web fonts (most clients ignore them, and they add size).
- [x] Colours from the site tokens in `resources/css/app.css` as hex values: background `#071D20`, surface `#0D292D`, text `#ECF7F5`, muted `#A9C3C2`, line `#285055`, primary `#00D9FF` (buttons/links), accent `#A5E04D` (wordmark). Button text in background colour on primary, as `app-button-primary`.
- [x] ~~Readable in light and dark mail clients~~ Decision (2026-10-29): no separate light/dark variant, the dark layout is the default for all clients. Dark background is set as `bgcolor` on body, wrapper, card and button cell, plus `color-scheme: dark`.
- [x] Plain-text part stays useful (Markdown mails already generate it; check wordmark/footer text there).
- [x] All existing mails use the new layout without changes to their content files.
- [x] Mail size before/after noted for one mail (e.g. `ConfirmRegistration`, HTML bytes from MailHog).
- [x] Preview route for local only (e.g. `/dev/mail/{mail}` rendering each mailable) or documented MailHog check; not reachable in production.
- [x] Tests (rendering each mailable, brand text present, no default Laravel logo) and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `resources/views/vendor/mail/html/{header,footer,layout,button,message}.blade.php`, `resources/views/vendor/mail/html/themes/weedy.css`, `config/mail.php`, `resources/views/emails/*.blade.php` (only if needed), a small mail logo in `public/images/mail/` (e.g. 96×96 PNG, shown at 48×48).
- **Logo**: hosted image (absolute `asset()` URL) instead of an embedded CID attachment, to keep the mail small. `APP_URL` must be the public URL in production.
- **Inlining**: Laravel inlines the theme CSS via CssToInlineStyles, so every rule is repeated per element. Short selectors and few rules matter more than in normal CSS.
- **Not a goal**: pixel match with the website. Colours, logo and wordmark must match; spacing and font rendering may differ.
- **Implemented**: changed components only (`html/{layout,header,footer,message,button}`, `text/message`), the rest falls back to the framework. `x-mail::message` takes an optional `notice` slot for the footer line (default: "You get this mail because this address was used on Weedy Gallery."). Theme `weedy.css` is 1.3 KB (default theme 4.6 KB). Logo `public/images/mail/logo.png`: 96×96 PNG8, 4.4 KB, made from `favicons/android-chrome-512x512.png`. Laravel's own notification mails (password reset, verification) use the same components, so they get the layout too.
- **Size**: `ConfirmRegistration` HTML **9,374 → 4,401 bytes**.
- **Preview**: `/dev/mail` lists all mails, `/dev/mail/{name}` renders one with sample data; the routes only exist when `APP_ENV=local`. Screenshots: `docs/features/screenshots/FEAT-016-*.png`.
- **Tests**: `tests/Feature/Mail/MailLayoutTest.php`.
- **Related**: FEAT-017/018/019 (new mails use this layout).
