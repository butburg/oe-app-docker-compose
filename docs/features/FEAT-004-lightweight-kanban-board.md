---
id: FEAT-004
title: Lightweight Local Kanban Board Viewer
status: done
priority: low
category: feature
assignee: unassigned
created: 2026-10-13
updated: 2026-10-13
tags:
  - kanban
  - productivity
  - tooling
---

# Lightweight Local Kanban Board Viewer

## Overview
A zero-dependency, fast, self-contained HTML/JS single-page Kanban application to visualize and manage markdown feature files without third-party SaaS overhead.

## Problem Statement
Standard project management tools (Jira, Linear, Taiga, etc.) introduce external overhead, account management, and disconnect task state from git repository files. Managing features directly in git-tracked markdown files keeps everything in sync with the codebase.

## Acceptance Criteria
- [ ] Self-contained single-page HTML file viewable directly in browser or local server.
- [ ] Renders columns: Backlog, Todo, In Progress, In Review, Done.
- [ ] Reads feature files (or imports markdown/folder using modern browser File System Access API / drag-and-drop).
- [ ] Allows editing status and exporting/saving markdown with updated frontmatter.
- [ ] Fast, clean, dark-mode/light-mode UI.

## Technical Implementation Notes
- Pure HTML, CSS, and Vanilla JS in `docs/kanban/index.html`.
- Parses YAML frontmatter regex-free or with minimal parser.
- Generates compliant markdown on download or copy.
