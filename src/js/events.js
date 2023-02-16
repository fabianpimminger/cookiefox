export const trigger = function(name, data = {}) {	
	const event = new CustomEvent(name, { detail: data });
	document.dispatchEvent(event);
}

export const triggerInit = function() {
	trigger("cookiefox:init");
}

export const triggerConsentChanged = function(consent) {
	trigger("cookiefox:consent-changed", consent);
}