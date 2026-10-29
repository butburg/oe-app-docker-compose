---
id: FEAT-015
title: Strip EXIF and GPS data from uploaded images
status: backlog # backlog | todo | in-progress | review | done
priority: high # low | medium | high
category: security # feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-29
updated: 2026-10-29
tags:
  - images
  - privacy
  - security
---

# Strip EXIF and GPS data from uploaded images

## Overview
Every stored image variant is free of metadata: no EXIF (camera, date, serial number), no GPS location, no XMP/IPTC comments. Only the colour profile may stay.

## Problem Statement
Images are processed with Intervention Image 3.9.1 and the Imagick driver (`config/image.php`). Imagick keeps metadata when re-encoding unless it is stripped, and Intervention 3.9.1 has no `strip` option. Photos taken with a phone usually contain the GPS position, so a published post can reveal where a user lives. Client-side compression (FEAT-001) cannot be relied on, because the server must not trust the client.

## Acceptance Criteria
- [ ] `StoreImage::handleStore` removes all metadata before encoding, for post and profile images and every size variant.
- [ ] The orientation from EXIF is applied first (auto-orientation), so images are not rotated wrongly after stripping.
- [ ] The ICC colour profile is kept or converted to sRGB, so colours do not shift.
- [ ] Existing images on prod are cleaned once (artisan command over all variants, idempotent, with dry-run option); deployment step documented.
- [ ] Test with a fixture JPEG that contains EXIF incl. GPS: no stored variant contains EXIF/GPS (checked with `exif_read_data` or Imagick `getImageProperties('exif:*')`), orientation is correct.
- [ ] Tests and validation passed (container).

## Technical Implementation Notes
- **Affected files / components**: `app/Actions/StoreImage.php`, new artisan command for existing files, `tests/` fixture image with GPS data.
- **Approach**: before `toJpeg()`: `$image->core()->native()` → Imagick: keep the ICC profile (`getImageProfile('icc')`), `stripImage()`, then set the profile again. Alternative: upgrade `intervention/image` to a version with the `strip` option and check that it keeps the ICC profile.
- **Order**: auto-orientation happens on `Image::read()` (Intervention default), so stripping afterwards is safe. Verify in the test.
- **Related**: FEAT-001 (client-side compression), FEAT-011 (image links).
