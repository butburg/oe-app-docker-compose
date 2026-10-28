---
id: FEAT-009
title: Discourage saving gallery images (right-click, drag, overlay)
status: backlog # backlog | todo | in-progress | review | done
priority: low # low | medium | high
category: enhancement # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-28
updated: 2026-10-28
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
- [ ] Right-click on a gallery, zoom, profile or About image does not open the browser's image menu (no "Save image as…" / "Open image in new tab").
- [ ] Images cannot be dragged out of the page (desktop) and long-press does not offer "Save image" (mobile Safari/Chrome).
- [ ] Zoom still works: click opens, click closes, keyboard (Enter/Space) still opens.
- [ ] Right-click elsewhere (text, links, inputs) keeps working normally.
- [ ] Alt texts stay intact for screen readers.
- [ ] Applies everywhere `image_or_placeholder` renders plus the About screenshots, not to the logo or UI icons.
- [ ] Tests and validation passed (container), manual check in desktop and mobile browser.

## Technical Implementation Notes
- **Affected files / components**: `resources/views/components/image_or_placeholder.blade.php` (normal and zoom image), `resources/views/about.blade.php` (screenshots), `resources/css/app.css` (shared class), possibly `resources/js/app.js`.
- **Approach**: a shared `.app-protected-image` wrapper with a transparent `absolute inset-0` overlay above the image; move the zoom `@click`/keyboard handlers from the `<img>` to the wrapper so the overlay does not break zoom. On the `<img>`: `draggable="false"`, `select-none`, `[-webkit-touch-callout:none]`, and `@contextmenu.prevent` on the wrapper.
- **Alternative**: no overlay, only one global `contextmenu`/`dragstart` listener in `app.js` for images with a `data-protected` attribute. Less markup, same effect; the overlay adds protection against "inspect → open image" for less technical users only marginally.
- **Constraints**: keep the zoom overlay's own click handling (`@click.stop`) working; check the hamster easter egg is unaffected (it is already `pointer-events-none`).
