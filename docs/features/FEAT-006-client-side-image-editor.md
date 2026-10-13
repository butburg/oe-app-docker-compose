---
id: FEAT-006
title: Client-Side Image Editors
status: backlog
priority: medium
category: feature
assignee: unassigned
created: 2026-10-13
updated: 2026-10-13
tags:
  - image-editing
  - upload
  - client-side
---

# Client-Side Image Editors

## Overview
Add an in-browser editing step to the post upload flow so users can prepare images before they are sent to the server.

## Problem Statement
The current upload flow does not provide common image adjustments such as cropping or a sepia treatment. Server-side image processing is also constrained by the production PHP host's memory limit.

## Acceptance Criteria
- [ ] Agree on an editor interaction concept (desktop and mobile screenshots) before implementation, including crop controls, preview, cancel/reset, and confirm behavior.
- [ ] Preview the selected image and provide a reversible crop tool; crop boundaries and the resulting output must match what the preview shows.
- [ ] Offer a filter (like sepia) treatment with a clear original/reset state; define whether it is a simple toggle or adjustable before implementation.
- [ ] Keep the original file unchanged until the user confirms; cancel/reset must not silently replace the original.
- [ ] Apply crop/filter and then compression in the browser before upload; retain server-side validation and do not add heavy image processing to PHP.
- [ ] Handle large files, unsupported or malformed formats, browser/API failures, and output-encoding failures with clear feedback and a path to continue or remove the image.
- [ ] Work with pre-upload image management and preserve image ordering and cover selection after edits, cancel, removal, and retry.
- [ ] Correctly handle image orientation and transparency where supported; revoke temporary object URLs and release editor resources when an image is replaced or the editor closes.
- [ ] Verify output format, dimensions, and size against upload validation limits; do not silently produce an upload that the server will reject.

## Risks and Failure Modes
- Decoding a very large source image can exhaust browser memory even if the final upload is compressed; test realistic high-resolution inputs and fail gracefully without freezing the upload flow.
- EXIF orientation can make the displayed crop differ from the uploaded result; normalize orientation before crop coordinates are applied.
- Repeated edits can degrade quality if each operation uses the previous compressed output; retain an editable source until confirmation and export once from that source.
- Canvas export may drop metadata, alter color profiles, or flatten transparency; choose and document acceptable behavior rather than implying a lossless edit.
- A browser may not support the chosen image format or canvas operation; limit supported formats explicitly and keep the original available when editing cannot proceed.
- Parallel client-side edit/compression work can race with reordering or submission; associate results with the selected image and block submission until pending edits finish or are canceled.
- A third-party editor library can add bundle weight, licensing obligations, maintenance risk, or incompatible mobile behavior; evaluate these before adoption.

## Open Product Decisions
- Should cropping be freeform, use fixed aspect ratios, or offer both? If fixed ratios, which ones matter for post images?
- Is sepia a single on/off preset or should users adjust its strength?
- Which input formats and maximum source dimensions/filesizes should be supported, and what output format/quality should be used?
- Should users be able to reopen and revise an edit before upload, or is confirm final for that upload session?
- What minimum browser support is required, especially for mobile Safari and older devices?
- Should edits be available only during post creation, or also for already-published images?

## Technical Implementation Notes
- **Affected files / components**: Post upload views and client-side assets; integrate with `FEAT-001` and `FEAT-002`.
- **Dependencies or constraints**: Prefer a maintained browser image-editing library if it fits the app's bundle, licensing, mobile support, and maintenance needs. Do not move heavy image processing onto the PHP host; continue validating uploads server-side. Define explicit source/output limits that match server validation before implementation.