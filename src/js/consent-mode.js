const TYPES = ["analytics_storage", "ad_storage", "ad_user_data", "ad_personalization"];

function deniedState() {
	const state = {};
	TYPES.forEach(function(type) {
		state[type] = "denied";
	});
	return state;
}

function grantedState() {
	const state = {};
	TYPES.forEach(function(type) {
		state[type] = "granted";
	});
	return state;
}

export function consentModeState(data, config, consent) {
	if (data && data.consent_type === "category") {
		const state = deniedState();
		const categories = (config && Array.isArray(config.categories)) ? config.categories : [];
		const accepted = (consent && typeof consent === "object") ? consent : {};

		categories.forEach(function(category) {
			if (!category || !category.slug || !accepted[category.slug]) {
				return;
			}

			const signals = Array.isArray(category.consent_mode) ? category.consent_mode : [];
			signals.forEach(function(signal) {
				if (state[signal] !== undefined) {
					state[signal] = "granted";
				}
			});
		});

		return state;
	}

	return consent === true ? grantedState() : deniedState();
}

export function applyConsentMode(data, config, consent) {
	if (!data || data.consent_mode !== true) {
		return;
	}

	const state = consentModeState(data, config, consent);
	window.dataLayer = window.dataLayer || [];
	if (typeof window.gtag !== "function") {
		window.gtag = function() {
			window.dataLayer.push(arguments);
		};
	}

	window.gtag("consent", "update", state);
	window.dataLayer.push({
		event: "cookiefox_consent_update",
		cookiefox: state
	});
}
