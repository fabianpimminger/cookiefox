import Cookies from "js-cookie";

export const embedContent = function() {
	let embeds = document.querySelectorAll(".cookiefox__embed.is-blocked");
	
	if(embeds.length > 0){
		embeds.forEach(function(embed, index) {
			if(embed.dataset.embed !== null){
				embed.classList.remove("is-blocked");
				let parent = embed.parentNode;
				let container = getContainer();
				container.innerHTML = embed.dataset.embed;
				let elements = container.children;
				
				Array.from(elements).forEach(element => {
					
					var newElement = cloneElement(element);

					if(!parent.classList.contains("wp-block-embed__wrapper") && element.tagName === "IFRAME"){
						var pElement = document.createElement("p");
						pElement.appendChild(newElement);
						newElement = pElement;
					}
					
					document.body.appendChild(newElement);
					parent.insertBefore(newElement, embed);
				});
	
				let nestedScripts = container.querySelectorAll("* script");
				
				if(nestedScripts.length > 0){
					nestedScripts.forEach(function(script) {
						var newElement = cloneElement(script);
						parent.insertBefore(newElement, embed);
					});
				}
				
				parent.removeChild(embed);
				
			}
		});
		
		window.dispatchEvent(new Event('resize'));
	}
}

export const injectElements = function(elements) {
	Array.from(elements).forEach(function(element){
		let newElement = cloneElement(element);
		document.body.appendChild(newElement);
	});
}

export const isCrawler = function() {
	if (/bot|googlebot|crawler|spider|robot|crawling/i.test(navigator.userAgent)){
		return true;
	}
	
	return false;
}

export const getCookieOptions = function(data) {
	let options = {sameSite: "strict", path: "/"};

	if(data.cookie_expiration !== undefined && data.cookie_expiration !== ""){
		options.expires = parseInt(data.cookie_expiration, 10);
	}

	if(data.cookie_domain !== undefined && data.cookie_domain !== ""){
		options.domain = data.cookie_domain;
	}

	if(typeof location !== "undefined" && location.protocol === "https:"){
		options.secure = true;
	}

	return options;
}

export const consentVersion = function(data) {
	const version = parseInt(data && data.consent_version, 10);
	return Number.isFinite(version) ? version : 0;
}

export const parseStoredConsent = function(raw, data) {
	if (raw === undefined || raw === null || raw === "") {
		return undefined;
	}

	try {
		const parsed = JSON.parse(raw);
		if (!parsed || typeof parsed !== "object") {
			return undefined;
		}

		const stored = parsed.v != null ? parseInt(parsed.v, 10) : 0;
		if ((Number.isFinite(stored) ? stored : 0) !== consentVersion(data)) {
			return undefined;
		}

		return parsed;
	} catch (e) {
		return undefined;
	}
}

export const stampConsentVersion = function(cookie, data) {
	return Object.assign({}, cookie, {
		v: consentVersion(data)
	});
}

export const setCookie = function(cookie, data) {
	
	let name = "cookiefox_consent";
	if(data.cookie_name !== undefined && data.cookie_name !== ""){
		name = data.cookie_name;
	}

	let value = cookie;
	if (typeof cookie !== "string") {
		value = JSON.stringify(Object.assign({}, cookie, {
			ts: Math.floor(Date.now() / 1000)
		}));
	}

	Cookies.set(name, value, getCookieOptions(data));
}

export const getContainer = function() {
	var container = document.createElement("div");
	container.style.display = "none"; 
	return container
}
		
const cloneElement = function(element){
	let newElement = document.createElement(element.tagName);
	newElement.innerHTML = element.innerHTML;
	let k = -1, attrs = element.attributes, attr;
	while ( ++k < attrs.length ) {
		newElement.setAttribute( (attr = attrs[k]).name, attr.value );
	}
	return newElement;
}
	
