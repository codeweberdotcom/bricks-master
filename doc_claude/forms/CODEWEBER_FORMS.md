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

1. Verify nonce
2. Fire codeweber_form_before_send hook
3. Validate & sanitize input
4. Check rate limiting
5. Save submission to database
6. Fire codeweber_form_saved hook
7. Send email notification
8. Fire codeweber_form_after_send hook
9. Return success response
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
   - **Form Type**: form | newsletter | testimonial | callback | resume
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

// Phone/Tel
[
    'type' => 'tel',
    'name' => 'phone',
    'label' => 'Phone Number',
    'validation' => 'tel',  // Phone format
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

        // Rate limiting
        'rateLimit' => true,
        'rateLimitPerMinute' => 5,     // Submissions per minute per IP
        'rateLimitPerHour' => 50,      // Submissions per hour per IP

        // Nonce verification (automatic, but can be disabled)
        'verifyNonce' => true,

        // reCAPTCHA v3
        'recaptchaEnabled' => false,
        'recaptchaKey' => '...',
        'recaptchaThreshold' => 0.5,
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

### Email Templates

Access via **Form Submissions → Email Templates**

**Edit:**
- Admin notification email
- User confirmation email
- Custom email templates

---

## Best Practices

### Security

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
