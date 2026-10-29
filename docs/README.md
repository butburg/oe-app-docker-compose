# Project Documentation & Features

This directory contains project documentation, knowledge base notes, and the feature backlog for the Oh Eweedy application.

## Directory Structure

```
docs/
├── README.md               # This directory index
├── CHANGELOG.md            # Version history and changelog
├── LaravelRemember.md      # Personal learnings and Laravel knowledge base
├── commands_list.txt       # Cheat sheet of frequently used docker/artisan commands
├── server-cronjobs.md      # Cron jobs to set up at the hoster (queue worker)
├── features/               # Individual feature specification files
│   ├── README.md           # Instructions for writing feature files
│   ├── template.md         # Template for new features
│   ├── FEAT-001-client-side-image-compression.md
│   ├── FEAT-002-pre-upload-image-management.md
│   ├── FEAT-003-ai-ad-creation-flow.md
│   └── FEAT-004-lightweight-kanban-board.md
├── kanban/
│   └── index.html          # Lightweight local Kanban board GUI (open in browser)
└── templates/              # Visual templates and wireframes
    └── vorlage_bildbeschriftung.jpg
```

## Features & Kanban Board

- **Add a feature**: Duplicate `features/template.md` and set its YAML frontmatter (ID, title, status, priority, tags).
- **Kanban Board GUI**: Open `kanban/index.html` in any browser to visualize your features across columns (`backlog`, `todo`, `in-progress`, `review`, `done`), drag cards between columns, and export/update `.md` files.
