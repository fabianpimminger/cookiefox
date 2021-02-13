<script>
	export let data;
	import * as Cookies from "js-cookie";
	import { onMount } from 'svelte';

	let cookie;
	let forceNotice = false;
	$: showNotice = (cookie === undefined || forceNotice);

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
		} else {
			if(data.scripts_no_consent !== undefined && data.scripts_no_consent !== ""){
				scripts = data.scripts_no_consent;
			}
		}
		
		if(scripts !== undefined && scripts !== ""){
			let div = document.createElement("div");
	    div.innerHTML = scripts;
	    document.body.appendChild(div);
			
	    let scriptElements = div.getElementsByTagName('script');
	    let j = -1;
	    while ( ++j < scriptElements.length ) {                                    
	        
	        var script  = document.createElement("script");
	        script.text = scriptElements[j].innerHTML;
					
	        let k = -1, attrs = scriptElements[j].attributes, attr;
	        while ( ++k < attrs.length ) {                                    
	          script.setAttribute( (attr = attrs[k]).name, attr.value );
	        }
	      
	        scriptElements[j].parentNode.replaceChild(script, scriptElements[j]);
					
	    }			
			
		}
		
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

<div class="cookiefox cookiefox--{data.notice_display}" style="{showNotice ? 'display: flex;' : ''}" aria-hidden="{showNotice ? 'true' : 'false'}">
	<div class="cookiefox__inner">
		<div class="cookiefox__body">
			<h3 class="cookiefox__title">{data.notice_title}</h3>
			<div class="cookiefox__text">{@html data.notice_text}</div>
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
	--cookiefox-box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
	--cookiefox-font-size-base: 16px;
	--cookiefox-font-size-base-mobile: 14px;
	--cookiefox-line-height: 1.5;
	--cookiefox-font-size-title: 1.3125em;
	--cookiefox-font-weight-title: bold;
	--cookiefox-font-size-text: 1em;
	--cookiefox-font-weight-text: normal;
	--cookiefox-font-size-button: 1.0625em;
	--cookiefox-font-weight-button: normal;
	--cookiefox-text-transform-button: none;
	--cookiefox-color-background-footer: rgba(0,0,0,0.025);
	--cookiefox-color-border-footer: rgba(0,0,0,0.05);
	--cookiefox-border-radius-modal: 10px;
	
	font-size: var(--cookiefox-font-size-base);
	font-family: var(--cookiefox-font-family);
	line-height: var(--cookiefox-line-height);
	color: var(--cookiefox-color-text-primary);
	display: none;
	
	@media(max-width: 640px){
		font-size: var(--cookiefox-font-size-base-mobile);
	}
}

.cookiefox--banner{
	position: fixed;
	z-index: 99999;
	bottom: 0px;
	left: 0px;
	width: 100%;	
	background: var(--cookiefox-color-background);
	padding: 28px 24px;
	box-shadow: var(--cookiefox-box-shadow);
	
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
				margin-right: 40px;
			}
		}	
	}
	
	.cookiefox__footer{
		margin-top: 0.75em;
		
		@media(max-width: 640px){
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
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: auto;
	background-color: #000;
	background-color: rgba(0,0,0,0.55);
	padding: 5px;
	box-shadow: var(--cookiefox-box-shadow);
	
	.cookiefox__inner{
		max-width: 600px;
		background: var(--cookiefox-color-background);
		border-radius: var(--cookiefox-border-radius-modal);
		overflow: hidden;
		
		.cookiefox__footer{
			border-top: 1px solid var(--cookiefox-color-border-footer); 
			background-color: var(--cookiefox-color-background-footer);
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
	font-size: var(--cookiefox-font-size-title);
	font-weight: var(--cookiefox-font-weight-title);
	margin: 0 0 0.5em;
}

.cookiefox__text{
	font-family: var(--cookiefox-font-family);
	font-size: var(--cookiefox-font-size-text);
	font-weight: var(--cookiefox-font-weight-text);
	
	a{
		text-decoration: underline;
		color: var(--cookiefox-color-text-primary);
		
		&:visited{
			color: inherit;
		}
		
		&:hover{
			text-decoration: none;
			color: var(--cookiefox-color-text-primary);
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
	font-size: var(--cookiefox-font-size-button);
	font-weight: var(--cookiefox-font-weight-button);
	text-transform: var(--cookiefox-text-transform-button);
	cursor: pointer;	
	border-radius: 5px;
	line-height: 1;
}

.cookiefox__button--primary{
	border: 1px solid var(--cookiefox-color-accent);
	background-color: var(--cookiefox-color-accent);
	color: var(--cookiefox-color-background);
	padding: 0.5625em 1.375em;
	transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
	
	&:hover{
		background-color: var(--cookiefox-color-text-primary);
		border: 1px solid var(--cookiefox-color-text-primary);
		text-decoration: none;
	}
}

.cookiefox__button--secondary{
	color: var(--cookiefox-color-text-secondary);	
	background-color: transparent;
	padding: 0.5625em 0em;
	border: 1px solid transparent;
	background-color: transparent;
	transition: color 0.15s ease-in-out;

	&:hover{
		border: 1px solid transparent;
		color: var(--cookiefox-color-text-primary);
		background-color: transparent;
		text-decoration: none;
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