# CookieFox audit (August 2026)

CookieFox is still a solid, small-footprint consent plugin (400+ installs, 5 stars). After a year idle it has drifted behind WordPress, its JS stack, and what Google/GDPR expect. WordPress.org already shows the **“hasn’t been tested with the latest 3 major releases”** warning (current WP is **7.1**; the plugin is tested to 6.8). That warning is the highest-leverage fix you can ship.

The architecture is still reasonable: singleton PHP, CPT + taxonomy for category mode, Svelte widget, tiny JS/CSS. Don’t rewrite it. Refresh it, then close the product gaps (Consent Mode, re-consent, proof of consent).

Last commit at the time of this audit: `2025-06-22` (2.0.11).

---

## Current snapshot

| Area | Today | Reality in 2026 |
|---|---|---|
| Plugin version | 2.0.11 (June 2025) | Last commit 2025-06-22 |
| WordPress | Requires 5.0, tested 6.8.1 (header) / 6.4.3 (`readme.txt`) | WP 7.1 is current |
| PHP | Requires 7.3 | 7.3 EOL since 2021; CMB2 2.12 wants 7.4+ |
| Frontend | Svelte 3.46, Rollup 2, js-cookie 2.2 | Svelte 5, Rollup 4, js-cookie 3 |
| Admin UI | CMB2 2.10.1 (Feb 2022) | CMB2 **2.12.0** (May 2026, WP 7 / PHP 8.3) |
| npm audit | 24 issues (2 critical, 19 high) | `js-cookie` is a **runtime** high; rest is mostly build-time |
| Tests | None | Manual only |

---

## Bugs to fix first

These are real, not stylistic.

### 1. Cookie domain is broken

In `src/js/functions.js`, the domain option is assigned the **expiration number**, not the domain:

```js
if(data.cookie_domain !== undefined && data.cookie_domain !== ""){
    options.domain = parseInt(data.cookie_expiration);
}
```

Anyone using “Cookie Domain” gets `domain=90`, so the consent cookie is not stored as intended. Should be `options.domain = data.cookie_domain`. Also set `secure: true` on HTTPS, and use the same domain/path when **removing** cookies in `removeCookies()`.

### 2. Category mode silently drops cookies after 10

`includes/class-rest_api.php` runs `WP_Query` **without `posts_per_page`**. That uses the site’s “Blog pages show at most” setting (usually 10). Categories with more than 10 cookies never reach the banner. Add `'posts_per_page' => -1` and `wp_reset_postdata()`. Also guard `get_terms()` against `WP_Error`.

### 3. Custom font CSS variable is misspelled

Settings write `--cookiefox--font-family`, but the component reads `--cookiefox-font-family` (one dash) in `src/js/CookieFox.svelte`:

```scss
font-family: var(--cookiefox-font-family);
```

Same typo on `.cookiefox__text`. Result: Arial/Verdana/etc. apply to the title only; body text always inherits the theme font.

### 4. Invalid “Stylesheet” default

The settings field default is `'theme'`, but the options are `external` / `inline` / `none` (`includes/class-settings.php`). `Helper` already defaults to `'external'`. Align the CMB2 default with that.

### 5. Corrupted consent cookie or failed REST call can crash the UI

- `JSON.parse(cookieData)` in both Svelte components has no `try/catch`. A truncated/edited cookie takes down the whole notice.
- If `/cookiefox/v1/cookies` fails, `config` stays `null` but `loaded` becomes `true`. Accept/Decline then throws on `config.categories.forEach`.
- Crawlers in category mode return early **before** `loadConfig()`, so always-on scripts never run for bots.

### 6. Sample content has a nonce but no capability check

In `includes/class-sample_content.php`, the installer verifies a nonce but does not check `current_user_can('manage_options')`. WordPress.org reviews expect both. `post_exists($title)` is also global (any post type), so a page titled “Sample Cookie” would skip creation.

### 7. Polylang REST language fallback is a no-op

In `includes/class-internationalization.php`:

```php
if (!in_array($current_language, $langs)) {
    $cur_lang = $default;
}
```

`$cur_lang` is never used. Invalid `?lang=` is not replaced with the default.

### 8. Version constants and listings are inconsistent

- Plugin header: **2.0.11**, `COOKIEFOX_VERSION` in `class-main.php`: **1.0.0**, `package.json`: **1.0.0**
- `readme.txt` “Tested up to: **6.4.3**” vs header **6.8.1** — WordPress.org uses `readme.txt`
- `package.json` license is **ISC**, plugin is **GPL-2.0**

### 9. Focus trap on banners contradicts the product

The notice always has `role="dialog"`, `aria-modal="true"`, and a focus trap — including **banner** mode, whose own copy says users can keep browsing. Tabbing the rest of the page is blocked. Trap + `aria-modal` should only apply to **modal**.

Escape on a banner currently **declines** (simple) or **saves current checkboxes** (category). Easy to trigger by accident.

---

## Security notes

Not a dumpster fire, but a plugin review would flag these.

