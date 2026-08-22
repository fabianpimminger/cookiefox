<script>
	export let data, config, showNotice;

	import { cookie, forceNotice } from './stores.js';
	import Cookies from "js-cookie";
	import { onMount, createEventDispatcher } from 'svelte';
	import { isCrawler, setCookie, injectElements, embedContent, getContainer, getCookieOptions, parseStoredConsent, stampConsentVersion } from './functions.js';
	
  const dispatch = createEventDispatcher();

	onMount(() => {		
		
		if(isCrawler()){
			$cookie = {consent: false};
			handleConsentChange();
			return;
		}
		
		$cookie = parseStoredConsent(Cookies.get(data.cookie_name), data);
		if($cookie !== undefined){
			handleConsentChange();
			setCookie($cookie, data);
		}
		
		dispatch("ready");
		
	});
		
	function handleAccept() {
		$cookie = stampConsentVersion({consent: true}, data);
		$forceNotice = false;
		handleConsentChange();
		setCookie($cookie, data);
		dispatch("consentChanged");		
	}	
	
	export function setConsent(consent) {
		if (typeof consent == "boolean") {
			$cookie = stampConsentVersion({consent: consent}, data);
			handleConsentChange();
			setCookie($cookie, data);
			dispatch("consentChanged");		
		} else {
			console.warn("consent argument not boolean");
		}
	}
	
	function handleDecline() {
		$cookie = stampConsentVersion({consent: false}, data);
		$forceNotice = false;
		handleConsentChange();
		setCookie($cookie, data);
		dispatch("consentChanged");		
	}	
	
	export const handleConsentChange = function() {
		var scripts = "";
		
		if($cookie.consent === true){
			if(config && config.scripts_consent !== undefined && config.scripts_consent !== ""){
				scripts += config.scripts_consent;
			}
			embedContent();
		} else {
			if(config && config.scripts_no_consent !== undefined && config.scripts_no_consent !== ""){
				scripts += config.scripts_no_consent;
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
		const cookieList = (config && config.cookies) || data.cookies;
		if(cookieList === undefined || cookieList === ""){
			return;
		}
		var cookiesToRemove = cookieList.split(",").map(cookie => cookie.trim());
		
		if(cookiesToRemove.length > 0){
			var cookies = Object.keys(Cookies.get());
			cookies = cookies.filter(element => cookiesToRemove.indexOf(element) !== -1);
			if(cookies.length > 0){
				let options = getCookieOptions(data);
				let removeOptions = {path: options.path || "/"};
				if(options.domain){
					removeOptions.domain = options.domain;
				}
				cookies.forEach(function(name){
					Cookies.remove(name, removeOptions);
					Cookies.remove(name, {path: "/"});
					Cookies.remove(name, {path: "/", domain: "."+window.location.hostname});
				});
			}
		}
	}
	
	function handleEscapeKey(event) {
		if (showNotice && event.key === 'Escape') {
			event.preventDefault();
			event.stopPropagation();
			handleDecline();
		}
	}
</script>

<svelte:window on:keydown={handleEscapeKey}/>

<div class="cookiefox__body">
	{#if data.notice_title}
	<div class="cookiefox__title" id="cookiefox__title" role="heading" aria-level="2">{data.notice_title}</div>
	{/if}
	{#if data.notice_text}
	<div class="cookiefox__text">{@html data.notice_text}</div>
	{/if}
</div>
<footer class="cookiefox__footer">
	{#if data.notice_button_decline_type !== "none"}
	<button class="cookiefox__button cookiefox__button--secondary is-{data.notice_button_decline_type}" on:click={handleDecline} >{data.notice_button_decline}</button>
	{/if}
	<div class="cookiefox__spacer"></div>
	<button class="cookiefox__button cookiefox__button--primary is-button" on:click={handleAccept}>{data.notice_button_accept}</button>
</footer>
