---
id: FEAT-009
title: Discourage saving gallery images (right-click, drag, overlay)
status: done # backlog | todo | in-progress | review | done
priority: low # low | medium | high
category: enhancement # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-28
updated: 2026-10-29
tags:
  - frontend
  - images
  - gallery
---

# Discourage saving gallery images (right-click, drag, overlay)

## Overview
Make it harder to casually save the artists' images: no "Save image as…" on right-click, no dragging an image to the desktop, and a transparent overlay so the image itself is never the click target.

## Problem Statement
Right now nothing protects the images. Every `<img>` (gallery, zoom view, profiles, My Images, About screenshots) can be right-clicked, saved or dragged out. A light protection shows respect for the uploaded work.

This is a deterrent, not real protection: the files are public under `storage/`, so dev tools, the network tab, screenshots or disabling JavaScript still get the image. Do not promise users more than that.

## Acceptance Criteria
- [x] Right-click on a gallery, zoom, profile or About image does not open the browser's image menu (no "Save image as…" / "Open image in new tab").
- [x] Images cannot be dragged out of the page (desktop) and long-press does not offer "Save image" (mobile Safari/Chrome).
- [x] Zoom still works: click opens, click closes, keyboard (Enter/Space) still opens.
- [x] Right-click elsewhere (text, links, inputs) keeps working normally.
- [x] Alt texts stay intact for screen readers.
- [x] Applies everywhere `image_or_placeholder` renders plus the About screenshots, not to the logo or UI icons.
- [x] Tests and validation passed (container), manual check in desktop and mobile browser.

## Technical Implementation Notes
- **Affected files / components**: `resources/views/components/image_or_placeholder.blade.php` (normal and zoom image), `resources/views/about.blade.php` (screenshots), `resources/css/app.css` (shared class), possibly `resources/js/app.js`.
- **Approach**: a shared `.app-protected-image` wrapper with a transparent `absolute inset-0` overlay above the image; move the zoom `@click`/keyboard handlers from the `<img>` to the wrapper so the overlay does not break zoom. On the `<img>`: `draggable="false"`, `select-none`, `[-webkit-touch-callout:none]`, and `@contextmenu.prevent` on the wrapper.
- **Alternative**: no overlay, only one global `contextmenu`/`dragstart` listener in `app.js` for images with a `data-protected` attribute. Less markup, same effect; the overlay adds protection against "inspect → open image" for less technical users only marginally.
- **Constraints**: keep the zoom overlay's own click handling (`@click.stop`) working; check the hamster easter egg is unaffected (it is already `pointer-events-none`).

## Decision
Went with the alternative (global script), since it does not touch the zoom click handling at all:
- Every protected `<img>` carries `data-protected` (normal, zoomable and zoom image in `image_or_placeholder`, About screenshots, Impressum contact images). The broken-image placeholder, logo, icons and hamsters are not marked.
- `resources/js/app.js`: one `contextmenu` and one `dragstart` listener on `document` that call `preventDefault()` only when the target is an `img[data-protected]`. Click and keyboard events are never touched, so zoom keeps working.
- `resources/css/app.css`: `img[data-protected]` gets `-webkit-touch-callout: none` (iOS long-press menu), `-webkit-user-drag: none` and `user-select: none`.
- The zoomable image now also opens on Enter/Space (`@keydown.enter`/`@keydown.space`); before, it was focusable but only reacted to clicks.

## Impressum text images

The contact and mail images on the impressum carry personal data, so besides the save deterrent they are rendered by `php artisan impressum:image` (`app/Console/Commands/GenerateImpressumImage.php`): every character gets its own rotation, height offset and spacing on a noisy background with strike lines. This stops simple OCR scrapers while people can still read it; it does not stop modern OCR or AI models. The text is passed only as arguments (one per line) so it never lands in the public repository. Font: Figtree Medium (`resources/fonts`, SIL OFL).
