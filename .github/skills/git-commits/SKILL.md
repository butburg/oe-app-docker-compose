---
name: git-commits
description: 'Writing commit messages in this repo. Use whenever committing changes, e.g. new/updated skills, docs, or features.'
---

# Commit messages

Format: `<type>(<scope>): <description>` — imperative, lowercase, no trailing period, ≤72 chars.
Scope = skill/dir touched (e.g. `docs`, `features`, `kanban`, `skills`); omit if repo-wide.

Types: `feat` (new feature/skill), `fix` (corrected wrong info/bug), `docs`, `refactor` (reorganize without behavior change), `chore`.

Body (optional, blank line first): the *why*, not a restatement of the diff. Wrap at ~72 chars.

Breaking/behavior change → `!` after type, e.g. `fix!:`.

```
feat(kanban): add local html board and markdown feature parser

Replaces external project management tools with self-contained
git-tracked markdown specifications and lightweight browser viewer.
```

## Rules

- One logical change per commit — don't bundle unrelated edits.
- Never invent a scope/type that doesn't fit; drop the scope rather than force one.
- Never commit on your own initiative. Draft the message, show it, and wait for the user to say "commit"/"yes" — or for them to tell you to hold off. Applies even after a multi-step task that clearly ends in a commit.

## Docs

https://www.conventionalcommits.org/en/v1.0.0/
