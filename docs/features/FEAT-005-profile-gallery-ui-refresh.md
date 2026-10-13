---
id: FEAT-005
title: Profile and Gallery UI Refresh
status: in-progress
priority: high
category: ui
assignee: unassigned
created: 2026-10-13
updated: 2026-10-13
tags:
  - ui
  - accessibility
  - responsive
---

# Profile and Gallery UI Refresh

## Overview
Make a restrained visual refinement to the existing gallery and profile pages. Use `docs/concepts/profile-gallery-concept.html` only as a reference for typography and visual tone; preserve working layouts, routes, image proportions, and interactions rather than rebuilding them.

## Problem Statement
Profile details occupy too much vertical space, post previews are cramped, and several text/background combinations make titles and metadata difficult to scan, especially on mobile.

## Implementation Subtasks
- [x] Preserve current behavior: gallery image proportions and zoom, responsive comment collapse, pagination, profile details, and existing publish/edit/delete controls.
- [x] Refine heading typography using the existing Nanum Myeongjo font; keep body copy and metadata readable in the existing typeface.
- [x] Tighten only rough vertical spacing and text hierarchy on the gallery and profile views; keep the existing page structure and controls.
- [x] Test at 320px, 375px, 768px, and desktop widths; verify gallery width, image fit, and mobile comment toggling.

## Acceptance Criteria
- [ ] Existing gallery images retain their aspect ratio and zoom behavior; no image-cover cropping is introduced for artwork.
- [ ] Existing comment collapse/expand behavior, routes, pagination, and publish/edit/delete actions remain unchanged.
- [ ] No follow action, image-overlay tags, or breadcrumb navigation is introduced.
- [ ] Nanum Myeongjo remains the display typeface; body text, metadata, and controls retain the app's established visual language.
- [ ] Spacing and hierarchy are improved without replacing the existing page structure or adding a new styling framework.
- [ ] Validate responsive layout at 320px, 375px, 768px, and desktop; verify focus, zoom/reflow, and long user-generated text.

## Risks and Failure Modes
- A concept can look polished with sample content but break with long names, empty biographies, high post counts, missing images, or translated labels; include these cases in the review and implementation checks.
- Contrast can regress when muted text, tags, overlays, or interactive states are evaluated separately from their real backgrounds; record tested color pairs rather than relying on visual judgment.
- A compact profile could hide important identity or actions on mobile; validate the information order and action visibility at the narrowest viewport.
- Screenshot approval can be misleading if the concept uses remote/demo images or fonts that fail to load; keep the concept reviewable locally and use representative assets/content.
- Adding Bootstrap or another styling framework solely to imitate common patterns may conflict with the existing Tailwind styles and increase bundle/maintenance cost; prefer existing project conventions unless a separate decision justifies a dependency.

## Resolved Product Decisions
- Keep the existing theme behavior and responsive layouts; do not copy the concept's alternate mobile post layout.
- Follow is not a product feature and must not appear in the implementation or concept.
- Do not cover artwork with tags or crop gallery images to fit a tile.
- Keep the existing collapsible comments and post-management controls as implemented.

## Technical Implementation Notes
- **Affected files / components**: `src/resources/views/profile/show.blade.php`, `src/resources/views/components/gallery/gallery.blade.php`, `src/resources/views/layouts/app.blade.php`, and existing Tailwind styles as needed.
- **Dependencies or constraints**: The concept is not a pixel specification. Preserve existing routes, behavior, image aspect ratios, and color-scheme handling.