export const CONSENT_MODE_TYPES = ["analytics_storage", "ad_storage", "ad_user_data", "ad_personalization"];

export const CSS_VARIABLES = [
	{ key: "background", variable: "--cookiefox--background", label: "Background", default: "#ffffff" },
	{ key: "text_primary", variable: "--cookiefox--color-text-primary", label: "Primary text", default: "#000000" },
	{ key: "text_secondary", variable: "--cookiefox--color-text-secondary", label: "Secondary text", default: "#767676" },
	{ key: "button_primary", variable: "--cookiefox--color-button-primary", label: "Primary button", default: "#3D854F" },
	{ key: "button_secondary", variable: "--cookiefox--color-button-secondary", label: "Secondary button", default: "#767676" }
];

export const LANGUAGES = [
	{ code: "en", label: "English" },
	{ code: "de", label: "Deutsch" }
];

// Default texts from Helper::default_settings() / Frontend::prepare_data() and languages/cookiefox-de_DE.po.
const TEXTS = {
	en: {
		notice_title: "Privacy Settings",
		notice_text: "We use cookies to improve your experience on our site. To learn more, view our privacy policy.",
		notice_button_accept: "Accept",
		notice_button_save: "Save",
		notice_button_manage: "Manage cookies",
		notice_button_decline: "Decline",
		notice_button_back: "Back",
		notice_button_cookie_information: "Cookie information",
		notice_text_name: "Name",
		notice_text_vendor: "Vendor",
		notice_text_purpose: "Purpose",
		notice_text_privacy_policy: "Privacy Policy",
		notice_text_cookies: "Cookies",
		notice_text_duration: "Duration",
		notice_text_hosts: "Hosts"
	},
	de: {
		notice_title: "Datenschutz-Einstellungen",
		notice_text: "Wir verwenden Cookies um dir die bestmögliche Erfahrung auf unserer Seite zu bieten. Um mehr zu erfahren, schau dir die Datenschutzerklärung an.",
		notice_button_accept: "Akzeptieren",
		notice_button_save: "Speichern",
		notice_button_manage: "Cookies verwalten",
		notice_button_decline: "Ablehnen",
		notice_button_back: "Zurück",
		notice_button_cookie_information: "Cookie Informationen",
		notice_text_name: "Name",
		notice_text_vendor: "Anbieter",
		notice_text_purpose: "Zweck",
		notice_text_privacy_policy: "Datenschutzerklärung",
		notice_text_cookies: "Cookies",
		notice_text_duration: "Laufzeit",
		notice_text_hosts: "Hosts"
	}
};

// Mirrors Helper::default_settings() and Frontend::prepare_data() in PHP.
export function defaultData(lang = "en") {
	return Object.assign({
		consent_type: "simple",
		notice_display: "banner",
		notice_delay: 0,
		notice_button_decline_type: "button",
		cookie_name: "cookiefox_consent",
		cookie_expiration: "90",
		cookie_domain: "",
		consent_version: 0,
		consent_mode: false,
		disabled_on_privacy_page: false,
		lang: lang
	}, TEXTS[lang]);
}

export function textLanguage(data) {
	return TEXTS[data.lang] ? data.lang : "en";
}

// Switches the default texts to another language. Texts the user has changed are kept.
export function applyLanguage(data, lang) {
	const out = Object.assign({}, data, { lang: lang });
	Object.keys(TEXTS[lang]).forEach(key => {
		const value = data[key];
		const isDefault = value === undefined || value === "" || Object.keys(TEXTS).some(code => TEXTS[code][key] === value);
		if (isDefault) {
			out[key] = TEXTS[lang][key];
		}
	});
	return out;
}

export function defaultConfig() {
	return {
		scripts_always: "",
		scripts_consent: "",
		scripts_no_consent: "",
		cookies: "",
		categories: []
	};
}

export function defaultState() {
	return {
		data: defaultData(),
		config: defaultConfig(),
		assetBase: "/cookiefox/",
		colors: {}
	};
}

export function newCategory() {
	return {
		name: "",
		slug: "",
		description: "",
		order: 0,
		cookies: [],
		always_on: false,
		integrations: {},
		consent_mode: []
	};
}

export function newCookie() {
	return {
		name: "",
		slug: "",
		vendor: null,
		description: null,
		privacy_policy: null,
		cookies: null,
		duration: null,
		hosts: null,
		scripts_consent: null,
		scripts_no_consent: null,
		scripts_always: null
	};
}

export function slugify(value) {
	return String(value || "")
		.toLowerCase()
		.normalize("NFKD")
		.replace(/[̀-ͯ]/g, "")
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");
}