- **Runtime:** `js-cookie` ≤ 3.0.5 has a high cookie-attribute injection advisory. You are on 2.2.1. Upgrade to **3.0.8** (API change: default export).
- **`{@html data.notice_text}`** and cookie descriptions are unsanitized on the way out. `notice_text` is a plain textarea. Run `wp_kses_post` in `prepare_data()`.
- Embed wrapper uses `htmlspecialchars()`; prefer `esc_attr()`.
- CMB2 button field echoes URL/label without escaping (`includes/cmb2/class-cmb2-button.php`).
- Shortcode uses `href="javascript:void(0)"` and `onclick=` — breaks under strict CSP; use a `<button>` or `href="#"` plus a listener (`.open-cookiefox` already exists).
- Consent **scripts** are stored as HTML and injected via `innerHTML` + clone. That is the feature, but the public REST endpoint returns those scripts to anyone. Fine if intended; document it. Don’t put secrets in opt-in scripts.
- Inline `<style>` colors use `esc_html()`. Prefer `sanitize_hex_color()`.
- `cmb2-conditional-logic.min.js` is enqueued on **every** admin page.
- No `uninstall.php` — options, CPT posts, and terms stay behind.

Most `npm audit` hits (`svelte` SSR XSS, `terser`, `ws`, `shell-quote`) are **devDependencies**. Still worth cleaning by upgrading the toolchain.

---

## Compliance / product gaps

CookieFox does **not** block cookies; it only delays scripts you configured. That is honest (the FAQ says so), but the market moved.

1. **Google Consent Mode v2** — required since March 2024 for Google Ads/Analytics in the EEA. A 5-star review even says users have to wire GCM themselves via GTM. Built-in `gtag('consent', 'default'|'update')` (and optional Microsoft UET / Meta) would be the single biggest feature.
2. **No consent versioning** — adding a new category does not re-prompt. Store a config hash/version in the cookie and show the notice again when cookies/categories change.
3. **No proof of consent** (GDPR Art. 7) — no timestamp, version, or log. Even a local cookie payload `{ consent, version, ts }` plus an optional `consent_id` would help.
4. **90-day expiry** is conservative (good); make the legal default explicit in the UI.
5. **Always-on scripts** run before any choice. Keep that only for strictly necessary cookies; don’t let marketing scripts live there.
6. Embed blocking only hooks `embed_oembed_html` / `video_embed_html`. Raw iframes, Custom HTML blocks, and GTM-injected embeds still load. A `render_block` filter (and optional script-src blocking) would close the obvious hole.
7. Decline can be set to **None** — there is already a warning. Consider making it harder (or hiding it in EU locales).

IAB TCF is not needed unless competing with Cookiebot. GCM + re-consent + a consent log cover most “is this still viable?” questions.

---

## Accessibility

The plugin already cares about this (focus-trap, `:focus-visible`, decline as a real button). Leftovers:

| Issue | Why it matters |
|---|---|
| Banner uses `aria-modal="true"` + focus trap | Incorrect for a non-modal banner |
| Title is `role="heading" aria-level="1"` | An `h1` at the bottom of every page; use `h2` |
| Custom checkboxes via `-webkit-appearance: none` | Verify name/state with a screen reader |
| Toggle in admin hides the real checkbox (`display: none`) | Not keyboard/SR friendly |
| `onclick` / `javascript:` URLs | Not keyboard-first, CSP-hostile |

---

## What to modernize

### Frontend packages

**Runtime (ships to users)**

| Package | You have | Go to | Notes |
|---|---|---|---|
| `js-cookie` | 2.2.1 | **3.0.8** | Security + ESM. Or drop it and use a small `document.cookie` helper. |
| `focus-trap` | 6.7.3 | **7 or 8** | API is close |
| `core-js` | 3.21 | drop with IE11 | Only needed for the legacy bundle |

**Build (dev)**

| Package | You have | Go to |
|---|---|---|
| `svelte` | 3.46 (EOL, audit noise) | **4.2** first (low-risk). Svelte 5 later if you want runes. |
| `svelte-preprocess` | 4.10 | 6.x (with Svelte 4+) |
| `rollup` | 2.70 | **4.x** |
| `rollup-plugin-terser` | 7 (vulnerable `serialize-javascript`) | **`@rollup/plugin-terser`** |
| `@rollup/plugin-*` | 2022-era | current majors |
| `@babel/*` | 7.16–7.17 | stay on Babel 7 unless you keep a legacy bundle |

**Drop the legacy/IE11 build.** IE11 is gone. That removes Babel, `core-js`, half of Rollup config, and the “Javascript: Modern vs Legacy” setting. `main.js` is 12 kb gzip vs `legacy.js` at 32 kb.

Svelte 4 is the sweet spot: same component syntax, supported, smaller jump than Svelte 5. Do **not** replace Svelte with vanilla — the UI is small but the reactivity (stores, category checkboxes, views) is what Svelte is good at.

### PHP / Composer

