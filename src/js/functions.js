import * as Cookies from "js-cookie";

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

export const setCookie = function(cookie, data) {
	
	let name = "cookiefox_consent";
	if(data.cookie_name !== undefined && data.cookie_name !== ""){
		name = data.cookie_name;
	}

	let options = {sameSite: "strict"};
	if(data.cookie_expiration !== undefined && data.cookie_expiration !== ""){
		options.expires = parseInt(data.cookie_expiration);
	}

	if(data.cookie_domain !== undefined && data.cookie_domain !== ""){
		options.domain = parseInt(data.cookie_expiration);
	}

	Cookies.set(name, cookie, options);
}

export const getContainer = function() {
	var container = document.createElement("div");
	container.style.display = "none"; 
	return container
}
		
export const cloneElement = function(element){
	let newElement = document.createElement(element.tagName);
	newElement.innerHTML = element.innerHTML;
	let k = -1, attrs = element.attributes, attr;
	while ( ++k < attrs.length ) {
		newElement.setAttribute( (attr = attrs[k]).name, attr.value );
	}
	return newElement;
}
	