// Builds the runtime object exactly as the bundle reads it from window.cookiefox.
export function buildRuntime(state) {
	const data = Object.assign({}, state.data);
	data.consent_version = parseInt(data.consent_version, 10) || 0;
	data.notice_delay = parseInt(data.notice_delay, 10) || 0;

	const config = Object.assign({}, state.config);
	config.categories = (config.categories || []).map((category, i) => {
		const out = Object.assign({}, category, {
			slug: category.slug || slugify(category.name),
			order: i
		});
		out.cookies = (category.cookies || []).map(cookie => Object.assign({}, cookie, {
			slug: cookie.slug || slugify(cookie.name)
		}));
		return out;
	});

	return { data, config };
}

// JSON that is safe inside an inline <script>: "<" is escaped so "</script>" in a script field can't end the tag.
export function scriptJSON(value, indent) {
	return JSON.stringify(value, null, indent)
		.replace(/</g, "\\u003c");
}

function assetUrl(base, path) {
	if (!base) {
		return path;
	}
	return base.replace(/\/+$/, "") + "/" + path;
}

export function cssVariablesBlock(colors) {
	const lines = CSS_VARIABLES
		.filter(item => colors && colors[item.key])
		.map(item => "\t\t" + item.variable + ": " + colors[item.key] + ";");

	if (lines.length === 0) {
		return "";
	}

	return "<style>\n\t.cookiefox{\n" + lines.join("\n") + "\n\t}\n</style>";
}

// Port of Consent_Mode::print_head(): default denied state, restored from the consent cookie when present.
export function consentModeHead(runtime) {
	const data = runtime.data;
	const mapping = {};

	if (data.consent_type === "category") {
		runtime.config.categories.forEach(category => {
			const types = Array.isArray(category.consent_mode) ? category.consent_mode : [];
			if (category.slug && types.length) {
				mapping[category.slug] = { types: types, always_on: !!category.always_on };
			}
		});
	}

	const settings = {
		name: data.cookie_name || "cookiefox_consent",
		version: data.consent_version,
		category: data.consent_type === "category",
		mapping: mapping
	};

	return `<script>
window.dataLayer = window.dataLayer || [];
function gtag(){window.dataLayer.push(arguments);}
window.gtag = window.gtag || gtag;
(function(s){
	var types = ${JSON.stringify(CONSENT_MODE_TYPES)};
	function state(granted){var o = {};types.forEach(function(t){o[t] = granted(t) ? "granted" : "denied";});return o;}
	var stored = null;
	try {
		document.cookie.split("; ").forEach(function(part){
			var i = part.indexOf("=");
			if (part.slice(0, i) !== s.name) {return;}
			var c = JSON.parse(decodeURIComponent(part.slice(i + 1)));
			if ((parseInt(c.v, 10) || 0) !== s.version) {return;}
			if (s.category) {
				var accepted = (c.consent && c.consent.categories) || {};
				stored = state(function(t){
					return Object.keys(s.mapping).some(function(slug){
						var m = s.mapping[slug];
						return (accepted[slug] || m.always_on) && m.types.indexOf(t) !== -1;
					});
				});
			} else {
				stored = state(function(){return !!c.consent;});
			}
		});
	} catch (e) {}
	var initial = state(function(){return false;});
	if (!stored) {initial.wait_for_update = 500;}
	gtag("consent", "default", initial);
	if (stored) {gtag("consent", "update", stored);}
})(${scriptJSON(settings)});
</script>`;
}

export function headSnippet(state, runtime) {
	const parts = [
		'<link rel="stylesheet" href="' + assetUrl(state.assetBase, "css/main.css") + '">'
	];

	const css = cssVariablesBlock(state.colors);
	if (css) {
		parts.push(css);
	}

	if (runtime.data.consent_mode === true) {
		parts.push(consentModeHead(runtime));
	}

	return parts.join("\n");
}

export function bodySnippet(state, runtime, options = {}) {
	const indent = options.compact ? undefined : "\t";

	return [
		"<script>",
		"var cookiefox = " + scriptJSON(runtime, indent) + ";",
		"</script>",
		'<div id="cookiefox" data-nosnippet></div>',
		'<script src="' + assetUrl(state.assetBase, "js/main.js") + '" defer></script>'
	].join("\n");
}

// Accepts raw JSON ({data, config} or a share/export object) or any text containing
// "var cookiefox = {...};", e.g. the page source of a WordPress site running CookieFox,
// which writes the top-level keys unquoted.
export function parseImport(text) {
	const source = String(text || "").trim();
	if (source === "") {
		throw new Error("Nothing to import.");
	}

	const marker = source.match(/(?:var|let|const)\s+cookiefox\s*=\s*/);
	const start = marker ? marker.index + marker[0].length : source.indexOf("{");
	if (start === -1 || source[start] !== "{") {
		throw new Error("No configuration object found.");
	}

	let parsed;
	try {
		parsed = parseObjectLoose(source, start);
	} catch (e) {
		throw new Error("The configuration could not be read: " + e.message);
	}

	if (!parsed || typeof parsed !== "object" || (!parsed.data && !parsed.config)) {
		throw new Error('Expected an object with "data" and "config".');
	}

	return stateFromImport(parsed);
}

