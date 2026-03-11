---
name: setup-claude-md
description: Initialize CLAUDE.md at WordPress root — create or add imports from theme and plugin
---

Set up `CLAUDE.md` at the WordPress project root.

**WordPress root:** current working directory (e.g. `c:/laragon/www/codeweber2026/`)

## Step 1: Find all CLAUDE.md files in the project

Check which of these exist:
- `wp-content/themes/codeweber/CLAUDE.md`
- `wp-content/plugins/codeweber-gutenberg-blocks/CLAUDE.md`

Only add imports for files that actually exist.

## Step 2: Check root CLAUDE.md

Check if `CLAUDE.md` exists at the WordPress root.

## Step 3: If root CLAUDE.md does NOT exist

Create it with `@` imports for all found CLAUDE.md files:

```markdown
# CLAUDE.md

@wp-content/themes/codeweber/CLAUDE.md
@wp-content/plugins/codeweber-gutenberg-blocks/CLAUDE.md
```

(Only include lines for files that actually exist.)

## Step 4: If root CLAUDE.md EXISTS

Read it and check for each import line:
- `@wp-content/themes/codeweber/CLAUDE.md`
- `@wp-content/plugins/codeweber-gutenberg-blocks/CLAUDE.md`

For each missing import (where the source file exists) — add the line after the existing imports.
If all imports are already present — report everything is ready, make no changes.

## Result

Report: created / updated / already configured — and which import lines were added.
