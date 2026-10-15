# Using CookieFox without WordPress

The CookieFox frontend is a single script and stylesheet. It runs on any website: all settings are embedded in the page, so no server or API is needed.

## Quick start with the configurator

1. Open the configurator at <https://fabianpimminger.github.io/cookiefox/>. To run it locally instead, run `npm run build`, then `npm run configurator`, and open <http://127.0.0.1:9401/configurator/>.
2. Pick English or German default texts, and start with the defaults or load an existing configuration.
3. Walk through the steps. The live preview uses the real script.
4. On "Get the code", download `js/main.js` and `css/main.css` and upload them to your site (for example to `/cookiefox/`). Then copy the two generated snippets into your pages, one into `<head>` and one before `</body>`.

The compiled files are also in the `assets/frontend/` folder of the WordPress.org plugin ZIP.

To change the configuration later, use either of these:

- **The link:** the configurator keeps the whole configuration in the URL (`#config=…`). Bookmark it or use "Copy link to this configuration".
- **Paste:** on the Start step, paste the generated snippet or the `var cookiefox = …` line from a WordPress site's page source.

Settings that the form doesn't know about are kept when you edit and export again.

## What the snippet contains

```html
<!-- In <head> -->
<link rel="stylesheet" href="/cookiefox/css/main.css">

<!-- Before </body> -->
<script>
var cookiefox = {"data": {…}, "config": {…}};
</script>
<div id="cookiefox" data-nosnippet></div>
<script src="/cookiefox/js/main.js" defer></script>
```

`var cookiefox` must be defined before `main.js` runs. The script replaces `#cookiefox` with the notice. CookieFox does not fill in defaults, so `data` and `config` must be complete. Use the configurator rather than writing them by hand.

Inside an inline `<script>`, escape `<` in the JSON as `<`, otherwise a `</script>` in one of the script fields ends the tag early. The configurator does this for you.

### `data`

| Key | Description |
| --- | --- |
| `consent_type` | `simple` (one accept/decline) or `category` (per category) |
| `notice_display` | `banner` or `modal` |
| `notice_delay` | Seconds before the notice appears |
| `notice_title`, `notice_text` | Title, and body text (HTML allowed) |
| `notice_button_accept`, `notice_button_decline`, `notice_button_save`, `notice_button_manage`, `notice_button_back`, `notice_button_cookie_information` | Button labels |
| `notice_button_decline_type` | `button`, `text` or `none` |
| `notice_text_name`, `_vendor`, `_purpose`, `_privacy_policy`, `_cookies`, `_duration`, `_hosts` | Labels in the cookie details view |
| `cookie_name`, `cookie_expiration`, `cookie_domain` | The consent cookie: name, lifetime in days, optional domain |
| `consent_version` | Raise it to ask every visitor for consent again |
| `consent_mode` | `true` sends Google Consent Mode v2 updates (also needs the `<head>` snippet) |
| `disabled_on_privacy_page` | `true` hides the notice on this page |

### `config`

| Key | Description |
| --- | --- |
| `scripts_consent` | HTML injected after the visitor accepts (simple mode) |
| `scripts_no_consent` | HTML injected after the visitor declines (simple mode) |
| `scripts_always` | HTML injected on every page view |
| `cookies` | Comma-separated cookie names to delete on decline (simple mode) |
| `categories` | Category mode: a list of `{name, slug, description, always_on, consent_mode: [...], cookies: [...]}` |

Each entry in a category's `cookies` list has these keys:

- `name`, `slug`
- `vendor`, `description`, `privacy_policy`
- `cookies`, `duration`, `hosts`
- `scripts_consent`, `scripts_no_consent`, `scripts_always`

## Styling

The notice is styled through CSS variables on `.cookiefox`. The configurator's Appearance step writes the color variables into the `<head>` snippet; add any others to your own stylesheet:

```css
.cookiefox{
	--cookiefox--font-family: inherit;
	--cookiefox--background: #ffffff;
	--cookiefox--color-text-primary: #000000;
	--cookiefox--color-text-secondary: #767676;
	--cookiefox--color-button-primary: #3D854F;
	--cookiefox--color-button-secondary: #767676;
	--cookiefox__button--border-radius: 5px;
}
```

More variables: `--cookiefox--font-size`, `--cookiefox--line-height`, `--cookiefox__title--font-size`, `--cookiefox__title--font-weight`, `--cookiefox__text--font-size`, `--cookiefox__button--font-size`, `--cookiefox__button--font-weight`, `--cookiefox__button--text-transform`, `--cookiefox__modal--border-radius`, `--cookiefox__notice--box-shadow`, `--cookiefox__footer--background`.

## Reopening the notice, API and events

- Any element with `class="open-cookiefox"` reopens the notice, for example a "Cookie settings" link in the footer.
- `window.cookiefox.api` provides `show()`, `getConsent()`, `setConsent(consent, category)` and `updateConsent()`.
- On `document`, the `cookiefox:init` event fires once consent is known. `cookiefox:consent-changed` fires after every change, with the consent in `event.detail`.
