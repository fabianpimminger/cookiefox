<script>
	export let data;
	import * as Cookies from "js-cookie";
	import { onMount } from 'svelte';

	let cookie;
	let forceNotice = false;
	$: showNotice = (forceNotice || (cookie === undefined && !data.disabled_on_privacy_page));

	onMount(() => {
		alwaysOnScripts();
		
		if(isCrawler()){
			cookie = {consent: false};
			handleConsentChange();
			return;
		}
		
		cookie = Cookies.get(data.cookie_name);
		if(cookie !== undefined){
			cookie = JSON.parse(cookie);
			handleConsentChange();
			setCookie();
		}
		
		initAPI();
	});
	
	function handleAccept() {
		cookie = {consent: true};
		forceNotice = false;
		handleConsentChange();
		setCookie();
	}	
	
	function handleDecline() {
		cookie = {consent: false};
		forceNotice = false;
		handleConsentChange();
		setCookie();
	}	
	
	function getContainer() {
		var container = document.createElement("div");
		container.style.display = "none"; 
		return container
	}
	
	function alwaysOnScripts() {
		if(data.scripts_always !== undefined && data.scripts_always !== ""){
			let container = getContainer();
	    container.innerHTML = data.scripts_always;
			injectElements(container.children);
		}
	}
	
	function handleConsentChange() {
		var scripts = "";
		
		if(cookie.consent === true){
			if(data.scripts_consent !== undefined && data.scripts_consent !== ""){
				scripts += data.scripts_consent;
			}
			embedContent();
		} else {
			if(data.scripts_no_consent !== undefined && data.scripts_no_consent !== ""){
				scripts += data.scripts_no_consent;
			}
			removeCookies();
		}

		if(scripts !== ""){
			let container = getContainer();
			container.innerHTML = scripts;
			injectElements(container.children);
		}
	}
	
	function removeCookies() {
		if(data.cookies === undefined || data.cookies === ""){
			return;
		}
		var cookiesToRemove = data.cookies.split(",").map(cookie => cookie.trim());
		
		if(cookiesToRemove.length > 0){
			var cookies = Object.keys(Cookies.get());
			cookies = cookies.filter(element => cookiesToRemove.indexOf(element) !== -1);
			if(cookies.length > 0){
				cookies.forEach(function(name){
					Cookies.remove(name);
					Cookies.remove(name, {domain: "."+window.location.hostname});
				});
			}
		}
	}
	
	function embedContent() {
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
		
	function cloneElement(element){
		let newElement = document.createElement(element.tagName);
		newElement.innerHTML = element.innerHTML;
		let k = -1, attrs = element.attributes, attr;
		while ( ++k < attrs.length ) {
			newElement.setAttribute( (attr = attrs[k]).name, attr.value );
		}
		return newElement;
	}
	
	function injectElements(elements) {
		Array.from(elements).forEach(function(element){
			let newElement = cloneElement(element);
			document.body.appendChild(newElement);
		});
	}
	
	function initAPI() {		
		window.cookiefox.api = {};
		window.cookiefox.api.show = function(){
			forceNotice = true;
		};
	}
	
	function isCrawler() {
		if (/bot|googlebot|crawler|spider|robot|crawling/i.test(navigator.userAgent)){
			return true;
		}
		
		return false;
	}
	
	function setCookie() {
		
		let name = "cookiefox_consent";
		if(data.cookie_name !== undefined && data.cookie_name !== ""){
			name = data.cookie_name;
		}

		let options = {};
		if(data.cookie_expiration !== undefined && data.cookie_expiration !== ""){
			options.expires = parseInt(data.cookie_expiration);
		}

		if(data.cookie_domain !== undefined && data.cookie_domain !== ""){
			options.domain = parseInt(data.cookie_expiration);
		}
				
		Cookies.set(data.cookie_name, cookie, options);
	}
	
</script>

<div class="cookiefox cookiefox--notice cookiefox--{data.notice_display}" style="{showNotice ? 'display: flex;' : ''}" aria-hidden="{showNotice ? 'false' : 'true'}" data-nosnippet>
	<div class="cookiefox__inner">
		<div class="cookiefox__body">
			{#if data.notice_title}
			<h3 class="cookiefox__title">{data.notice_title}</h3>
			{/if}
			{#if data.notice_text}
			<div class="cookiefox__text">{@html data.notice_text}</div>
			{/if}
		</div>
		<footer class="cookiefox__footer">
			
			{#if data.notice_button_decline_type !== "none"}
			<!-- svelte-ignore a11y-positive-tabindex -->
			<button class="cookiefox__button cookiefox__button--secondary is-{data.notice_button_decline_type}" on:click={handleDecline} tabindex="1">{data.notice_button_decline}</button>
			{/if}
			<!-- svelte-ignore a11y-positive-tabindex -->
			<button class="cookiefox__button cookiefox__button--primary is-button" on:click={handleAccept} tabindex="1">{data.notice_button_accept}</button>
		</footer>
	</div>
</div>

<style global lang="scss">


.cookiefox{
	--cookiefox--color-primary: #000000;
	--cookiefox--color-secondary: #767676;
	--cookiefox--color-button-primary: #3d854f;
	--cookiefox--color-button-secondary: #767676;
	--cookiefox--background: #ffffff;
	--cookiefox--font-family: inherit;
	--cookiefox--font-size: 16px;
	--cookiefox--font-size-mobile: 14px;
	--cookiefox--line-height: 1.5;
	--cookiefox__notice--box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
	--cookiefox__modal--border-radius: 10px;
	--cookiefox__title--font-size: 1.3125em;
	--cookiefox__title--font-weight: bold;
	--cookiefox__text--font-size: 1em;
	--cookiefox__text--font-weight: normal;
	--cookiefox__button--font-size: 1.0625em;
	--cookiefox__button--font-weight: normal;
	--cookiefox__button--text-transform: none;
	--cookiefox__button--border-radius: 5px;
	--cookiefox__footer--background: rgba(0,0,0,0.025);
	--cookiefox__footer--color-border: rgba(0,0,0,0.05);
	--cookiefox__embed--background: #f0f0f0;
	--cookiefox__embed--border-color: #e8e8e8;
	
	box-sizing: border-box;
	font-size: var(--cookiefox--font-size);
	font-family: var(--cookiefox-font-family);
	line-height: var(--cookiefox--line-height);
	color: var(--cookiefox--color-primary);
	
	@media(max-width: 640px){
		font-size: var(--cookiefox--font-size-mobile);
	}
}

.cookiefox--notice{
	display: none;
}

.cookiefox--banner{
	position: fixed;
	z-index: 99999;
	bottom: 0px;
	left: 0px;
	width: 100%;	
	background: var(--cookiefox--background);
	padding: 28px 24px;
	box-shadow: var(--cookiefox__notice--box-shadow);
	
	@media(max-width: 640px){
		padding: 18px 14px 14px;
	}
	
	.cookiefox__inner{
		width: 100%;
		
		@media(min-width: 640px){
			display: flex;
			align-items: flex-end;
			
			.cookiefox__body{
				flex: 1;
				align-self: center;
				margin-right: 40px;
			}
		}	
	}
	
	.cookiefox__footer{
		
		@media(max-width: 640px){
			margin-top: 0.75em;
			display: flex;
			justify-content: space-between;
		}
	}
}

.cookiefox--modal{
	position: fixed;
	z-index: 99999;
	bottom: 0px;
	left: 0px;
	width: 100%;	
	height: 100%;
	align-items: center;
	justify-content: center;
	overflow: auto;
	background-color: #000;
	background-color: rgba(0,0,0,0.55);
	padding: 5px;
	box-shadow: var(--cookiefox__notice--box-shadow);
	
	.cookiefox__inner{
		max-width: 600px;
		background: var(--cookiefox--background);
		border-radius: var(--cookiefox__modal--border-radius);
		overflow: hidden;
		
		.cookiefox__footer{
			border-top: 1px solid var(--cookiefox__footer--color-border); 
			background: var(--cookiefox__footer--background);
			display: flex;
			justify-content: space-between;
			padding: 14px 24px;
			
			@media(max-width: 640px){
				padding: 8px 14px;
			}	
			
		}	
		
		.cookiefox__body{
			padding: 26px 24px 22px;
	
			@media(max-width: 640px){
				padding: 16px 14px;
			}	
		}
	}

		
}

.cookiefox__title{
	font-family: var(--cookiefox-font-family);
	font-size: var(--cookiefox__title--font-size);
	font-weight: var(--cookiefox__title--font-weight);
	margin: 0 0 0.5em;
}

.cookiefox__text{
	font-family: var(--cookiefox-font-family);
	font-size: var(--cookiefox__text--font-size);
	font-weight: var(--cookiefox__text--font-weight);
	
	a{
		text-decoration: underline;
		color: var(--cookiefox--color-primary);
		
		&:visited{
			color: inherit;
		}
		
		&:hover{
			text-decoration: none;
			color: var(--cookiefox--color-primary);
		}
	}
}

.cookiefox__footer{	
	> .cookiefox__button{
		margin-right: 1.5em;
		
		&:last-child{
			margin-right: 0px;
		}
	}
}

.cookiefox__button {
	font-family: inherit;
	text-decoration: none;
	font-size: var(--cookiefox__button--font-size);
	font-weight: var(--cookiefox__button--font-weight);
	text-transform: var(--cookiefox__button--text-transform);
	cursor: pointer;	
	border-radius: var(--cookiefox__button--border-radius);
	line-height: 1;
	box-shadow: none;
	transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
	
	&.is-button{
		padding: 0.5625em 1.375em;
	}
	
	&.is-text{
		padding: 0.5625em 0em;		
	}
	
	&:hover{
		text-decoration: none !important;
	}
}

.cookiefox__button--primary{
	&.is-button{
		border: 1px solid var(--cookiefox--color-button-primary) !important;
		background-color: var(--cookiefox--color-button-primary) !important;
		color: var(--cookiefox--background) !important;
		
		&:hover{
			background-color: var(--cookiefox--color-primary) !important;
			border: 1px solid var(--cookiefox--color-primary) !important;
		}
	}
}

.cookiefox__button--secondary{
	&.is-button{
		border: 1px solid var(--cookiefox--color-button-secondary) !important;
		background-color: var(--cookiefox--color-button-secondary) !important;
		color: var(--cookiefox--background) !important;
		
		&:hover{
			background-color: var(--cookiefox--color-primary) !important;
			border: 1px solid var(--cookiefox--color-primary) !important;
			color: var(--cookiefox--background) !important;
		}
	}	
	
	&.is-text{
		border: 1px solid transparent !important;
		background-color: transparent !important;
		color: var(--cookiefox--color-secondary) !important;
		background-color: transparent;
		transition: color 0.15s ease-in-out;
	
		&:hover{
			border: 1px solid transparent !important;
			background-color: transparent !important;
			color: var(--cookiefox--color-primary) !important;
		}
	}
}

.cookiefox__embed{
	background-color: var(--cookiefox__embed--background);
	border: 1px solid var(--cookiefox__embed--border-color);
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	text-align: center;
	
	&:first-child:last-child{
		height: 100%;
	}
	
	.wp-embed-responsive .wp-has-aspect-ratio &{
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		height: 100%;
		width: 100%;
	}
	
	.cookiefox__embed-notice{
		padding: 2em 2.5em; 
	}
	
	.cookiefox__embed-footer{
		margin-top: 1.25em;
	}
}

@media(max-width: 640px){
	.cookiefox__button--primary,
	.cookiefox__button--secondary{
		padding-top: 0.75em;
		padding-bottom: 0.75em;
	}
}

</style>