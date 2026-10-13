---
name: concept-design
description: 'Create and review responsive UI concepts before implementation; use for visual proposals, screenshots, and design approval.'
user-invocable: true
---

# Concept Design

## Workflow
1. Read the feature spec and inspect the real view, navigation, theme, and components before designing.
2. Create or update a focused static concept in `docs/concepts/`; use the app's real brand, assets, and interaction patterns.
3. Make every label serve a user or navigation purpose. Remove editorial annotations, generic subtitles, and concept-only copy from the rendered UI.
4. Render and inspect desktop and mobile screenshots; test narrow widths, text contrast, wrapping, asset loading, and representative long content.
5. Share the concept and screenshots for review. Resolve open design decisions and record approval in the feature spec before implementation.
6. Do not change application UI code before approval unless the user explicitly asks to skip the concept gate.

## Local Preview
- From the repository root: `python3 -m http.server 8765`
- Concept URL: `http://localhost:8765/docs/concepts/<file>.html`
- Verify rendered assets and layout in the browser; a source-only check is not screenshot approval.