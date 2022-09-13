<script>
	export let data, showNotice;
	
	import { cookie, forceNotice } from './stores.js';
	import * as Cookies from "js-cookie";
	import { onMount, createEventDispatcher } from 'svelte';
	import { isCrawler, setCookie, injectElements, embedContent, getContainer } from './functions.js';
	
  const dispatch = createEventDispatcher();
	let config;
	let loaded = false;
	let consents = {};
	let processedConsents = [];
	let firstRun = false;
	let view = "overview";
	
	onMount(() => {				

		if(isCrawler()){
			$cookie = {
				consent: {
					categories: {}
				}
			};
			
			handleConsentChange();
			return;
		}
		
		let cookieData = Cookies.get(data.cookie_name);
		if(cookieData !== undefined){
			$cookie = JSON.parse(cookieData);
			if($cookie.consent !== undefined && $cookie.consent.categories !== undefined){
				consents = $cookie.consent.categories;
			}
			setCookie($cookie, data);
		} else {
			firstRun = true;
		}
		
		loadConfig();
				
		dispatch("ready");
	});
	
	function loadConfig() {
	  fetch(data.api_base + "cookiefox/v1/cookies?lang=" + data.lang)
	  .then(response => response.json())
	  .then(data => {
	    config = data;
			
			config.categories.forEach(category => {
				if(category.always_on){
					consents[category.slug] = true;
				} else if(consents[category.slug] === undefined){
					consents[category.slug] = false;
				}
			});
			
			handleAlwaysOnCookies();

			if(!firstRun){
				handleConsentChange();
				firstRun = false;
			}
			
			loaded = true;
	  })
		.catch(error => {
	    // console.log(error);
			loaded = true;
	    return [];
	  });
	}
	
	function setupCookie() {
		if($cookie === undefined){
			$cookie = {
				consent: {
					categories: {}
				}
			};
		}
		
		$cookie.consent.categories = consents;
	}
	
	export const handleConsentChange = function() {
		let scripts = "";

		
		config.categories.forEach(category => {
						
			if(processedConsents[category.slug] === undefined || processedConsents[category.slug] !== consents[category.slug]){
				let consent = category.slug;
				
				// console.log("category processing", consents[category.slug], consent); 
				
				category.cookies.forEach(cookie => {
					// console.log("cookie processing", cookie.slug); 
					if(consents[category.slug]){
						if(cookie.scripts_consent !== undefined && cookie.scripts_consent !== ""){
							// console.log("cookie script", cookie.slug, "consent"); 
							scripts += cookie.scripts_consent;
						}
					} else {
						if(cookie.scripts_no_consent !== undefined && cookie.scripts_no_consent !== ""){
							// console.log("cookie script", cookie.slug, "no consent"); 
							scripts += cookie.scripts_no_consent;
						}
					}
				});
				
				if(consents[category.slug] && category.integrations.unblock_embeds !== undefined){
					// console.log("category integration", "unblock media"); 
					embedContent();
				}
				
				processedConsents[category.slug] = consents[category.slug];
			} else {
				// console.log("category already processed for consent", category.slug, consents[category.slug]); 
			}
		});

		if(scripts !== ""){
			let container = getContainer();
			container.innerHTML = scripts;
			injectElements(container.children);
		}
	}
	
	function handleAlwaysOnCookies() {
		let scripts = "";
		
		config.categories.forEach(category => {	
			category.cookies.forEach(cookie => {
				if(cookie.scripts_always !== undefined && cookie.scripts_always !== ""){
					// console.log("cookie script", cookie.slug, "always"); 
					scripts += cookie.scripts_always;
				}
			});
		});

		if(scripts !== ""){
			let container = getContainer();
			container.innerHTML = scripts;
			injectElements(container.children);
		}		
	}
		
	function handleAccept() {
		config.categories.forEach(category => {
			consents[category.slug] = true;
		});
		
		handleSave();
	}	
	
	function handleDecline() {
		config.categories.forEach(category => {
			if(!category.always_on){
				consents[category.slug] = false;
			}
		});
		
		console.log(config.categories);
		
		handleSave();
	}	

	function handleSave() {
		$forceNotice = false;
		handleConsentChange();
		setupCookie();
		setCookie($cookie, data);
	}	
	
	function handleManage() {
		if(view == "overview"){
			view = "details";
		} else {
			view = "overview";
		}
	}	

	function handleEscapeKey(event) {
		if (showNotice && event.key === 'Escape') {
			event.preventDefault();
			event.stopPropagation();
			handleSave();
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
	{#if loaded && view == "overview" && config.categories}
	<div class="cookiefox__categories">
		{#each config.categories as category}
		<div class="cookiefox__category">
			<label class="cookiefox__category-label" class:is-disabled="{category.always_on}">
				<input class="cookiefox__category-checkbox" type="checkbox" disabled={category.always_on} name="consents" value={category.slug} bind:checked={consents[category.slug]}>
				{category.name}
			</label>
		</div>
		{/each}
	</div>
	<div class="cookiefox__meta">
		<button class="cookiefox__button cookiefox__button--secondary is-text is-medium" on:click={handleManage}>{data.notice_button_manage}</button>
	</div>
	{/if}
	{#if loaded && view == "details" && config.categories}
	<div class="cookiefox__details">
		{#each config.categories as category}
		<div class="cookiefox__details-category">
			<label class="cookiefox__category-label" class:is-disabled="{category.always_on}">
				<input class="cookiefox__category-checkbox" type="checkbox" disabled={category.always_on} name="consents[{category.slug}]" value={category.slug} bind:checked={consents[category.slug]}>
				{category.name}
			</label>
			<div class="cookiefox__details-description">
				{category.description}
			</div>
			{#if category.cookies && category.cookies.length > 0}
			<details class="cookiefox__details-more">
				<summary class="cookiefox__details-summary">Cookie information</summary>
				<div class="cookiefox__details-cookies">
					{#each category.cookies as cookie}
					<table class="cookiefox__table">
						{#if cookie.name}
						<tr>
							<th class="cookiefox__th">{data.notice_text_name}</th>
							<td class="cookiefox__td">{cookie.name}</td>
						</tr>
						{/if}
						{#if cookie.vendor}
						<tr>
							<th class="cookiefox__th">{data.notice_text_vendor}</th>
							<td class="cookiefox__td">{cookie.vendor}</td>
						</tr>
						{/if}
						{#if cookie.description}
						<tr>
							<th class="cookiefox__th">{data.notice_text_purpose}</th>
							<td class="cookiefox__td">{cookie.description}</td>
						</tr>
						{/if}
						{#if cookie.privacy_policy}
						<tr>
							<th class="cookiefox__th">{data.notice_text_privacy_policy}</th>
							<td class="cookiefox__td">{cookie.privacy_policy}</td>
						</tr>
						{/if}
						{#if cookie.cookies}
						<tr>
							<th class="cookiefox__th">{data.notice_text_cookies}</th>
							<td class="cookiefox__td">{cookie.cookies}</td>
						</tr>
						{/if}
					</table>
					{/each}
				</div>
			</details>
			{/if}
		</div>
		{/each}
	</div>
	<div class="cookiefox__meta">
		<button class="cookiefox__button cookiefox__button--secondary is-text is-medium" on:click={handleManage}>{data.notice_button_back}</button>
	</div>
	{/if}
	{#if !loaded}
	<div class="cookiefox__spinner">
		<svg viewBox="0 0 64 64"><g><circle stroke-width="4" stroke-dasharray="128" stroke-dashoffset="82" r="26" cx="32" cy="32" fill="none"><animateTransform values="0,32,32;360,32,32" attributeName="transform" type="rotate" repeatCount="indefinite" dur="750ms"></animateTransform></circle></g></svg>	</div>	
	{/if}
</div>
{#if loaded}
<footer class="cookiefox__footer">
	{#if data.notice_button_decline_type !== "none"}
	<button class="cookiefox__button cookiefox__button--secondary is-{data.notice_button_decline_type}" on:click={handleDecline} >{data.notice_button_decline}</button>
	{/if}
	<button class="cookiefox__button cookiefox__button--secondary is-button" on:click={handleSave}>{data.notice_button_save}</button>
	<div class="cookiefox__spacer"></div>
	<button class="cookiefox__button cookiefox__button--primary is-button" on:click={handleAccept}>{data.notice_button_accept}</button>
</footer>
{/if}
