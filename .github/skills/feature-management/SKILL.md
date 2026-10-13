---
name: feature-management
description: 'Manage, create, and update feature files with YAML frontmatter for the Oh Eweedy Kanban board. Use when creating new features, updating feature status, or inspecting the feature backlog.'
user-invocable: true
---

# Feature Management Skill

## When to Use
- Adding new feature ideas, enhancements, or bugfix tasks to the project.
- Updating the status (`backlog`, `todo`, `in-progress`, `review`, `done`) of a feature.
- Querying or auditing the current feature backlog.
- Formatting feature files for compatibility with the Kanban board GUI (`docs/kanban/index.html`).

## Workflow

### 1. Adding a New Feature
1. Determine the next feature ID by inspecting `docs/features/` (e.g. `FEAT-005`).
2. Copy the template from `docs/features/template.md` to `docs/features/FEAT-<id>-<slug>.md`.
3. Set the YAML frontmatter:
   - `id`: Unique identifier (e.g., `FEAT-005`)
   - `title`: Short descriptive title
   - `status`: Default to `backlog` or `todo`
   - `priority`: `low`, `medium`, or `high`
   - `category`: `feature`, `enhancement`, `bugfix`, `ui`, or `security`
   - `created`: Current date (`YYYY-MM-DD`)
   - `updated`: Current date (`YYYY-MM-DD`)
   - `tags`: Array of relevant tags
4. Write clear acceptance criteria and implementation notes referencing relevant files in `src/`.

### 2. Updating Feature Status
1. Open the relevant `docs/features/FEAT-*.md` file.
2. Update the `status` field in the frontmatter.
3. Update `updated` date.
4. Mark completed acceptance criteria checkboxes (`[x]`).

### 3. Visualizing on the Kanban Board
- `docs/features/FEAT-*.md` is the source of truth; board reads/writes relative to the repository through `docs/kanban/server.py`.
- Start from repo root with `python3 docs/kanban/server.py`; open `http://localhost:8765/docs/kanban/index.html`.
- Board edits and drag/drop status changes save the matching Markdown file and update `updated`; do not add seed data to HTML.
- New cards create `FEAT-###-<slug>.md`; `template.md` and `README.md` are not tickets.
- After changes, reload the board and verify the file and card metadata.
