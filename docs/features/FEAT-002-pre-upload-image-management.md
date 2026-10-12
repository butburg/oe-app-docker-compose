---
id: FEAT-002
title: Pre-Upload Image Management
status: in-progress
priority: medium
category: ui
assignee: unassigned
created: 2026-10-13
updated: 2026-10-13
tags:
  - upload
  - ui
  - images
---

# Pre-Upload Image Management

## Overview
Provide an interactive staging area in the creation flow where users can manage selected images before generating and uploading the post.

## Problem Statement
Currently, users cannot easily preview, reorder, or choose which image serves as the title/cover thumbnail before uploading. If an unwanted image is selected, they would have to start over.

## Acceptance Criteria
- [ ] Show image thumbnails immediately after selection.
- [ ] Allow reordering images via drag-and-drop or move buttons.
- [ ] Allow removing individual images before submission.
- [ ] Allow selecting a primary "title image" (featured thumbnail).
- [ ] Integration with client-side compression pipeline.

## Technical Implementation Notes
- Blade upload form in `resources/views/` and Alpine.js / Vanilla JS preview state.
- Update form data packaging before submit.