// Parses an object whose top-level keys may be unquoted; every value must be JSON.
function parseObjectLoose(source, start) {
	const result = {};
	let i = start + 1;
	const skip = () => {
		while (i < source.length && /\s/.test(source[i])) {
			i++;
		}
	};

	skip();
	if (source[i] === "}") {
		return result;
	}

	while (i < source.length) {
		skip();
		let key;
		if (source[i] === '"') {
			const end = valueEnd(source, i);
			key = JSON.parse(source.slice(i, end));
			i = end;
		} else {
			const match = /^[A-Za-z_$][\w$]*/.exec(source.slice(i));
			if (!match) {
				throw new Error("unexpected character at position " + i);
			}
			key = match[0];
			i += key.length;
		}

		skip();
		if (source[i] !== ":") {
			throw new Error('expected ":" after "' + key + '"');
		}
		i++;
		skip();

		const end = valueEnd(source, i);
		result[key] = JSON.parse(source.slice(i, end));
		i = end;

		skip();
		if (source[i] === ",") {
			i++;
		} else if (source[i] === "}") {
			return result;
		} else {
			throw new Error("unexpected character at position " + i);
		}
	}

	throw new Error("the object is incomplete");
}

// Index just past the JSON value starting at `start`, skipping brackets inside strings.
function valueEnd(source, start) {
	let depth = 0;
	let inString = false;
	let escaped = false;

	for (let i = start; i < source.length; i++) {
		const ch = source[i];
		if (inString) {
			if (escaped) {
				escaped = false;
			} else if (ch === "\\") {
				escaped = true;
			} else if (ch === '"') {
				inString = false;
				if (depth === 0) {
					return i + 1;
				}
			}
			continue;
		}
		if (ch === '"') {
			inString = true;
		} else if (ch === "{" || ch === "[") {
			depth++;
		} else if (ch === "}" || ch === "]") {
			if (depth === 0) {
				return i;
			}
			depth--;
			if (depth === 0) {
				return i + 1;
			}
		} else if (ch === "," && depth === 0) {
			return i;
		}
	}

	if (depth === 0 && !inString) {
		return source.length;
	}
	throw new Error("the object is incomplete");
}

function stateFromImport(parsed) {
	const state = defaultState();
	const data = parsed.data && typeof parsed.data === "object" ? parsed.data : {};
	const config = parsed.config && typeof parsed.config === "object" ? parsed.config : {};

	// Unknown keys are kept so a round-trip never drops anything.
	state.data = Object.assign(state.data, data);
	state.config = Object.assign(state.config, config);
	state.config.categories = (Array.isArray(state.config.categories) ? state.config.categories : []).map(category => {
		const out = Object.assign(newCategory(), category);
		out.cookies = (Array.isArray(out.cookies) ? out.cookies : []).map(cookie => Object.assign(newCookie(), cookie));
		out.consent_mode = Array.isArray(out.consent_mode) ? out.consent_mode : [];
		out.integrations = out.integrations && typeof out.integrations === "object" && !Array.isArray(out.integrations) ? out.integrations : {};
		return out;
	});
	state.data.consent_mode = state.data.consent_mode === true || state.data.consent_mode === "on";

	if (typeof parsed.assetBase === "string") {
		state.assetBase = parsed.assetBase;
	}

	if (parsed.colors && typeof parsed.colors === "object") {
		state.colors = Object.assign({}, parsed.colors);
	} else {
		// WordPress keeps its color settings in the same option, so they come along in "data".
		CSS_VARIABLES.forEach(item => {
			const value = data["color_" + item.key];
			if (typeof value === "string" && value !== "" && value.toLowerCase() !== item.default.toLowerCase()) {
				state.colors[item.key] = value;
			}
		});
	}

	return state;
}

export function encodeShare(state) {
	const json = JSON.stringify({
		data: state.data,
		config: state.config,
		assetBase: state.assetBase,
		colors: state.colors
	});
	const bytes = new TextEncoder().encode(json);
	let binary = "";
	for (let i = 0; i < bytes.length; i++) {
		binary += String.fromCharCode(bytes[i]);
	}
	return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
}

export function decodeShare(value) {
	const base64 = value.replace(/-/g, "+").replace(/_/g, "/");
	const binary = atob(base64 + "===".slice((base64.length + 3) % 4));
	const bytes = new Uint8Array(binary.length);
	for (let i = 0; i < binary.length; i++) {
		bytes[i] = binary.charCodeAt(i);
	}
	return stateFromImport(JSON.parse(new TextDecoder().decode(bytes)));
}
