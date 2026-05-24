# CodeWeber Forms System

Complete guide to the native forms system for CodeWeber theme - a modern alternative to Contact Form 7.

---

## Overview

CodeWeber includes a built-in **form system** for handling contact forms, surveys, newsletter signups, testimonials, and more. The system features:

**Key capabilities:**
- Native form creation via Gutenberg blocks
- 12+ field types (text, email, textarea, select, radio, checkbox, file, date, time, number, hidden, etc.)
- All submissions saved to database
- SMTP email delivery integration
- Rate limiting and honeypot anti-spam
- Admin submissions management interface
- Email templates with customization
- REST API for programmatic access
- JavaScript and PHP hooks for customization
- Gutenberg block builder for form creation

**Architecture:**
- Form CPT (`codeweber_form`) for storing form definitions
- Database table (`wp_codeweber_forms_submissions`) for submissions
- Renderer class for frontend output
- Mailer class for email sending
- Validator class for input validation

---

## Form Lifecycle

### Step 1: Form Creation (Admin)

Forms created in WordPress admin:

1. **Navigate:** Forms → Add New Form
2. **Design form** using Gutenberg:
   - Add "Form" block from Codeweber category
   - Add "Form Field" blocks inside
   - Configure each field (type, label, validation, etc.)
   - Configure form settings (email, success message, etc.)
3. **Publish form** → Form now has Post ID (e.g., 123)

### Step 2: Form Display (Frontend)

Form rendered on frontend via:

```php
// Shortcode
[codeweber_form id="123"]

// Gutenberg block
// Add "Form" block, set Form ID = 123

// PHP
echo do_shortcode('[codeweber_form id="123"]');

// REST
GET /wp-json/codeweber-forms/v1/forms/123
```

Flow:
1. `CodeweberFormsRenderer::render()` loads form post
2. Extracts form block structure from post content
3. Generates HTML with fields
4. Enqueues JavaScript handler
5. Fires `codeweber_form_opened` hook

### Step 3: User Interaction

User fills and submits form:

1. **Form validation** (JavaScript):
   - Required field check
   - Email format validation
   - Custom validators
   - Fires `codeweberFormInvalid` event on error

2. **AJAX submission** (JavaScript `form-submit-universal.js`):
   - Collects form data
   - Fires `codeweberFormSubmitting` event (can cancel)
   - POST to `/wp-json/codeweber-forms/v1/submit`

### Step 4: Backend Processing

Server-side form handling:

```php
// REST endpoint: POST /wp-json/codeweber-forms/v1/submit

1. Verify nonce (X-WP-Nonce header)
2. Check honeypot field
3. Verify one-time token (cwf_token) — see Security section
4. Check rate limiting
5. Validate & sanitize input
6. Fire codeweber_form_before_send hook
7. Save submission to database
8. Fire codeweber_form_saved hook
9. Send email notification
10. Fire codeweber_form_after_send hook
11. Return success response
```

### Step 5: Response & Confirmation

Frontend receives response:

```javascript
// Success
{
    "success": true,
    "message": "Thank you! We'll be in touch.",
    "submissionId": "abc123",
    "redirectUrl": "https://..."  // Optional
}

// Fire codeweberFormSubmitted event
document.dispatchEvent(
    new CustomEvent('codeweberFormSubmitted', {
        detail: {
            formId: 123,
            submissionId: 'abc123',
            message: '...'
        }
    })
);
```

### Step 5а: Success в модале

Если форма была открыта в модальном окне, при успешной отправке вызывается:

```js
// form-submit-universal.js → replaceModalContentWithEnvelope(form, message)
// внутри:
window.codeweberModal.showSuccess(message || '');
```

Это запускает единый success-поток модальной системы:

1. Skeleton-loader заменяет форму мгновенно
2. Загружается шаблон из `codeweber/v1/success-message-template`
3. Модал автоматически закрывается через 3 секунды

Этот же путь используется во **всех** сценариях завершения:

- Успешная отправка обычной формы
- Успешная отправка newsletter-формы
- Ответ `already_subscribed` для newsletter-форм (пользователь уже подписан)

> **Важно:** `form-submit-universal.js` **не создаёт модальные окна самостоятельно** — никакого `#newsletter-success-modal`, `#codeweber-form-success-modal` и т.д. Вся логика открытия/показа делегируется в `replaceModalContentWithEnvelope` → `window.codeweberModal.showSuccess()`.

