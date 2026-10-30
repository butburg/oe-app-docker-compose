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
4. Open the Kanban board (see below) to see it in its column.

## Opening the Kanban Board

```bash
python3 docs/kanban/server.py   # from the repository root, stop with Ctrl+C
```

Then open http://localhost:8765/docs/kanban/index.html, either in a normal browser (works from WSL too) or inside VS Code via `Ctrl+Shift+P` → **Simple Browser: Show**. Opening `index.html` directly as a file does not work, the board needs the server for its `/api/features` calls.

Sync:
- Changes on the board (drag to another column, edits) are written to the Markdown file right away.
- Changes to the Markdown files (saved in the editor, auto save, git checkout) show up on the board by themselves within about a second. This includes an open detail dialog; unsaved input there gives way to the file. The server watches `docs/features` and tells the open page through `/api/events`.

The board only treats files named `FEAT-<number>-<slug>.md` as tickets; it ignores this README and `template.md`. No folder picker or manual import is needed.
