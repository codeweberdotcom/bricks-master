# File Loading Order & Dependencies

Complete reference for how modules are loaded and their dependencies.

## Load Sequence (functions.php)

The theme initializes modules in dependency order. This document tracks the exact sequence and why each module loads when it does.

### Phase 1: Custom Post Type Definitions (Lines 8-14)

**Order:**
```php
1.  cpt-header.php
2.  cpt-footer.php
3.  cpt-page-header.php
4.  cpt-modals.php
5.  cpt-html_blocks.php
6.  cpt-clients.php
7.  cpt-notifications.php
```

**Why first?**
- CPTs must be registered before Redux reads them
- Other modules may depend on CPT existence
- Allows WordPress to recognize custom post types immediately

**Full CPT list** (all in `/functions/cpt/`):
- cpt-header.php → `Header` post type
- cpt-footer.php → `Footer` post type
- cpt-page-header.php → `PageHeader` post type
- cpt-modals.php → `Modal` post type
- cpt-html_blocks.php → `HTMLBlock` post type
- cpt-clients.php → `Clients` post type
- cpt-notifications.php → `Notifications` post type
- cpt-faq.php → `FAQ` post type
- cpt-staff.php → `Staff` post type
- cpt-testimonials.php → `Testimonials` post type
- cpt-vacancies.php → `Vacancies` post type
- cpt-offices.php → `Offices` post type
- cpt-services.php → `Services` post type
- cpt-projects.php → `Projects` post type
- cpt-documents.php → `Documents` post type
- cpt-price_list.php → `PriceList` post type
- cpt-price_package.php → `PricePackage` post type
- cpt-legal.php → `LegalDoc` post type

### Phase 2: Core Setup Functions (Lines 16-18)

**Order:**
```php
1.  setup.php              (Theme support, features)
2.  roles.php              (Custom user roles)
3.  gulp.php               (Gulp integration)
```

**Dependencies:**
- No external dependencies
- Must load early for theme setup hooks

**Purposes:**
- `setup.php`: Enables post thumbnails, custom logo, menus, etc.
- `roles.php`: Defines custom capabilities for user roles
- `gulp.php`: Integrates Gulp for child theme asset compilation

### Phase 3: Required Plugin Activation (Lines 20-21)

**Order:**
```php
1.  plugins/tgm/class-tgm-plugin-activation.php
2.  plugins/tgm/plugins_autoinstall.php
```

**Purposes:**
- TGM Plugin Activation: automatic required plugin installation
- Must load early so plugins can be detected/activated

### Phase 4: Core Classes & Registries (Lines 23-30)

**Order:**
```php
1.  class-codeweber-options.php       (Redux wrapper)
2.  enqueues.php                      (Script/style registration)
3.  images.php                        (Image size definitions)
4.  pdf-thumbnail-install.php         (PDF thumbnail setup)
5.  pdf-thumbnail.php                 (PDF thumbnail functions)
6.  pdf-thumbnail-js.php              (PDF thumbnail JavaScript)
7.  navmenus.php                      (Navigation menu registration)
8.  sidebars.php                      (Widget area registration)
9.  documentation.php                 (Admin documentation)
```

**Dependencies:**
- `class-codeweber-options.php` must load before anything accessing Redux
- `enqueues.php` can load anytime (WordPress will execute on wp_enqueue_scripts hook)
- `images.php` should load early for early image size access
- Others are self-contained utilities

**Why this order:**
- Classes needed by later modules load first
- Hooks for script/image registration happen here
- Widget/menu registration in WordPress must happen early

### Phase 5: Helper Libraries (Lines 32-35)

**Order:**
```php
1.  lib/class-wp-bootstrap-navwalker.php
2.  lib/class-codeweber-vertical-dropdown-walker.php
3.  lib/class-codeweber-menu-collapse-walker.php
4.  codeweber-nav.php
```

**Dependencies:**
- Nav walkers are independent classes
- `codeweber-nav.php` may use the walkers
- Must load after navmenus.php (which registers menus these walk)

**Purpose:**
- Define custom menu rendering classes for Bootstrap 5 integration

### Phase 6: Core Functions (Lines 36-39)

