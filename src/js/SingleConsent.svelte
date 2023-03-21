<script>
	export let data, showNotice;

	import { cookie, forceNotice } from './stores.js';
	import * as Cookies from "js-cookie";
	import { onMount, createEventDispatcher } from 'svelte';
	import { isCrawler, setCookie, injectElements, embedContent, getContainer } from './functions.js';
	
  const dispatch = createEventDispatcher();

	onMount(() => {		
		
		if(isCrawler()){
			$cookie = {consent: false};
			handleConsentChange();
			return;
		}
		
		let cookieData = Cookies.get(data.cookie_name);
		if(cookieData !== undefined){
			$cookie = JSON.parse(cookieData);
			handleConsentChange();
			setCookie($cookie, data);
		}
		
		dispatch("ready");
		
	});
		
	function handleAccept() {
		$cookie = {consent: true};
		$forceNotice = false;
		handleConsentChange();
		setCookie($cookie, data);
		dispatch("consentChanged");		
	}	
	
	export function setConsent(consent) {
		if (typeof consent == "boolean") {
			$cookie = {consent: consent};
			handleConsentChange();
			setCookie($cookie, data);
			dispatch("consentChanged");		
		} else {
			console.warn("consent argument not boolean");
		}
	}
	
	function handleDecline() {
		$cookie = {consent: false};
		$forceNotice = false;
		handleConsentChange();
		setCookie($cookie, data);
		dispatch("consentChanged");		
	}	
	
	export const handleConsentChange = function() {
		var scripts = "";
		
		if($cookie.consent === true){
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
	<h3 class="cookiefox__title" id="cookiefox__title">{data.notice_title}</h3>
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
