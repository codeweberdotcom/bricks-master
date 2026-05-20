# Notifications System

Visitor-triggered notification system. A single active notification fires when a trigger condition is met. Supports three output types: modal window, CW Notify toast, Telegram message.

**CPT:** `notifications` (`functions/cpt/cpt-notifications.php`)  
**Frontend:** `src/assets/js/notification-triggers.js` → compiled to `dist/`  
**Container:** `functions/integrations/modal/modal-container.php`  
**Telegram AJAX:** `functions/integrations/telegram/telegram-init.php`

---

## How It Works

1. PHP (`codeweber_get_active_notification_modal()`) finds the first active notification within its date range.
2. The notification div is rendered in the footer with all config as `data-*` attributes.
3. `notification-triggers.js` reads the attributes, waits for the trigger, then fires.
4. Script is only enqueued when `wp_count_posts('notifications')->publish > 0`.

---

## Notification Types

### `modal`
Displays a Bootstrap modal. Content comes from the Modal CPT post.

### `cw_notify`
Shows a toast notification via `window.CWNotify.show()`.

### `telegram`
On trigger: JS sends AJAX `codeweber_notification_telegram` → PHP replaces variables → `CW_Telegram_Bot::from_redux()` sends message.

**Message variables:** `{title}`, `{site_name}`, `{site_url}`, `{page_url}`, `{ip}`, `{user_agent}`, `{date}`, `{utm_source}`, `{utm_medium}`, `{utm_campaign}`, `{utm_term}`, `{utm_content}`

---

## Trigger Types

| Type | Description |
|------|-------------|
| `delay` | Fires after `wait_delay` ms on page load |
| `inactivity` | Fires after N ms of no mouse/keyboard activity |
| `viewport` | Fires when element ID enters viewport |
| `scroll_middle` | Fires when user scrolls past 50% of page |
| `scroll_end` | Fires when user reaches bottom of page |
| `codeweber_form` | Fires on CodeWeber Form success event |
| `cf7_form` | Fires on CF7 `wpcf7mailsent` event |
| `woocommerce_order` | Fires on WooCommerce thank-you page |
| `page` | Fires only on specific page/post/archive (PHP-side check) |
| `utm_param` | Fires only when UTM param + value match URL (both required) |

---

## Composite Trigger Chain

Replaces single trigger with a sequential chain. Steps must fire in order. Cookie `cw_notif_{id}_chain` stores current step index.

- Enable via checkbox **Composite Trigger Chain** in meta box
- Add steps via repeater (step types same as trigger types above)
- **Cookie Lifetime** — hours to remember completed steps (default 24)
- After all steps complete → notification fires

---

## Max Firings

Controls how many times a notification fires per visitor.

| Meta key | Description | Default |
|----------|-------------|---------|
| `_notification_max_firings` | Max fires (0 = unlimited) | 1 |
| `_notification_count_reset` | Hours before counter resets (0 = session cookie) | 720 |

Cookie: `cw_notif_{id}_count` — stores fire count per visitor.

JS functions: `canFire()` checks cookie vs limit; `recordFire()` increments cookie. Both called in: `chainFire()`, `showCwNotify()`, `sendTelegramNotification()`, `showNotificationModal()`.

---

## Data Attributes (HTML)

All notification divs carry:

```
data-notification-id        — CPT post ID
data-notification-type      — modal | cw_notify | telegram
data-wait                   — wait_delay ms
data-trigger-type           — trigger type slug
data-trigger-inactivity     — ms for inactivity trigger
data-trigger-viewport       — element ID for viewport trigger
data-trigger-utm-param      — UTM parameter name
data-trigger-utm-value      — UTM parameter expected value
data-composite              — JSON array of chain steps
data-composite-lifetime     — hours for chain cookie
data-max-firings            — max fires (0=unlimited)
data-count-reset            — hours before count resets
```

---

## Scheduling

- `_notification_start_date` / `_notification_end_date` — stored as `Y-m-d H:i:s`
- `codeweber_get_active_notification_modal()` compares `current_time('timestamp')` against these
- Returns first matching notification; only one fires at a time

---

## Gotchas

- **Only one notification active at a time** — first match in `get_posts()` order wins
- **`utm_param` trigger:** both param AND value must be non-empty, otherwise never fires
- **`page` trigger:** checked PHP-side in `codeweber_get_active_notification_modal()` — wrong page = no div rendered
- **Composite steps order is strict** — step N cannot fire before step N-1
- **JS compiled** — changes to `src/assets/js/notification-triggers.js` require `npm run build`