**Order:**
```php
1.  global.php             (Global helper functions)
2.  breadcrumbs.php        (Breadcrumb generation)
3.  cleanup.php            (Theme cleanup/optimization)
4.  custom.php             (Custom hooks/filters)
```

**Dependencies:**
- All are independent utility functions
- Can load in any order

**Purposes:**
- Global utilities for templates
- Breadcrumb navigation
- Cleanup non-essential WP output
- Define custom hooks for the theme

### Phase 7: Contact Form 7 Integration (Lines 41-43)

**Order:**
```php
IF class_exists('WPCF7'):
    integrations/cf7.php
```

**Dependencies:**
- Conditional: only loads if CF7 plugin is active
- Depends on: core functions already loaded

**Purpose:**
- CF7 form styling, custom validation, consent handling

### Phase 8: Admin Settings (Lines 45)

**Order:**
```php
1.  admin/admin_settings.php
```

**Dependencies:**
- Depends on: Codeweber_Options (phase 4)
- Depends on: Core functions (phase 6)

**Purpose:**
- WordPress admin page for theme settings

### Phase 9: AJAX Fetch System (Lines 46)

**Order:**
```php
1.  fetch/fetch-handler.php
```

**Dependencies:**
- Depends on: Core functions for security checks
- Registers wp_ajax_fetch_action hook

**Purpose:**
- AJAX endpoint dispatcher for frontend data requests

### Phase 10: WooCommerce Integration (Lines 48-50)

**Order:**
```php
IF class_exists('WooCommerce'):
    woocommerce.php
```

**Dependencies:**
- Conditional: only if WooCommerce plugin active
- Depends on: enqueues.php for script registration

**Purpose:**
- WooCommerce-specific styling and customization

### Phase 11: Data Standardization (Lines 52-61)

**Order:**
```php
1.  integrations/dadata/class-codeweber-dadata.php
2.  integrations/dadata/dadata-ajax.php
3.  user.php                (User functions)
4.  cyr-to-lat.php         (Cyrillic transliteration)
```

**Dependencies:**
- DaData: independent integration
- user.php: core user functions
- cyr-to-lat.php: transliteration utility

**Purposes:**
- DaData: address standardization API
- User: custom user functions
- Cyr-to-lat: transliterate Cyrillic to Latin

### Phase 12: Comments System (Lines 63-64)

**Order:**
```php
1.  lib/comments-helper.php
2.  comments-reply.php
```

**Dependencies:**
- Independent comment utilities

**Purpose:**
- Comment form handling and reply functionality

### Phase 13: Post Card Template System (Lines 65)

**Order:**
```php
1.  post-card-templates.php
```

**Dependencies:**
- Depends on: Codeweber_Options
- Used by: templates and shortcodes

**Purpose:**
- Flexible post card rendering with templates

### Phase 14: Personal Data & Privacy (Lines 69-101)

**Order:**
```php
1.  integrations/personal-data-v2/init.php
2.  [Personal_Data_V2 providers registered via hook]
    - integrations/personal-data-v2/providers/class-cf7-provider.php
    - integrations/personal-data-v2/providers/class-testimonials-provider.php
    - integrations/personal-data-v2/providers/class-consent-provider.php
3.  integrations/personal-data-v2/test-providers.php  (if WP_DEBUG)
```

**Dependencies:**
- Conditional on: personal_data_v2_ready hook
- If WP_DEBUG: loads test providers

**Purpose:**
- GDPR compliance: personal data export/deletion
- Data providers for CF7, testimonials, consents

### Phase 15: Newsletter Subscription (Lines 71-72)

**Order:**
```php
1.  integrations/newsletter-subscription/newsletter-init.php
```

**Dependencies:**
- Independent integration

**Purpose:**
- Newsletter subscription functionality

### Phase 16: Image Licensing (Lines 130)

**Order:**
```php
1.  integrations/image-licenses/image-licenses.php
```

**Dependencies:**
- Independent integration

**Purpose:**
- Track and display image licenses

### Phase 17: Search & Analytics (Lines 135-139)

**Order:**
```php
1.  integrations/ajax-search-module/ajax-search.php
2.  integrations/ajax-search-module/search-statistics.php
3.  integrations/ajax-search-module/matomo-search-integration.php
```

