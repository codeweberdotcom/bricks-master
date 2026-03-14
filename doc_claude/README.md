# CodeWeber Theme Documentation

Complete documentation for the CodeWeber WordPress theme — a production-grade custom theme built on Bootstrap 5 with extensive CPT support, Gutenberg integration, and advanced features.

> **Claude Code instructions:** [CLAUDE.md](../../../../CLAUDE.md) — project-level instructions for Claude Code (environment, build commands, architecture overview).

## Quick Navigation

### 📐 Architecture & Setup
- **[THEME_OVERVIEW.md](architecture/THEME_OVERVIEW.md)** — Theme structure, entry points, design patterns
- **[FILE_LOADING_ORDER.md](architecture/FILE_LOADING_ORDER.md)** — Dependency graph and load sequence
- **[CHILD_THEME_GUIDE.md](architecture/CHILD_THEME_GUIDE.md)** — Creating and configuring child themes

### 🔧 Development
- **[LOCAL_SETUP.md](development/LOCAL_SETUP.md)** — Laragon setup, WordPress installation, WP-CLI
- **[BUILD_SYSTEM.md](development/BUILD_SYSTEM.md)** — Gulp, npm scripts, asset compilation
- **[CODING_STANDARDS.md](development/CODING_STANDARDS.md)** — Naming conventions, security practices

### 📦 Custom Post Types
- **[CPT_CATALOG.md](cpt/CPT_CATALOG.md)** — Complete catalog of all 18 CPT types
- **[CPT_HOW_TO_ADD.md](cpt/CPT_HOW_TO_ADD.md)** — 8-step recipe for adding new CPTs

### 🎨 Templates & Display
- **[TEMPLATE_SYSTEM.md](templates/TEMPLATE_SYSTEM.md)** — Header/footer selection, rendering logic
- **[POST_CARDS_SYSTEM.md](templates/POST_CARDS_SYSTEM.md)** — Post card templates, filters, shortcodes
- **[ARCHIVE_SINGLE_PATTERNS.md](templates/ARCHIVE_SINGLE_PATTERNS.md)** — Common template patterns

### ⚙️ Settings & Configuration
- **[REDUX_OPTIONS.md](settings/REDUX_OPTIONS.md)** — Theme options, Redux API, Codeweber_Options class
- **[ADMIN_PANELS.md](settings/ADMIN_PANELS.md)** — Redux panel structure, configuration

### 🔌 API & Hooks
- **[AJAX_FETCH_SYSTEM.md](api/AJAX_FETCH_SYSTEM.md)** — Frontend-to-backend data flow, action types
- **[REST_API_REFERENCE.md](api/REST_API_REFERENCE.md)** — REST endpoint catalog with parameters
- **[HOOKS_REFERENCE.md](api/HOOKS_REFERENCE.md)** — All custom filters and actions

### 📋 Forms & Integrations
- **[CODEWEBER_FORMS.md](forms/CODEWEBER_FORMS.md)** — Form lifecycle, submissions, validation
- **[CF7_INTEGRATION.md](forms/CF7_INTEGRATION.md)** — Contact Form 7 setup and customization
- **[DADATA.md](integrations/DADATA.md)** — Address standardization integration
- **[YANDEX_MAPS.md](integrations/YANDEX_MAPS.md)** — Yandex Maps setup and usage
- **[SMSRU.md](integrations/SMSRU.md)** — SMS.ru messaging integration
- **[MATOMO.md](integrations/MATOMO.md)** — Analytics tracking
- **[PERSONAL_DATA_V2.md](integrations/PERSONAL_DATA_V2.md)** — GDPR & privacy compliance
- **[NEWSLETTER.md](integrations/NEWSLETTER.md)** — Newsletter subscription system

### 🔒 Security
- **[SECURITY_CHECKLIST.md](security/SECURITY_CHECKLIST.md)** — Security best practices and patterns

## Directory Structure

```
wp-content/themes/codeweber/
├── functions.php                 # Entry point with require_once chain
├── functions/
│   ├── cpt/                     # Custom post type definitions (18 types)
│   ├── fetch/                   # AJAX fetch handlers (Codeweber\Functions\Fetch)
│   ├── integrations/            # Third-party integrations
│   ├── lib/                     # Nav walkers and helpers
│   ├── admin/                   # WordPress admin pages
│   ├── enqueues.php             # Script/style registration
│   ├── class-codeweber-options.php  # Redux wrapper class
│   └── [other core functions]
├── redux-framework/             # Redux Framework configuration
├── templates/
│   ├── post-cards/              # Post card templates by type
│   ├── header/                  # Header variations
│   ├── components/              # Reusable template parts
│   └── [page, single, archive templates]
└── [root level: single.php, archive.php, page.php, etc.]
```

