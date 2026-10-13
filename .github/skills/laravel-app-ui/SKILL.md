---
name: laravel-app-ui
description: 'Implement and validate UI changes in the running Oh Eweedy Laravel app while preserving existing behavior.'
user-invocable: true
---

# Laravel App UI

## Scope
- Use when the user asks to change the actual website or implement an approved design.
- `docs/concepts/` is reference only; once implementation is requested, do not keep editing concepts unless explicitly asked.
- App entry points: `src/resources/views/`, `src/resources/css/app.css`, `src/tailwind.config.js`, `src/routes/`, `src/app/`.

## Product invariants
- Brand appears once in the topbar; route title belongs to the page heading. Gallery title = `Gallery`; posts area = `My Images`; account route = `Profile settings`.
- Fixed dark/neutral palette; no system light-mode override. Sans-serif Figtree throughout.
- Preserve current routes, collapsed mobile comments, publish/edit/delete/protection/share/date/comment-count controls, pagination, and zoom.
- Never crop artwork in gallery, post lists, or previews; use `scaleDown` for stored variants and `object-contain` in artwork thumbnails. `cover` is for avatars only.
- No Follow, image-overlay tags, or breadcrumbs.

## Workflow
1. Inspect the active Laravel route and owning Blade/component; identify one specific defect before editing.
2. Make the smallest app-only change; validate that slice immediately before continuing.
3. Check nav/title alignment, icon centers, wrapping, focus, image natural/rendered ratios, and measured contrast.
4. Browser widths: 320, 375, 640, 768, 900, 1024, 1280, 1440; test both sides of breakpoints.
5. Reload and screenshot the actual app (`:8000`), not only the concept (`:8765`). Vite HMR = `:5173`.
6. Keep existing user changes; do not commit unless asked. State exactly what was tested and what remains.