**Dependencies:**
- Independent integrations
- Matomo integration adds search event tracking

**Purposes:**
- AJAX search functionality
- Search statistics tracking
- Matomo analytics integration

### Phase 18: CodeWeber Forms (Lines 145)

**Order:**
```php
1.  integrations/codeweber-forms/codeweber-forms-init.php
```

**Dependencies:**
- Depends on: Codeweber_Options, AJAX system

**Purpose:**
- Custom form system

### Phase 19: Yandex Maps Integration (Lines 146)

**Order:**
```php
1.  integrations/yandex-maps/yandex-maps-init.php
```

**Dependencies:**
- Independent integration

**Purpose:**
- Yandex Maps integration

### Phase 20: AJAX Filter System (Lines 149)

**Order:**
```php
1.  ajax-filter.php
```

**Dependencies:**
- Depends on: AJAX system (phase 9)

**Purpose:**
- Universal AJAX filtering for posts

### Phase 21: Demo Data (Lines 152-162)

**Order:**
```php
1.  demo/demo-clients.php
2.  demo/demo-faq.php
3.  demo/demo-testimonials.php
4.  demo/demo-staff.php
5.  demo/demo-vacancies.php
6.  demo/demo-forms.php
7.  demo/demo-cf7-forms.php
8.  demo/demo-offices.php
9.  demo/demo-footer.php
10. demo/demo-header.php
11. demo/demo-ajax.php
```

**Purposes:**
- Generate sample data for theme demo
- Only loads if needed

### Phase 22: Modal Container & API (Lines 167-172)

**Order:**
```php
1.  integrations/modal-container.php
2.  integrations/modal-rest-api.php
```

**Dependencies:**
- Depends on: CPT system (Modal type)

**Purposes:**
- Modal rendering container
- REST API for modals

### Phase 23: Success Messages (Lines 177)

**Order:**
```php
1.  integrations/success-message-template.php
```

**Purpose:**
- Universal success message display

### Phase 24: Testimonial Forms (Lines 182)

**Order:**
```php
1.  testimonials/testimonial-form-api.php
```

**Dependencies:**
- Depends on: Testimonials CPT
- Depends on: Form system

**Purpose:**
- Form submission API for testimonials

### Phase 25: PHP Compatibility Fixes (Lines 188-194)

**Order:**
```php
1.  add_action('current_screen', ...) → Fix strip_tags(null) issue
```

**Purpose:**
- Compatibility fix for newer PHP versions

## Dependency Graph

```
PHASE 1: CPT System
    └── All CPTs registered

PHASE 2: Core Setup
    ├── setup.php
    ├── roles.php
    └── gulp.php

PHASE 3: Plugin Activation
    └── TGM Plugin Activation

PHASE 4: Core Classes & Registries
    ├── class-codeweber-options.php
    ├── enqueues.php
    ├── images.php
    ├── pdf-thumbnail-*
    ├── navmenus.php
    └── sidebars.php
         ↓ (Depends on Phase 1)

PHASE 5: Helper Libraries
    ├── class-wp-bootstrap-navwalker.php
    ├── class-codeweber-vertical-dropdown-walker.php
    ├── class-codeweber-menu-collapse-walker.php
    └── codeweber-nav.php
         ↓ (Uses Phase 4)

PHASE 6: Core Functions
    ├── global.php
    ├── breadcrumbs.php
    ├── cleanup.php
    └── custom.php
         ↓ (Used by all later phases)

PHASE 7: Integration - CF7
    └── integrations/cf7.php (conditional)
         ↓ (Depends on Phase 4, 6)

PHASE 8: Admin Settings
    └── admin/admin_settings.php
         ↓ (Depends on Phase 4, 6)

PHASE 9: AJAX System
    └── fetch/fetch-handler.php
         ↓ (Depends on Phase 6)

PHASE 10: Integration - WooCommerce
    └── woocommerce.php (conditional)
         ↓ (Depends on Phase 4)

PHASE 11: Data Systems
    ├── integrations/dadata/*
    ├── user.php
    └── cyr-to-lat.php
         ↓ (Depends on Phase 6)

PHASE 12: Comments
    ├── lib/comments-helper.php
    └── comments-reply.php

PHASE 13: Post Cards
    └── post-card-templates.php
         ↓ (Depends on Phase 4, 6, 12)

PHASE 14: Privacy/GDPR
    ├── integrations/personal-data-v2/init.php
    ├── providers/* (loaded via hook)
    └── test-providers.php (if WP_DEBUG)

PHASE 15: Newsletter
    └── integrations/newsletter-subscription/newsletter-init.php

PHASE 16: Image Licenses
    └── integrations/image-licenses/image-licenses.php

PHASE 17: Search & Analytics
    ├── integrations/ajax-search-module/ajax-search.php
    ├── integrations/ajax-search-module/search-statistics.php
    └── integrations/ajax-search-module/matomo-search-integration.php

PHASE 18: CodeWeber Forms
    └── integrations/codeweber-forms/codeweber-forms-init.php
         ↓ (Depends on Phase 4, 9)

PHASE 19: Yandex Maps
    └── integrations/yandex-maps/yandex-maps-init.php

PHASE 20: AJAX Filters
    └── ajax-filter.php
         ↓ (Depends on Phase 9)

PHASE 21: Demo Data
    └── demo/*.php

PHASE 22: Modals
    ├── integrations/modal-container.php
    └── integrations/modal-rest-api.php
         ↓ (Depends on Phase 1)

PHASE 23: Success Messages
    └── integrations/success-message-template.php

PHASE 24: Testimonial Forms
    └── testimonials/testimonial-form-api.php
         ↓ (Depends on Phase 1, 18)

PHASE 25: Compatibility
    └── Current screen action hook
```

