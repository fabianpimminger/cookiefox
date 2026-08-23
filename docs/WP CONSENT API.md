# WP Consent API (CookieFox as CMP)

CookieFox becomes a consent *provider* for [WP Consent API](https://wordpress.org/plugins/wp-consent-api/). That plugin is the bus; CookieFox is the banner. Plugins that already call `wp_has_consent('statistics')` (Site Kit, Jetpack `honor_cookie_consent`, Woo, WP Statistics, Burst, …) then follow CookieFox without per-plugin connectors.

Do **not** bundle WP Consent API. Do **not** replace CookieFox’s own `text/plain` blocking. The API is extra signal for well-behaved plugins; CookieFox integrations stay the hard block for plugins we wrap.

---

## How the API works

Two facts together decide if a category is allowed:

1. **Consent type** (`optin` / `optout`). Filter `wp_get_consent_type`. If no CMP sets it, it is `false` and **every category is allowed** (fail-open). CookieFox is GDPR opt-in, so this **must** be `optin` in the head, or the API is useless.
2. **Visitor choice** per category: unset / `allow` / `deny`. JS `wp_set_consent('marketing', 'allow')` writes `wp_consent_*` cookies the API plugin owns. PHP `wp_has_consent('marketing')` reads those.

Categories (fixed): `functional`, `preferences`, `statistics`, `statistics-anonymous`, `marketing`.

JS events tracking plugins listen for: `wp_consent_type_defined`, `wp_listen_for_consent_change` (detail = `{ statistics: 'allow', ... }`).

CMP must:

- `add_filter( "wp_consent_api_registered_{$plugin}", '__return_true' )` (`$plugin` = `cookiefox/cookiefox.php`)
- `add_filter( 'wp_get_consent_type', … )` → `'optin'`
- Head: `window.wp_consent_type = 'optin'` + `document.dispatchEvent(new CustomEvent('wp_consent_type_defined'))`
- On CookieFox consent change (and returning visitor): `wp_set_consent(category, 'allow'|'deny')` for each API category **before** injecting blocked scripts (same order as Google Consent Mode)

If `wp-consent-api` is not active, CookieFox does nothing with this API (current behavior).

---

## Mapping (same idea as Google Consent Mode)

CookieFox categories are user-defined. Do not guess slugs.

**On each `cookiefox_category`:** a second multicheck, WP Consent API categories (next to Google Consent Mode Signals).

Sample content:

| CookieFox category | WP Consent API |
| --- | --- |
| Functional (always on) | `functional`, `preferences` |
| Statistics | `statistics`, `statistics-anonymous` |
| Marketing | `marketing` |
| External Media | (none) |

Runtime: granted API categories = **union** of accepted CookieFox categories (always-on included). Unmapped API categories stay `deny` under opt-in.

**Simple mode:** Accept → all five `allow`. Decline → `functional` + `preferences` `allow`, the rest `deny` (essential cookies still needed).

Filter: `cookiefox_wp_consent_state` (`$state`, `$consent`) — values `'allow'` / `'deny'`.

Admin warning (category mode): toggle on, no category has any WP API signal assigned.

Do not put WP API flags on Always On except Functional/preferences. Description: same “do not assign tracking categories to Always On” as Google CM.

---

## When it runs

Same pattern as Google Consent Mode: an explicit toggle, **off by default**. The toggle (and the category WP Consent API checkboxes) **only appear when `wp-consent-api` is active**. If that plugin is missing, CookieFox does not register as CMP and does not set `wp_consent_type` (fail-open stays the API’s problem, not ours).

Runs on the front end when:

- Cookie notice is enabled
- WP Consent API plugin is active
- CookieFox setting `wp_consent_api` is on

Place the toggle in its own settings section (next to Google Consent Mode). Category term field `wp_consent` only in the taxonomy UI if the plugin is installed.

`wp` / `wp_head` 0 (alongside Google Consent Mode snippet, or the same printer). Footer JS still does `wp_set_consent` on accept/deny/save / `setConsent`.

Order in `handleConsentChange`:

1. Google Consent Mode `gtag('consent', 'update', …)`
2. `wp_set_consent` for each mapped API category
3. Inject CookieFox blocked scripts (`activateIntegration`)

That way Jetpack’s `honor_cookie_consent` gate sees `statistics=allow` when the stats script starts.

---

## Reprompt

Ask again bumps `cookiefox_consent_version` but leaves `wp_consent_*` cookies. PHP plugins would still see last visit’s `allow`.

On reprompt: delete WP Consent API cookies (use the API prefix / `consent_api` cookie helpers if present). Until the visitor chooses again, `wp_has_consent` under opt-in is false.

---

## Out of scope (v1)

- Service-level API (`wp_set_service_consent('google-analytics')`) — CookieFox has no per-service banner toggles; category is enough. Later: map integration slugs to services.
- Bundling `wp-consent-api`
- Turning off CookieFox Site Kit / Jetpack / MI connectors when the API is present (double-block is OK; API is for plugins we do not wrap)
- Region / opt-out consent type
- `wp_add_cookie_info` cookie registry (nice later, not required to speak the API)

---

## Files

- `includes/class-wp_consent_api.php` — detect plugin, register CMP, head snippet, PHP type filter, cookie clear on reprompt, term-meta helpers (mirror `Consent_Mode`)
- Category CMB2 multicheck `wp_consent` + sample content + DE strings
- `src/js/consent-mode.js` or sibling `wp-consent-api.js` — `applyWpConsent(data, config, consent)` called from Simple/Category `handleConsentChange` next to `applyConsentMode`
- Runtime config: `category.wp_consent = [ 'statistics', … ]` for JS mapping
- `data.wp_consent_api` boolean: plugin active **and** toggle on (JS no-ops otherwise)
- Helper default: `'wp_consent_api' => 'off'`
- e2e: optional fake `wp_set_consent` in the fixture; assert it is called with `allow`/`deny` after accept/decline

---

## Wiki (developer)

New page or a section on Integrations: CookieFox as WP Consent API CMP; hooks; mapping term meta; `wp_set_consent` / `wp_consent_type_defined`; filter `cookiefox_wp_consent_state`.
