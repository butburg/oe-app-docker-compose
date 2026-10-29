---
id: FEAT-016
title: Branded, lightweight mail layout
status: backlog # backlog | todo | in-progress | review | done
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
- [ ] Mail components are published (`php artisan vendor:publish --tag=laravel-mail`) to `resources/views/vendor/mail`; files that are not changed are deleted again so they keep following the framework.
- [ ] Header: logo (small PNG, absolute URL, fixed `width`/`height`, `alt` = app name) and the two-part wordmark as **text** (same split as `x-app-wordmark`: first word in accent colour), so the brand shows even when images are blocked.
- [ ] Footer: app name, link to the app, short note why the mail was sent; slot for a per-mail line (e.g. link to mail settings, see FEAT-018/019).
- [ ] Own theme `resources/views/vendor/mail/html/themes/weedy.css`, set in `config/mail.php` (`markdown.theme`). Only the rules that are actually used (body, wrapper, header, content, button, footer, links, headings, paragraph). Target: well under the default theme's size.
- [ ] Font: exactly one web font name and one fallback: `font-family: Figtree, sans-serif;`. No `@import` / `<link>` of web fonts (most clients ignore them, and they add size).
- [ ] Colours from the site tokens in `resources/css/app.css` as hex values: background `#071D20`, surface `#0D292D`, text `#ECF7F5`, muted `#A9C3C2`, line `#285055`, primary `#00D9FF` (buttons/links), accent `#A5E04D` (wordmark). Button text in background colour on primary, as `app-button-primary`.
- [ ] Readable in light and dark mail clients (Gmail web, Gmail app, Outlook, Apple Mail): dark background set on the wrapper table, not only on `body`; contrast checked.
- [ ] Plain-text part stays useful (Markdown mails already generate it; check wordmark/footer text there).
- [ ] All existing mails use the new layout without changes to their content files.
- [ ] Mail size before/after noted for one mail (e.g. `ConfirmRegistration`, HTML bytes from MailHog).
- [ ] Preview route for local only (e.g. `/dev/mail/{mail}` rendering each mailable) or documented MailHog check; not reachable in production.
- [ ] Tests (rendering each mailable, brand text present, no default Laravel logo) and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `resources/views/vendor/mail/html/{header,footer,layout,button,message}.blade.php`, `resources/views/vendor/mail/html/themes/weedy.css`, `config/mail.php`, `resources/views/emails/*.blade.php` (only if needed), a small mail logo in `public/images/mail/` (e.g. 96×96 PNG, shown at 48×48).
- **Logo**: hosted image (absolute `asset()` URL) instead of an embedded CID attachment, to keep the mail small. `APP_URL` must be the public URL in production.
- **Inlining**: Laravel inlines the theme CSS via CssToInlineStyles, so every rule is repeated per element. Short selectors and few rules matter more than in normal CSS.
- **Not a goal**: pixel match with the website. Colours, logo and wordmark must match; spacing and font rendering may differ.
- **Related**: FEAT-017/018/019 (new mails use this layout).