## When Adding New Files

**Steps to integrate a new module:**

1. **Identify dependencies**: What modules does yours depend on?
2. **Find correct phase**: Insert after all dependencies are loaded
3. **Use `require_once`**: Always use `require_once`, not `require` or `include`
4. **Add documentation**: Update this file with new phase and dependencies
5. **Test initialization**: Verify no undefined function/class errors

**Example: Adding a "reviews" integration**

Reviews depends on:
- Core functions (phase 6) — for helper functions
- AJAX system (phase 9) — for submission handling
- Redux options (phase 4) — for settings

Insert after phase 9, before phase 11:

```php
// Phase 10a: Reviews Integration
require_once get_template_directory() . '/functions/integrations/reviews.php';
```

## Redux Initialization Timing

Redux is not loaded in the main functions.php sequence. Instead, it's initialized on the `after_setup_theme` hook (line 124) with priority 30:

```php
add_action('after_setup_theme', 'codeweber_initialize_redux', 30);

function codeweber_initialize_redux() {
    require_once get_template_directory() . '/redux-framework/redux-core/framework.php';
    $opt_name = 'redux_demo';
    require_once get_template_directory() . '/redux-framework/sample/theme-config.php';
    require_once get_template_directory() . '/functions/cpt/redux_cpt.php';
    require_once get_template_directory() . '/redux-framework/theme-settings/theme-settings.php';
}
```

**Why hook instead of direct require?**
- WordPress theme support must be set first (via setup.php)
- CPTs must be registered before Redux reads them
- Redux needs proper WordPress state to initialize

**Hook priority 30** means it runs after default priority (10) but before most custom code.

## Critical Files to Never Remove

| File | Why |
|------|-----|
| functions.php | Entry point |
| functions/cpt/*.php | Post type definitions |
| functions/class-codeweber-options.php | Redux wrapper |
| functions/enqueues.php | Asset management |
| redux-framework/sample/theme-config.php | Redux configuration |
| functions/fetch/fetch-handler.php | AJAX dispatcher |

Removing these will break fundamental functionality.

## Testing Load Order

To verify load order during development:

```php
// In functions.php, add temporary debugging:
add_action('wp_footer', function() {
    if (current_user_can('manage_options')) {
        echo '<!-- Theme load order verified -->';
    }
});
```

Or check WordPress debug log:
```php
// In any loaded file:
error_log('Module loaded: ' . __FILE__);
```

---

**Next Steps**:
- See **THEME_OVERVIEW.md** for architecture details
- See **CHILD_THEME_GUIDE.md** for child theme setup
- Check specific module documentation for integration details