- Bump **Requires PHP** to **7.4** (minimum for CMB2 2.12) or **8.1** if leaving ancient hosts behind. WordPress 7.1 supports 8.5.
- Bump **Requires at least** WordPress to **6.2** or **6.4**.
- Update CMB2: `"cmb2/cmb2": "^2.12.0"`. 2.12 fixes PHP 8.1–8.3 deprecations and WP 7 input styling.
- Add `"php": ">=7.4"` to `composer.json`.
- Trim the release zip: CMB2’s `languages/`, `example-functions.php`, `sass/`, `package-lock.json` should not ship. The vendor tree is the bulk of the plugin.

Replacing CMB2 with the Settings API would shrink the plugin a lot, but it is a rewrite of the whole admin. **Update CMB2; don’t replace it now.**

### WordPress APIs that aged

- `wp_register_script( ..., true )` — use the `$args` array (`in_footer`, optionally `strategy => 'defer'`).
- `load_plugin_textdomain()` on WP.org plugins is unnecessary since 6.7 JIT translations and can cause “loaded too early” notices. Defaults in `Helper::default_settings()` call `__()` when options are missing; `Post_Type` reads options at **file load** time, which is too early for i18n.
- `filemtime()` as script version is fine; also use a real `COOKIEFOX_VERSION` so CDNs can cache predictably.
- CPT `show_in_rest => true` with `publicly_queryable => false` is OK, but cookie posts (including scripts) are in the REST index for users who can edit them — expected.

### Tooling you don’t have

- No PHPCS / WPCS, no EditorConfig, mixed tabs/spaces (settings file even mixes both in one field).
- No tests. Even 10 PHPUnit cases (REST payload, defaults, sample content caps, cookie domain helper) would lock the bugs above.
- `.gitignore` excludes `vendor/` **and** built `assets/frontend/{js,css}/`. Fine for GitHub if the WP.org zip is built separately; confusing for contributors. Document `composer install && npm run build`.
- `.DS_Store` is in the repo (gitignore didn’t catch the existing file).
- `class-admin.php` is an empty stub.
- Dead `console.log` in `setConsent()`.

---

## Performance

What is still good: one small JS file, optional CSS, one footer `<div>`, no jQuery.

What to tighten:

- **Category mode does an extra REST request on every page** just to learn categories/scripts. `window.cookiefox.config` already exists as an escape hatch (commit `dea8c60`). Default should be: print categories in the footer JSON (or a `wp_add_inline_script`) and skip `fetch`. REST can stay for caching/WPML.
- That REST handler is N+1 (`WP_Query` per category). One query + `tax_query` / `wp_get_object_terms` is enough.
- Conditional logic JS on all admin screens.
- Inline `var cookiefox = {data: ...}` dumps **all** options (colors, cookie domain, scripts) into every HTML page. Strip to what the UI needs.

---

## WordPress.org housekeeping

The directory currently paints the plugin as unmaintained. A 2.0.12 that only:

1. Sets **Tested up to: 7.1** in **`readme.txt`** (not just the header)
2. Fixes cookie domain + `posts_per_page`
3. Updates CMB2

…would restore trust for existing installs more than any new feature.

Also: Upgrade Notice still says “tested with 5.9.3”. Donate link is `http://`. Tags include GDPR/CCPA in the readme header — WP.org now limits tags; keep the five that already work.

---

## Suggested roadmap

### Release 2.0.12 — “still alive” (1–2 days)

- Cookie domain bug, `posts_per_page => -1`, font CSS variable, stylesheet default
- `try/catch` around cookie JSON; don’t enable buttons until config exists
- Sample content: `current_user_can`
- CMB2 → 2.12
- `readme.txt` Tested up to **7.1**, PHP 7.4+, sync versions
- `js-cookie` → 3.0.8
- Banner: no focus trap / `aria-modal`

This is the release that removes the abandoned-plugin banner.

### 2.1 — modern baseline

- Drop IE11/legacy build and Babel
- Rollup 4 + `@rollup/plugin-terser`, Svelte 4
- Inline category config (kill the extra REST hop by default)
- `wp_kses_post` on texts, `sanitize_hex_color`, capability checks
- `uninstall.php`
- Consent cookie: `Secure`, correct domain, optional version key
- `defer` strategy, stop loading admin JS globally

### 2.2 — stay competitive

- Google Consent Mode v2 helper (default denied, update on accept/save)
- Re-prompt when cookie/category config changes
- Broader embed blocking (`render_block`, optional iframe filter)
- Optional consent timestamp in the cookie
- PHPCS + a handful of PHPUnit tests

### Later / only if growing the plugin

- Svelte 5
- Replace CMB2 with Settings API (smaller zip)
- Consent log CPT
- Auto-blocking of known script handles (`wp_print_scripts`)
- IAB TCF (expensive, probably not worth it at 400 installs)

---

## Bottom line

The original pitch — small, accessible, no jQuery — is still the right one. The work that matters is: **fix the cookie-domain and REST pagination bugs, bump “tested up to”, update CMB2/js-cookie, then add Consent Mode.** Everything else is hygiene.
