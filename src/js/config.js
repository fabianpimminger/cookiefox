const TTL_MS = 10 * 60 * 1000;

function emptyConfig() {
	return {
		scripts_always: "",
		scripts_consent: "",
		scripts_no_consent: "",
		cookies: "",
		categories: []
	};
}

function storageKey(lang) {
	return "cookiefox_config:" + (lang || "");
}

export function isConfigReady(config) {
	return config !== null && config !== undefined && typeof config === "object";
}

function readCache(lang) {
	try {
		const raw = sessionStorage.getItem(storageKey(lang));
		if (!raw) {
			return null;
		}

		const parsed = JSON.parse(raw);
		if (!parsed || !parsed.savedAt || !parsed.config || typeof parsed.config !== "object") {
			return null;
		}

		if (Date.now() - parsed.savedAt > TTL_MS) {
			sessionStorage.removeItem(storageKey(lang));
			return null;
		}

		return parsed.config;
	} catch (e) {
		return null;
	}
}

function writeCache(lang, config) {
	try {
		sessionStorage.setItem(storageKey(lang), JSON.stringify({
			savedAt: Date.now(),
			config
		}));
	} catch (e) {
		// Ignore quota / private mode.
	}
}

export function loadConfig(data, config) {
	if (isConfigReady(config)) {
		return Promise.resolve(config);
	}

	const cached = readCache(data && data.lang);
	if (cached) {
		return Promise.resolve(cached);
	}

	const apiBase = (data && data.api_base) || "";
	const lang = (data && data.lang) || "";
	const joiner = apiBase.includes("?") ? "&" : "?";
	const url = apiBase + "cookiefox/v1/cookies" + joiner + "lang=" + encodeURIComponent(lang);

	return fetch(url)
		.then(response => {
			if (!response.ok) {
				throw new Error("Failed to load cookie config");
			}
			return response.json();
		})
		.then(payload => {
			if (!payload || typeof payload !== "object") {
				return emptyConfig();
			}
			writeCache(lang, payload);
			return payload;
		})
		.catch(() => emptyConfig());
}
