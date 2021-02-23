<script>
	export let data;
	import * as Cookies from "js-cookie";
	import { onMount } from 'svelte';

	let cookie;
	let forceNotice = false;
	$: showNotice = (forceNotice || (cookie === undefined && !data.disabled_on_privacy_page));

	onMount(() => {
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
	
	function handleConsentChange() {
		var scripts;
		
		if(cookie.consent === true){
			if(data.scripts_consent !== undefined && data.scripts_consent !== ""){
				scripts = data.scripts_consent;
			}
			embedContent();
		} else {
			if(data.scripts_no_consent !== undefined && data.scripts_no_consent !== ""){
				scripts = data.scripts_no_consent;
			}
		}
		
		if(scripts !== undefined && scripts !== ""){
			let div = document.createElement("div");
	    div.innerHTML = scripts;
	    document.body.appendChild(div);
			injectScripts(div);
		}
		
	}
	
	function embedContent() {
		let embeds = document.querySelectorAll(".cookiefox__embed.is-blocked");
		
    if(embeds.length > 0){
			embeds.forEach(function(embed, index) {
        if(embed.dataset.embed !== null){
					let parent = embed.parentNode;
          embed.classList.remove("is-blocked");
          parent.innerHTML = embed.dataset.embed;
					injectScripts(parent);
				}
			});
    }
	}
	
	function injectScripts(container) {
    var scripts = container.querySelectorAll('script');
		scripts.forEach(function(script){
			let newScript = document.createElement("script");
      newScript.text = script.innerHTML;
      let k = -1, attrs = script.attributes, attr;
      while ( ++k < attrs.length ) {                                    
      	script.setAttribute( (attr = attrs[k]).name, attr.value );
      }
      script.parentNode.replaceChild(newScript, script);
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
			
			{#if data.notice_button_decline_enabled === "on"}
			<!-- svelte-ignore a11y-positive-tabindex -->
			<button class="cookiefox__button cookiefox__button--secondary" on:click={handleDecline} tabindex="1">{data.notice_button_decline}</button>
			{/if}
			<!-- svelte-ignore a11y-positive-tabindex -->
			<button class="cookiefox__button cookiefox__button--primary" on:click={handleAccept} tabindex="1">{data.notice_button_accept}</button>
		</footer>
	</div>
</div>

<style global lang="scss">


.cookiefox{
	--cookiefox--color-primary: #000000;
	--cookiefox--color-secondary: #666666;
	--cookiefox--color-accent: #60B665;
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
	box-shadow: var(--cookiefox--box-shadow);
	
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
	box-shadow: var(--cookiefox--box-shadow);
	
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
}

.cookiefox__button--primary{
	border: 1px solid var(--cookiefox--color-accent);
	background-color: var(--cookiefox--color-accent);
	color: var(--cookiefox--background);
	padding: 0.5625em 1.375em;
	transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
	
	&:hover{
		background-color: var(--cookiefox--color-primary);
		border: 1px solid var(--cookiefox--color-primary);
		text-decoration: none;
	}
}

.cookiefox__button--secondary{
	color: var(--cookiefox--color-secondary);	
	background-color: transparent;
	padding: 0.5625em 0em;
	border: 1px solid transparent;
	background-color: transparent;
	transition: color 0.15s ease-in-out;

	&:hover{
		border: 1px solid transparent;
		color: var(--cookiefox--color-primary);
		background-color: transparent;
		text-decoration: none;
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