## Getting Started

### 1. Development Setup (5 minutes)
```bash
cd wp-content/themes/codeweber
npm install
npm start
```
This runs Gulp in watch mode with BrowserSync hot-reload.

### 2. Understanding the Architecture (15 minutes)
Read **THEME_OVERVIEW.md** for the big picture, then **FILE_LOADING_ORDER.md** to understand initialization.

### 3. Common Tasks
- **Add a new CPT**: Follow **CPT_HOW_TO_ADD.md**
- **Create a new template**: See **TEMPLATE_SYSTEM.md** and **POST_CARDS_SYSTEM.md**
- **Add a custom hook**: Check **HOOKS_REFERENCE.md** first to avoid duplicates
- **Configure Redux options**: See **REDUX_OPTIONS.md** and **ADMIN_PANELS.md**

## Key Concepts

### Redux Framework Options
All theme settings are stored in Redux Framework with key `redux_demo`. Access via:
```php
$val = Codeweber_Options::get('setting_key', 'default_value');
```

### Child-First Pattern
Asset resolution prioritizes child theme over parent:
- `codeweber_get_dist_file_url()` — checks child dist/ first
- `get_theme_file_path()` — WordPress native child-first lookup

### Custom Post Types (18 total)
Header, Footer, PageHeader, Modal, HTMLBlock, Client, Notification, Staff, FAQ, Testimonial, Vacancy, Office, Service, Project, LegalDoc, Document, PriceList, plus post/page.

### AJAX Fetch System
Namespace: `Codeweber\Functions\Fetch`
- Endpoints: `/wp-admin/admin-ajax.php?action=fetch_action`
- Nonce security: `fetch_action_nonce`
- Dispatcher in `fetch-handler.php`

### Post Card Templates
Flexible system for rendering post cards with:
- Template name resolution (prefix-based + post-type-based)
- Fallback chain: specific → default → parent template
- Filters for full customization

## File Organization

Each documentation file is:
- **Task-oriented**: "How to do X?" not "What is X?"
- **Self-contained**: minimal cross-dependencies
- **Scannable**: clear headings, tables, code blocks
- **Practical**: real code examples from the codebase

## Key Classes & Functions

| Component | File | Purpose |
|-----------|------|---------|
| `Codeweber_Options` | `functions/class-codeweber-options.php` | Redux wrapper with get/get_post_meta/is_ready |
| `codeweber_get_dist_file_url()` | `functions/enqueues.php` | Child-first asset URL resolution |
| `cw_render_post_card()` | `functions/post-card-templates.php` | Post card rendering with template system |
| `handle_fetch_action()` | `functions/fetch/fetch-handler.php` | AJAX dispatcher for Codeweber\Functions\Fetch namespace |
| Redux Panels | `redux-framework/sample/theme-config.php` | Theme settings panels |

## Important File Paths

- **Theme root**: `/wp-content/themes/codeweber/`
- **Functions**: `/wp-content/themes/codeweber/functions/`
- **CPTs**: `/wp-content/themes/codeweber/functions/cpt/`
- **Templates**: `/wp-content/themes/codeweber/templates/`
- **Redux config**: `/wp-content/themes/codeweber/redux-framework/`
- **AJAX handlers**: `/wp-content/themes/codeweber/functions/fetch/`
- **Integrations**: `/wp-content/themes/codeweber/functions/integrations/`

## Tips for Reading This Documentation

1. **Start with**: THEME_OVERVIEW.md → FILE_LOADING_ORDER.md → your specific task
2. **Use cross-references**: Each file links to related documentation
3. **Check code examples**: All examples use actual paths from the codebase
4. **Search by folder**: Architecture → Development → CPT → your task

## Contributing to Documentation

When updating the theme:
1. Update relevant documentation files
2. Keep examples synchronized with actual code paths
3. Maintain the task-oriented format
4. Add code snippets with exact file paths and line numbers

---

**Last Updated**: 2026-03-11
**Theme Version**: Latest
**Environment**: Laragon + PHP 8.x + WordPress 6.x + Bootstrap 5