Подробнее: [MODAL_SYSTEM.md](../api/MODAL_SYSTEM.md#10-успешная-отправка-формы)

### Step 6: Admin Review

Admins review submissions:

- **Form Submissions → All Submissions** - view all
- Filter by form, status, date
- View individual submission details
- Mark as read/archived/deleted
- Download attachments
- Export to CSV

---

## Creating Forms

### Method 1: Gutenberg Blocks (Recommended)

**Step-by-step:**

1. Go to **Forms → Add New Form**
2. Click **"+"** to add block
3. Search for and add **"Form"** block (Codeweber Gutenberg Blocks category)
4. Configure form properties in sidebar:
   - **Form ID**: Unique identifier (auto-generated)
   - **Form Type**: form | newsletter | testimonial | callback | resume | faq | event-registration | questionnaire | brief
   - **Email Settings:**
     - Email to (recipient)
     - Email from (sender)
     - Subject line
   - **Messages:**
     - Success message
     - Error message
   - **Security:**
     - Enable honeypot
     - Enable rate limiting
     - Spam check

5. Add form fields by clicking **"+"** inside form block
6. For each field, add **"Form Field"** block
7. Configure field properties:
   - **Field Type**: text | email | textarea | select | radio | checkbox | file | date | time | number | hidden
   - **Label**: Display name
   - **Placeholder**: Helper text
   - **Required**: true/false
   - **Validation**: email | url | tel | number | custom regex
   - **Width**: Bootstrap grid (col-12, col-md-6, etc.)
   - **Field Name**: Identifier (auto-generated from label)

8. **Publish** form

### Form Types

All available form type values, admin labels, and badge colours:

| Value | Admin Label | Badge Colour |
|-------|-------------|--------------|
| `form` | Form | `#607d8b` (blue-grey) |
| `newsletter` | Newsletter | `#00897b` (teal) |
| `testimonial` | Testimonial | `#8e24aa` (purple) |
| `callback` | Callback | `#e53935` (red) |
| `resume` | Resume | `#1e88e5` (blue) |
| `faq` | FAQ | `#f4511e` (orange) |
| `event-registration` | Event | `#43a047` (green) |
| `questionnaire` | Questionnaire | `#00897b` (teal) |
| `brief` | Brief | `#6a1b9a` (deep purple) |

Type is set in the **Form** block Inspector sidebar → "Form Type" select. The value is stored in the `formType` block attribute and rendered in:

- Admin list column **"Type"** (`codeweber-forms-cpt.php` → `cwf_form_type_column`)
- Admin filter dropdown above the list (`restrict_manage_posts`)
- Email notification metadata

### Method 2: Shortcode (Simple)

For quick testing or simple forms:

```php
[codeweber_form id="123"]

[codeweber_form id="123" title="Contact Us"]

[codeweber_form id="123" name="contact-form" title="Get in Touch"]
```

**Attributes:**
- `id` (required) - Form post ID
- `title` (optional) - Display above form
- `name` (optional) - Internal identifier for analytics

### Method 3: PHP (Developer)

```php
// Method 1: Shortcode
echo do_shortcode('[codeweber_form id="123"]');

// Method 2: Direct class usage
if (class_exists('CodeweberFormsRenderer')) {
    $renderer = new CodeweberFormsRenderer();
    $form_post = get_post(123);
    echo $renderer->render(123, $form_post);
}

// Method 3: Render from config array
$form_config = [
    'id' => 'inline-form',
    'type' => 'form',
    'fields' => [
        [
            'type' => 'text',
            'name' => 'name',
            'label' => 'Your Name',
            'required' => true,
        ],
        [
            'type' => 'email',
            'name' => 'email',
            'label' => 'Email Address',
            'required' => true,
        ],
    ],
    'settings' => [
        'successMessage' => 'Thanks for contacting us!',
    ]
];

if (class_exists('CodeweberFormsRenderer')) {
    $renderer = new CodeweberFormsRenderer();
    echo $renderer->render('inline-form', $form_config);
}
```

---

## Field Types

### Text Input Fields

```php
// Text
[
    'type' => 'text',
    'name' => 'first_name',
    'label' => 'First Name',
    'placeholder' => 'John',
    'required' => true,
    'validation' => 'text',  // No special validation
]

// Email
[
    'type' => 'email',
    'name' => 'email',
    'label' => 'Email Address',
    'validation' => 'email',  // Auto-validated
]

// Phone/Tel — с маской темы
[
    'type' => 'tel',
    'name' => 'phone',
    'label' => 'Phone Number',
    'validation' => 'tel',  // Phone format
    'useThemeMask' => true,  // использовать opt_phone_mask из Redux
]

// Phone/Tel — с кастомной маской
[
    'type' => 'tel',
    'name' => 'phone',
    'label' => 'Phone Number',
    'phoneMask' => '+7 (___) ___-__-__',  // символ _ — позиция цифры
    'phoneMaskCaret' => '_',              // символ курсора (по умолчанию _)
    'phoneMaskSoftCaret' => '_',          // символ-заглушка (по умолчанию _)
]

// URL
[
    'type' => 'url',
    'name' => 'website',
    'label' => 'Website',
    'validation' => 'url',
]

// Number
[
    'type' => 'number',
    'name' => 'quantity',
    'label' => 'Quantity',
    'min' => 1,
    'max' => 100,
]
```

### Phone Mask

Поле типа `tel` поддерживает два режима маски:

**1. Маска темы (`useThemeMask: true`)**

Берёт маску из Redux → Внешний вид → Phone mask (`opt_phone_mask`). При изменении настройки в Redux все формы с этим флагом автоматически подхватывают новую маску — без пересохранения блоков.

```php
[
    'type' => 'tel',
    'name' => 'phone',
    'label' => 'Phone Number',
    'useThemeMask' => true,
]
```

В Gutenberg-редакторе: Inspector → Phone mask → тоггл **"Use theme mask"**.

**2. Кастомная маска (атрибуты `phoneMask`)**

```php
[
    'type' => 'tel',
    'phoneMask' => '+7 (___) ___-__-__',  // _ — позиция для цифры
    'phoneMaskCaret' => '_',              // символ курсора (опционально)
    'phoneMaskSoftCaret' => '_',          // заглушка в маске (опционально)
]
```

**Приоритет:**

- `useThemeMask=true` → всегда берёт `opt_phone_mask` из Redux, `phoneMask` игнорируется
- `useThemeMask=false` + `phoneMask` задан → используется кастомная маска
- `useThemeMask=false` + `phoneMask` пустой → фоллбэк на `opt_phone_mask` (поведение по умолчанию для PHP-renderer)

**Где применяется:**

- `save.js` (статический HTML Gutenberg) — при `useThemeMask=true` записывает `data-mask-use-theme="true"`, тема JS (`addTelMask()`) подставляет значение из `window.codeweberTheme.phoneMask`
- `codeweber-forms-renderer.php` (shortcode / PHP-рендер) — напрямую читает `Codeweber_Options::get('opt_phone_mask')`
- `render.php` в плагине (inline button путь) — аналогично PHP

---

### Text Area

```php
[
    'type' => 'textarea',
    'name' => 'message',
    'label' => 'Message',
    'placeholder' => 'Your message here...',
    'rows' => 5,  // Number of visible rows
    'required' => true,
]
```

### Select/Option Fields

```php
// Select dropdown
[
    'type' => 'select',
    'name' => 'subject',
    'label' => 'Subject',
    'required' => true,
    'options' => [
        'support' => 'Support',
        'sales' => 'Sales',
        'feedback' => 'Feedback',
        'other' => 'Other',
    ],
]

// Radio buttons
[
    'type' => 'radio',
    'name' => 'contact_method',
    'label' => 'Preferred Contact Method',
    'options' => [
        'email' => 'Email',
        'phone' => 'Phone',
        'sms' => 'SMS',
    ],
]

// Checkboxes
[
    'type' => 'checkbox',
    'name' => 'interests',
    'label' => 'I am interested in:',
    'options' => [
        'news' => 'Newsletter',
        'events' => 'Events',
        'offers' => 'Special Offers',
    ],
    'required' => false,  // Can be optional
]
```

### Date & Time

```php
// Date picker
[
    'type' => 'date',
    'name' => 'appointment_date',
    'label' => 'Preferred Date',
]

// Time picker
[
    'type' => 'time',
    'name' => 'appointment_time',
    'label' => 'Preferred Time',
]
```

### File Upload

```php
[
    'type' => 'file',
    'name' => 'resume',
    'label' => 'Attach Resume',
    'accept' => '.pdf,.doc,.docx',  // Allowed file types
    'multiple' => false,  // Single or multiple files
    'maxSize' => 5242880,  // Max file size (5MB in bytes)
]
```

### Hidden Fields

```php
[
    'type' => 'hidden',
    'name' => 'source',
    'value' => 'contact-page',
    'label' => 'Source',  // Optional for hidden
]
```

### Consent Checkbox

```php
[
    'type' => 'consent',
    'name' => 'gdpr_consent',
    'label' => 'I agree to the privacy policy',
    'required' => true,
    'consentType' => 'gdpr',  // Types: gdpr, marketing, terms
]
```

---

## Form Settings

### Email Configuration

```php
[
    'settings' => [
        // Recipients
        'emailTo' => 'admin@example.com',      // Or list: 'admin@ex.com,support@ex.com'
        'emailFromName' => 'Contact Form',     // Sender name
        'emailFromAddress' => 'noreply@ex.com', // Sender email
        'emailSubject' => 'New Contact Form Submission',

        // Admin notification
        'adminEmailTemplate' => 'default',  // Template name

        // User confirmation
        'sendUserConfirmation' => true,
        'userConfirmationTemplate' => 'user-confirmation',

        // Messages
        'successMessage' => 'Thank you! We\'ll be in touch soon.',
        'errorMessage' => 'There was an error. Please try again.',
    ]
]
```

### Security Settings

```php
[
    'settings' => [
        // Honeypot (hidden field to catch bots)
        'honeypot' => true,
        'honeypotField' => 'website',  // Field name

        // One-time token (see Security section below)
        'token_enabled' => true,

        // Rate limiting
        'rateLimit' => true,
        'rateLimitPerMinute' => 5,     // Submissions per minute per IP
        'rateLimitPerHour' => 50,      // Submissions per hour per IP

        // Nonce verification (automatic)
        'verifyNonce' => true,
    ]
]
```

### Behavior Settings

```php
[
    'settings' => [
        // Redirect after success
        'redirectOnSuccess' => false,
        'redirectUrl' => 'https://example.com/thank-you',

        // Auto-reply
        'autoReply' => true,
        'autoReplyTemplate' => 'confirmation',

        // Save submissions
        'saveSubmissions' => true,
        'submissionStatus' => 'new',  // Initial status
    ]
]
```

---

## Validation

### Built-in Validators

Field validation happens both frontend (JS) and backend (PHP):

```php
'validation' => 'email'      // Email format
'validation' => 'url'        // URL format
'validation' => 'tel'        // Phone format
'validation' => 'number'     // Numeric
'validation' => 'regex:/^\d{3}-\d{4}$/'  // Custom regex

// Required field
'required' => true

// Min/Max length
'minLength' => 3
'maxLength' => 100

// Min/Max value (for numbers)
'min' => 0
'max' => 100

// File size
'maxFileSize' => 5242880  // 5MB in bytes
```

### Custom Validation

Via PHP hook:

```php
add_action('codeweber_form_before_send', function($form_id, $form_data, $fields) {
    // Check custom field
    if (empty($fields['custom_field'])) {
        throw new Exception('Custom field is required!');
    }

    // Validate custom logic
    if ($fields['phone'] && !preg_match('/^\d{10}$/', $fields['phone'])) {
        throw new Exception('Phone must be 10 digits');
    }
}, 10, 3);
```

Via JavaScript hook:

```javascript
document.addEventListener('codeweberFormSubmitting', function(event) {
    const formData = event.detail.formData;

    // Custom validation
    if (formData.age < 18) {
        alert('You must be 18 or older');
        event.preventDefault();
    }
});
```

---

## Multipage Forms

### Overview

A multipage form splits a long form into sequential pages (steps). Each page is a separate **Form Page** block nested inside the **Form** block. Navigation is handled by JavaScript (`form-multipage.js`), which shows one step at a time, validates each step before advancing, and persists progress in `localStorage`.

**Architecture:**

```
Form block (cwgb-form-multipage data-total-steps="N")
├── Form Page block (cwgb-form-step data-step="1")
│   ├── Form Field blocks…
│   └── Navigation: Next button (.cwgb-form-next)
├── Form Page block (cwgb-form-step data-step="2")
│   ├── Form Field blocks…
│   └── Navigation: Back + Next buttons
└── Form Page block (cwgb-form-step data-step="N")
    ├── Form Field blocks…
    └── Navigation: Back + Submit button
```

The PHP renderer (`codeweber-forms-renderer.php`) generates all pages in one pass. Only the active step is `display:block`; others are `display:none`. This means the entire form is submitted as one `<form>` element — **no AJAX between steps**.

### Form Page Block Attributes (`block.json`)

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `pageTitle` | string | `''` | Step title shown in progress indicator |
| `nextButtonText` | string | `'Next'` | Label for the Next button |
| `backButtonText` | string | `'Back'` | Label for the Back button |
| `nextButtonClass` | string | `'btn btn-primary'` | CSS classes for Next button |
| `backButtonClass` | string | `'btn btn-outline-secondary'` | CSS classes for Back button |
| `pageConditionalLogic` | boolean | `false` | Enable page-level conditional logic |
| `pageConditionalAction` | string | `'show'` | `show` or `skip` |
| `pageConditionalMatch` | string | `'all'` | `all` (AND) or `any` (OR) |
| `pageConditionalRules` | array | `[]` | Array of rule objects (see Page Conditional Logic) |

### Progress Indicator

The renderer outputs a progress indicator block when `totalSteps > 1`:

```html
<div class="cwgb-form-progress" aria-label="Step 1 of 3">
    <div class="progress mb-2">
        <div class="progress-bar" role="progressbar"
             style="width: 33%"
             aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>
    <div class="cwgb-form-progress-text mb-2">
        <div class="text-muted">
            <span class="cwgb-form-progress-current">1</span> of
            <span class="cwgb-form-progress-total">3</span>
        </div>
        <h5 class="mb-0 text-primary">
            <span class="cwgb-form-progress-title">Step Title</span>
        </h5>
    </div>
</div>
```

**JS-updatable elements:**

| Selector | What JS writes |
|----------|----------------|
| `.progress-bar` | `style.width` + `aria-valuenow` |
| `.cwgb-form-progress-current` | Current visible step number |
| `.cwgb-form-progress-total` | Total visible step count |
| `.cwgb-form-progress-title` | `pageTitle` of current step |
| `.cwgb-form-progress` | `aria-label` = "Step N of M" |

The step title wrapper (`<h5>`) is hidden via `display:none` when the current page has no title.

Page titles are embedded as JSON in a hidden element:

```html
<script type="application/json" class="cwgb-form-page-titles">
    ["Step 1 Title","","Step 3 Title"]
</script>
```

### localStorage Persistence

`form-multipage.js` saves form state to `localStorage` after every field change and step navigation. State is restored on page reload.

**Storage key:** `cwf_mp_{formId}`

**Stored payload:**
```json
{
    "step": 2,
    "fields": { "name": "John", "subject": "sales" },
    "expires": 1716921600000
}
```

**TTL:** 24 hours from last save. Expired data is discarded on load.

**Excluded from storage:**
- `form_nonce`, `_wpnonce`, `cwf_token`, `form_id`, `form_honeypot` — security tokens
- `type="file"` — file inputs cannot be serialised

**Restore logic** (`restoreFieldValues`):
1. Radio buttons — matched by `input[type="radio"][name="…"]`, sets `checked` on matching value
2. Checkboxes — matched by `input[type="checkbox"][name="…"]`, array comparison
3. Everything else — `el.value = val`, skipping `type="file"`

> Using explicit type selectors (not `querySelector('[name="…"]')`) prevents a radio being detected as a generic input when `radio.type` is not checked.

### Navigation Flow

```
Next click
  → validateStep(activeStep)   // HTML5 + phone-mask custom validity
  → findNextStep(+1)           // skips pages with shouldSkipPage() === true
  → saveState()
  → goToStep(targetStep)

Back click
  → findNextStep(-1)           // same skip logic in reverse
  → saveState()
  → goToStep(targetStep)

Field change
  → saveState()
  → getVisibleSteps()          // recalculate for progress bar
  → updateProgress()
```

### Per-step Validation

Before advancing, `validateStep(stepEl)` runs:

1. Phone mask fields (`input[type="tel"][data-mask]`) — sets `setCustomValidity` based on whether the value has real digits and no `_` placeholders
2. All fields — `el.reportValidity()` on first invalid element, returns `false`

Back navigation never validates (users can go back freely).

### Reset After Successful Submit

On `codeweberFormSubmitted` event (dispatched by `form-submit-universal.js`):

```javascript
document.addEventListener('codeweberFormSubmitted', function (e) {
    if (String(e.detail.formId) === String(formId)) {
        clearState(formId);   // remove localStorage entry
        currentStep = 1;
        form.reset();         // clear all field values
        goToStep(form, 1, totalSteps, pageTitles);
    }
});
```

This ensures users who submit a form inside a modal see a clean first step if they open the modal again.

### Dynamic Init (Modal Support)

Forms added to the DOM after initial load (e.g. loaded into a modal) are initialised on the `codeweberFormOpened` event:

```javascript
document.addEventListener('codeweberFormOpened', function () {
    document.querySelectorAll('.cwgb-form-multipage').forEach(function (form) {
        if (!form.dataset.cwgbMpInit) {
            form.dataset.cwgbMpInit = '1';
            initForm(form);
        }
    });
});
```

The `data-cwgb-mp-init` flag prevents double-initialisation if the event fires multiple times.

---

## Field Conditional Logic

Field-level conditional logic shows or hides individual **Form Field** blocks based on values of other fields within the same form page.

### Data Attributes

The PHP renderer writes these attributes on the field wrapper `.cwgb-form-field-wrapper`:

| Attribute | Values | Description |
|-----------|--------|-------------|
| `data-cond-action` | `show` \| `hide` | Show the field when rules match; or hide it |
| `data-cond-match` | `all` \| `any` | AND logic (all rules must match) or OR logic |
| `data-cond-rules` | JSON string | Array of rule objects |

**Rule object structure:**
```json
{ "field": "subject", "operator": "is", "value": "support" }
```

### Operators

| Operator | Matches when |
|----------|-------------|
| `is` | field value equals `value` (case-insensitive) |
| `is_not` | field value does not equal `value` |
| `contains` | field value includes `value` as substring |
| `not_contains` | field value does not include `value` |
| `is_empty` | field value is empty / unchecked |
| `is_not_empty` | field value is not empty |

### JavaScript Engine (`form-conditional.js`)

- Listens to `change` and `input` events on the form
- On each event, re-evaluates all `[data-cond-action]` wrappers
- Sets `display:none` / `display:''` and `disabled` on fields inside hidden wrappers (so disabled fields are excluded from submission)

### Gutenberg Editor

In the **Form Field** block Inspector sidebar → **Conditional Logic** panel:

1. Toggle "Enable Conditional Logic"
2. Choose action: **Show** or **Hide** this field
3. Choose match: **All** / **Any**
4. Add rules:
   - Select field (dropdown lists all `fieldName` values in the same form)
   - Select operator
   - Enter value (or leave empty for `is_empty`/`is_not_empty`)

---

## Page Conditional Logic

Page-level conditional logic shows or skips entire **Form Page** blocks based on values of any fields filled in previous steps.

### Data Attributes

Written on the `.cwgb-form-step` div by the PHP renderer (`codeweber-forms-renderer.php`):

| Attribute | Values | Description |
|-----------|--------|-------------|
| `data-page-cond-action` | `show` \| `skip` | `show`: display page only when rules match; `skip`: skip page when rules match |
| `data-page-cond-match` | `all` \| `any` | AND / OR logic |
| `data-page-cond-rules` | JSON string | Same rule object format as field conditional logic |

Only written when `pageConditionalLogic === true` and `pageConditionalRules` is non-empty.

### Logic in `form-multipage.js`

**`shouldSkipPage(form, stepEl)`** — returns `true` if the page should be hidden:

```javascript
// action = 'show': page is shown only when condition is met
//   → skip if condition is NOT met
// action = 'skip': page is skipped when condition is met
//   → skip if condition IS met

return action === 'show' ? !conditionMet : conditionMet;
```

**`getVisibleSteps(form, totalSteps)`** — returns array of step numbers that are NOT skipped:

```javascript
// e.g. [1, 3, 4] if step 2 is skipped
var visible = [];
for (var i = 1; i <= totalSteps; i++) {
    var stepEl = form.querySelector('.cwgb-form-step[data-step="' + i + '"]');
    if (stepEl && !shouldSkipPage(form, stepEl)) visible.push(i);
}
return visible.length > 0 ? visible : [1];
```

**`findNextStep(form, from, direction, totalSteps)`** — finds next non-skipped step:

```javascript
// direction: +1 (Next) or -1 (Back)
var step = from + direction;
while (step >= 1 && step <= totalSteps) {
    var stepEl = form.querySelector('.cwgb-form-step[data-step="' + step + '"]');
    if (stepEl && !shouldSkipPage(form, stepEl)) return step;
    step += direction;
}
return from; // no valid step found — stay on current
```

### Progress Bar Recalculation

`getVisibleSteps()` is called on every field `change` event and every navigation. `updateProgress()` receives the result:

```javascript
function updateProgress(form, currentStep, totalSteps, pageTitles, visibleSteps) {
    var dispCurrent = visibleSteps.indexOf(currentStep) + 1; // position in visible list
    var dispTotal   = visibleSteps.length;                   // count of visible steps
    // updates .cwgb-form-progress-current, .cwgb-form-progress-total, progress-bar width
}
```

This means the progress bar always reflects only the pages the user will actually see, and updates live as the user fills in the fields that control visibility.

### Supported Operators

Same as field-level conditional logic (see table above): `is`, `is_not`, `contains`, `not_contains`, `is_empty`, `is_not_empty`.

Multi-value fields (checkbox groups, radio buttons) are handled in `getPageFieldValues()`:
- **Checkbox** — returns array of all checked values
- **Radio** — returns array with single checked value, or empty array
- **Text/select/textarea** — returns array with single string value

### Gutenberg Editor

In the **Form Page** block Inspector sidebar → **Conditional Logic** panel:

1. Toggle "Enable Conditional Logic"
2. Choose action: **Show** (show this page only when rules match) or **Skip** (skip this page when rules match)
3. Choose match: **All** / **Any**
4. Add rules:
   - Select field — dropdown lists all `form-field` blocks from **all pages** in the parent form (via `useSelect` traversal)
   - Select operator
   - For select / radio / checkbox fields: value is a dropdown of the field's own options
   - For other field types: free-text value input

> **Important:** Rules can reference fields from any page, including later pages. Evaluate rules only after the user has visited the relevant page, otherwise the condition may be evaluated against an empty value.

---

## Hooks & Events

### PHP Actions (Server)

#### `codeweber_form_before_send`

Fires **before** form submission is processed.

**Use case:** Pre-submission validation, logging

```php
add_action('codeweber_form_before_send', function($form_id, $form_data, $fields) {
    // $form_id: int - Form post ID (123)
    // $form_data: array - Form configuration
    // $fields: array - Submitted field values ['name' => 'John', 'email' => '...']

    // Validate custom field
    if (empty($fields['custom_field'])) {
        throw new Exception('Custom field is required');
    }

    // Log submission
    error_log("Form $form_id submitted by " . $fields['email']);
}, 10, 3);
```

#### `codeweber_form_after_send`

Fires **after** email is sent successfully.

**Use case:** Integration with third-party services, CRM

```php
add_action('codeweber_form_after_send', function($form_id, $form_data, $submission_id) {
    // $form_id: int - Form post ID
    // $form_data: array - Form config
    // $submission_id: string - Unique submission ID

    // Send to external API
    wp_remote_post('https://api.example.com/leads', [
        'body' => json_encode([
            'form_id' => $form_id,
            'submission_id' => $submission_id,
        ])
    ]);
}, 10, 3);
```

#### `codeweber_form_saved`

Fires **when** submission is saved to database.

**Use case:** Notifications, webhooks

```php
add_action('codeweber_form_saved', function($submission_id, $form_id, $form_data) {
    // Send to Slack
    wp_remote_post('https://hooks.slack.com/...', [
        'body' => json_encode([
            'text' => "New form submission: $submission_id"
        ])
    ]);
}, 10, 3);
```

#### `codeweber_form_send_error`

Fires when **error occurs**.

**Use case:** Error logging, alerting

```php
add_action('codeweber_form_send_error', function($form_id, $form_data, $error) {
    // Log error
    error_log("Form $form_id error: " . $error);

    // Send admin alert
    wp_mail(get_option('admin_email'), 'Form Error', $error);
}, 10, 3);
```

#### `codeweber_form_opened`

Fires when **form is rendered** on frontend.

**Use case:** Analytics tracking, form impressions

```php
add_action('codeweber_form_opened', function($form_id) {
    // Log to analytics
    error_log("Form $form_id viewed");
}, 10, 1);
```

### JavaScript Events (Browser)

#### `codeweberFormOpened`

Fires when form loaded on page.

```javascript
document.addEventListener('codeweberFormOpened', function(event) {
    const { formId, form } = event.detail;

    // Track in Google Analytics
    if (window.gtag) {
        gtag('event', 'form_view', { form_id: formId });
    }
});
```

#### `codeweberFormSubmitting`

Fires **before** submission. Can cancel with `preventDefault()`.

```javascript
document.addEventListener('codeweberFormSubmitting', function(event) {
    const { formId, form, formData } = event.detail;

    // Custom validation
    if (formData.age < 18) {
        alert('Must be 18+');
        event.preventDefault();
    }
});
```

#### `codeweberFormInvalid`

Fires when **validation fails**.

```javascript
document.addEventListener('codeweberFormInvalid', function(event) {
    const { formId, message } = event.detail;
    console.error(`Form ${formId} validation error: ${message}`);
});
```

#### `codeweberFormSubmitted`

Fires on **successful** submission.

```javascript
document.addEventListener('codeweberFormSubmitted', function(event) {
    const { formId, submissionId, message } = event.detail;

    // Close modal
    const modal = form.closest('.modal');
    if (modal && window.bootstrap) {
        bootstrap.Modal.getInstance(modal)?.hide();
    }

    // Redirect
    window.location.href = '/thank-you';
});
```

#### `codeweberFormError`

Fires on **submission error**.

```javascript
document.addEventListener('codeweberFormError', function(event) {
    const { formId, message } = event.detail;
    console.error(`Form ${formId} error: ${message}`);
});
```

---

## Email Templates

### Default Templates

System includes pre-built templates:

- `default` - Standard notification email
- `user-confirmation` - Auto-reply to user
- `newsletter` - Newsletter signup confirmation
- `custom` - Custom template (editable)

### Email Template Variables

Variables available in templates:

```php
{form_title}           // Form name
{form_id}              // Form post ID
{submission_id}        // Unique submission ID
{submission_date}      // Date/time of submission
{sender_email}         // User's email if available
{sender_name}          // User's name if available
{field_name}           // Value of specific field
{all_fields}           // HTML table of all fields
{submission_link}      // Link to submission in admin
```

### Editing Templates

1. Go to **Form Submissions → Email Templates**
2. Select template from dropdown
3. Edit HTML/variables
4. Save changes

### Custom Template

Create template in code:

```php
$template = '<h2>{form_title}</h2>
<p>New submission received:</p>
<table>
    <tr><td>Date:</td><td>{submission_date}</td></tr>
    <tr><td>Email:</td><td>{sender_email}</td></tr>
    <tr><td>Message:</td><td>{field_message}</td></tr>
</table>
<p><a href="{submission_link}">View in admin</a></p>';
```

---

## REST API

### Endpoints

#### GET `/wp-json/codeweber-forms/v1/forms/{id}`

Get form configuration.

**Response:**
```json
{
    "id": 123,
    "title": "Contact Form",
    "fields": [...],
    "settings": {...}
}
```

#### POST `/wp-json/codeweber-forms/v1/submit`

Submit form.

**Request:**
```json
{
    "formId": 123,
    "fields": {
        "name": "John Doe",
        "email": "john@example.com",
        "message": "Hello!"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Thank you!",
    "submissionId": "abc123"
}
```

#### POST `/wp-json/codeweber-forms/v1/form-opened`

Track form view.

**Request:**
```json
{
    "formId": 123
}
```

#### POST `/wp-json/codeweber-forms/v1/upload`

Upload file.

**Request:** multipart/form-data

**Response:**
```json
{
    "success": true,
    "fileId": "file-123",
    "fileName": "resume.pdf"
}
```

---

## Admin Interface

### Submissions Page

Access via **Form Submissions → All Submissions**

**Features:**
- View all submissions
- Filter by form, status, date range
- Search by content
- Mark as read/archived/deleted
- Download attachments
- View full submission details
- Export to CSV

### Settings

Access via **Form Submissions → Settings**

**Options:**
- Enable/disable submission saving
- Email configuration defaults
- Rate limiting settings
- Honeypot field configuration
- One-time token protection (Security section)

### Email Templates

Access via **Form Submissions → Email Templates**

**Edit:**
- Admin notification email
- User confirmation email
- Custom email templates

---

---

## Security

### Уровни защиты форм

Система использует многоуровневую защиту от спама и ботов:

| Уровень | Механизм | Что блокирует |
| ------- | -------- | ------------- |
| 1 | **WP Nonce** (`X-WP-Nonce` header) | Прямые POST-запросы без загрузки страницы |
| 2 | **Honeypot** | Простых ботов, заполняющих все поля |
| 3 | **One-time token** | Повторные отправки с одним nonce |
| 4 | **Rate limiting** | Массовые отправки с одного IP |

### One-time Token (cwf_token)

**Файл:** `functions/integrations/codeweber-forms/codeweber-forms-token.php`  
**Класс:** `CodeweberFormsToken`

**Механизм:**

```text
1. PHP рендерит форму → генерирует UUID → set_transient('cwf_token_' . $uuid, $form_id, 1800)
2. UUID вставляется в <input type="hidden" name="cwf_token">
3. JS передаёт токен в payload при отправке
4. API проверяет: get_transient() → delete_transient() → true/false
5. Повторная отправка с тем же токеном → 403 invalid_token
```

**Ключевые свойства:**

- TTL: 30 минут
- One-use: transient удаляется сразу после проверки
- Формат: UUID v4 (`/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/`)
- Работает для всех форм: CPT, inline блоки, шорткод

**Отключение (если сбой с transients/кешем):**

`Форма → Настройки → Security → One-time Form Token` — снять галку.

Или программно:

```php
$options = get_option('codeweber_forms_options', []);
$options['token_enabled'] = false;
update_option('codeweber_forms_options', $options);
```

**API-методы:**

```php
// Генерация (в render_from_config())
$token = CodeweberFormsToken::generate($form_id);
// → UUID, сохранён в transient cwf_token_{uuid}

// Верификация (в submit_form())
$ok = CodeweberFormsToken::verify($token);
// → true (и transient удалён) или false
```

### Phone Mask Validation

Поля `type="tel"` с маской (`data-mask`) имеют особенность: при фокусе маска
заполняет поле символами `+7 (___) ___-__-__`, что не является пустой строкой.
HTML5 `required` считает такое поле заполненным.

**Решение:** перед каждым вызовом `form.checkValidity()` JS устанавливает
`setCustomValidity` для замаскированных телефонных полей:

```javascript
// form-submit-universal.js — выполняется в двух местах:
// 1. В обработчике клика кнопки (до checkValidity)
// 2. В submitHandler (до checkValidity)

form.querySelectorAll('input[type="tel"][data-mask]').forEach(function(input) {
    const value = input.value || '';
    const hasRealDigits = value.replace(/\D/g, '').length > 0 && !value.includes('_');
    if (input.required && !hasRealDigits) {
        input.setCustomValidity('Введите номер телефона');
    } else {
        input.setCustomValidity('');
    }
});
```

Условие `hasRealDigits`:

- `''` → нет цифр → invalid ✓
- `'+7 (___) ___-__-__'` → содержит `_` → invalid ✓
- `'+7 (999) 123-45-67'` → цифры есть, нет `_` → valid ✓

---

## Best Practices

### Security Best Practices

1. **Always sanitize user input** - System does this, but verify in hooks
2. **Validate on both frontend and backend** - Don't trust client-side validation
3. **Enable honeypot** - Catches most bots
4. **Enable rate limiting** - Prevent spam attacks
5. **Use nonce verification** - System does this automatically

### Performance

1. **Cache form HTML** - For frequently rendered forms
2. **Limit file uploads** - Set reasonable file size limits
3. **Use indexed database fields** - For filtering/searching
4. **Archive old submissions** - Don't let database grow unbounded

### UX

1. **Clear success/error messages** - Users need feedback
2. **Progressive enhancement** - Forms work without JS
3. **Accessible field labels** - Use descriptive labels
4. **Mobile-friendly layout** - Test on mobile devices
5. **Auto-save drafts** - For long forms

### Integrations

1. **Send to CRM** - Via `codeweber_form_after_send` hook
2. **Webhook notifications** - To Slack, Teams, etc.
3. **Newsletter subscription** - Auto-subscribe users
4. **Lead scoring** - Qualify leads before sending to sales
5. **Automation** - Zapier, Make.com, IFTTT

---

## Troubleshooting

### Form Not Appearing

- Check form is published
- Verify correct form ID in shortcode
- Check browser console for JS errors
- Verify REST API is accessible

### Emails Not Sending

- Check SMTP configuration in Redux
- Verify recipient email in form settings
- Check `wp-content/debug.log` for errors
- Test with `wp_mail()` directly

### Submissions Not Saving

- Check database table exists: `wp_codeweber_forms_submissions`
- Verify `saveSubmissions` setting is enabled
- Check file permissions on database

### Validation Not Working

- Check field configuration
- Verify validation rules are set
- Check JavaScript console for errors
- Test backend validation separately

---

## Related Documentation

- **[HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md)** — Complete hooks catalog
- **[REST_API_REFERENCE.md](../api/REST_API_REFERENCE.md)** — REST endpoints
- **[CF7_INTEGRATION.md](CF7_INTEGRATION.md)** — Contact Form 7 compatibility
- **[CPT_CATALOG.md](../cpt/CPT_CATALOG.md)** — codeweber_form CPT details
