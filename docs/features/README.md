# Features Directory

This directory stores project feature specifications. Each feature is managed in its own Markdown file with frontmatter metadata at the top. The Kanban board reads and writes these files directly, so the Markdown remains the source of truth.

## File Naming Convention

```
FEAT-<number>-<short-slug>.md
```
Examples:
- `FEAT-001-client-side-image-compression.md`
- `FEAT-002-pre-upload-image-management.md`

## Metadata Schema (Frontmatter)

Every feature file begins with YAML frontmatter:

```yaml
---
id: FEAT-001
title: Feature Title
status: backlog         # Allowed: backlog | todo | in-progress | review | done
priority: medium        # Allowed: low | medium | high
category: feature       # Allowed: feature | enhancement | bugfix | ui | security
assignee: unassigned
created: 2026-10-13
updated: 2026-10-13
tags:
  - frontend
  - images
---
```

### Supported Statuses (Kanban Columns)
- `backlog`: Ideas and planned features not yet scheduled.
- `todo`: Ready to be worked on next.
- `in-progress`: Currently being developed.
- `review`: Implemented, testing or code review pending.
- `done`: Completed and deployed.

## Creating a New Feature

1. Copy [`template.md`](template.md) to a new file, e.g. `FEAT-005-my-feature.md`.
2. Fill out the YAML frontmatter fields (especially `id`, `title`, and `status`).
3. Describe the overview, problem statement, acceptance criteria, and technical plan.
4. From the repository root, run `python3 docs/kanban/server.py`, then open [`../kanban/index.html`](http://localhost:8765/docs/kanban/index.html). The board reads and writes this folder directly through a repo-relative local API.

The board only treats files named `FEAT-<number>-<slug>.md` as tickets; it ignores this README and `template.md`. No folder picker or manual import is needed.
