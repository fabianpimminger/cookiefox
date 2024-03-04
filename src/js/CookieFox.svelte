<script>
	export let data;
	import { cookie, forceNotice } from './stores.js';
	import { triggerInit, triggerConsentChanged } from './events.js';
	import { onMount } from 'svelte';
	import { injectElements, getContainer } from './functions.js';
	import SingleConsent from './SingleConsent.svelte';
	import CategoryConsent from './CategoryConsent.svelte';
	import focusTrap from './focus-trap.js';
	
	let consentComponent;
	let forceDelay = false;
	let handleConsentChange;
	let setConsent;
	$: showNotice = ($forceNotice || ($cookie === undefined && !data.disabled_on_privacy_page && !forceDelay));

	onMount(() => {
		alwaysOnScripts();
				
		if(data.notice_delay && data.notice_delay > 0){
			forceDelay = true;
			
			setTimeout(() => {
  			forceDelay = false;
			}, parseInt(data.notice_delay) * 1000)
		}
		
		if(data.consent_type === "category"){
			consentComponent = CategoryConsent;
		} else {
			consentComponent = SingleConsent;
		}
		
		initAPI();

	});

	function alwaysOnScripts() {
		if(data.scripts_always !== undefined && data.scripts_always !== ""){
			let container = getContainer();
	    container.innerHTML = data.scripts_always;
			injectElements(container.children);
		}
	}
			
	function initAPI() {		
		window.cookiefox.api = {};
		window.cookiefox.api.show = function(){
			$forceNotice = true;
		};
		
		window.cookiefox.api.updateConsent = function(){
			handleConsentChange();
		}
		
		window.cookiefox.api.getConsent = function(){
			if($cookie && $cookie.consent){
				return $cookie.consent
			}
			
			return null;
		}
		
		window.cookiefox.api.setConsent = function(consent, category){
			if(data.consent_type == "category") {
				setConsent(consent, category);
			} else {
				setConsent(consent);
			}
		}
		
	}	
	
	function consentInitHandler() {
		triggerInit();
	}

	function consentChangedHandler() {
		if($cookie && $cookie.consent){
			triggerConsentChanged($cookie.consent);
		} else {
			triggerConsentChanged();
		}
	}
	
</script>


<div class="cookiefox cookiefox--notice cookiefox--{data.notice_display} cookiefox--{data.consent_type}" style="{showNotice ? 'display: flex;' : ''}" role="dialog"
        aria-modal="true" aria-labelledby="cookiefox__title" aria-hidden="{showNotice ? 'false' : 'true'}" data-nosnippet use:focusTrap={showNotice}>
	<div class="cookiefox__inner">
		<svelte:component this={consentComponent} data={data} bind:setConsent={setConsent} showNotice={showNotice} on:ready={consentInitHandler} on:consentChanged={consentChangedHandler} bind:handleConsentChange={handleConsentChange} />
	</div>
</div>

<style global lang="scss">
.cookiefox{
	--cookiefox--color-text-primary: #000000;
	--cookiefox--color-text-secondary: #767676;
	--cookiefox--color-button-primary: #3d854f;
	--cookiefox--color-button-secondary: #767676;
	--cookiefox--background: #ffffff;
	--cookiefox--font-family: inherit;
	--cookiefox--font-size: 16px;
	--cookiefox--font-size-mobile: 14px;
	--cookiefox--line-height: 1.5;
	--cookiefox__notice--box-shadow: 0px 5px 15px rgba(0,0,0,0.2);
	--cookiefox__modal--border-radius: 6px;
	--cookiefox__title--font-size: 1.25em;
	--cookiefox__title--font-weight: bold;
	--cookiefox__text--font-size: 1em;
	--cookiefox__text--font-weight: normal;
	--cookiefox__text--text-align: left;
	--cookiefox__button--font-size: 1em;
	--cookiefox__button--font-weight: normal;
	--cookiefox__button--text-transform: none;
	--cookiefox__button--border-radius: 5px;
	--cookiefox__button--letter-spacing: 0px;
	--cookiefox__footer--background: rgba(0,0,0,0.025);
	--cookiefox__footer--color-border: rgba(0,0,0,0.1);
	--cookiefox__embed--background: #f0f0f0;
	--cookiefox__embed--border-color: #e8e8e8;
	
	box-sizing: border-box;
	font-size: var(--cookiefox--font-size);
	font-family: var(--cookiefox-font-family);
	line-height: var(--cookiefox--line-height);
	color: var(--cookiefox--color-text-primary);
	
	*, *::before, *::after {
    box-sizing: inherit;
  }
	
	input[type=checkbox],
	a,
	button,
	input,
	summary{
		&:focus{
			outline-offset: 2px;
			outline: 1px dotted var(--cookiefox--color-text-primary);
		}
		
		&:focus:not(:focus-visible) { 
			outline: none 
		}
	}

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
	max-height: 100%;
	overflow: auto;
	overscroll-behavior: contain;
	background: var(--cookiefox--background);
	box-shadow: var(--cookiefox__notice--box-shadow);
		
	.cookiefox__inner{
		width: 100%;
		
		@media(min-width: 1024px){			
			.cookiefox__body{
				flex: 1;
				align-self: center;
			}
		}	
	}

	
	.cookiefox__categories, .cookiefox__meta{
		justify-content: flex-start;

		@media(max-width: 1024px){
			justify-content: center;	
		}
	}
	
	.cookiefox__body{
		padding: 28px 24px 7px;
		
		@media(max-width: 1024px){
			padding: 18px 14px 7px;
		}
	}
	
	.cookiefox__footer{
		padding: 7px 24px 28px;
		display: flex;
		justify-content: flex-end;
			
		@media(max-width: 1024px){
			padding: 7px 14px 18px;
		}
	}
	
	&.cookiefox--simple{
		.cookiefox__spacer{
			display: none;
		}
	}
	
	.cookiefox__spacer{
		display: block;
		flex: 1;
	}
	
	.cookiefox__title{
		@media(max-width: 1024px){
			text-align: center;	
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
	padding: 10px;
	
	.cookiefox__inner{
    overflow: auto;
		overscroll-behavior: contain;
    max-height: 100%;
		max-width: 680px;
		background: var(--cookiefox--background);
		border-radius: var(--cookiefox__modal--border-radius);
		box-shadow: var(--cookiefox__notice--box-shadow);
		scroll-padding-bottom: 65px;
		
		.cookiefox__footer{
			display: flex;
			justify-content: flex-start;
			padding: 14px 24px;
			position: sticky;
			bottom: 0px;
			left:0px;
			width: 100%;
			background: var(--cookiefox--background);
			
			@media(max-width: 640px){
				padding: 8px 14px;
			}	
			
			&:after{
    		content: "";
    		position: absolute;
    		left: 0px;
    		top: 0px;
    		width: 100%;
    		height: 100%;
				border-top: 1px solid var(--cookiefox__footer--color-border); 
    		background: var(--cookiefox__footer--background);
    		z-index: -1;
			}
			
		}	
		
		.cookiefox__spacer{
			flex: 1;
			height: 1px;
		}
		
		.cookiefox__body{
			padding: 26px 24px 22px;
	
			@media(max-width: 640px){
				padding: 16px 14px;
			}	
		}
	}
	
	.cookiefox__title{
		text-align: center;
		margin-bottom: 0.75em;
	}
	
	.cookiefox__categories, .cookiefox__meta{
		justify-content: center;
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
	text-align: var(--cookiefox__text--text-align);
	
	a{
		text-decoration: underline;
		color: var(--cookiefox--color-text-primary);
		
		&:visited{
			color: inherit;
		}
		
		&:hover{
			text-decoration: none;
			color: var(--cookiefox--color-text-primary);
		}
	}
}

.cookiefox__categories{
  margin-top: 2.25em;
	margin-left: -0.75em;
	margin-right: -0.75em;
	margin-bottom: -1.25em;
  display: flex;
  align-items: center;
	flex-wrap: wrap;
}

.cookiefox__category{
	margin: 0 0.75em 1.25em;
}

.cookiefox__category-label{
	display: flex;
	align-items: center;
	font-size: var(--cookiefox__text--font-size);
	line-height: 1;
	color: var(--cookiefox--color-text-primary);
	font-weight: bold;
	text-transform: none;
	margin: 0px;
	cursor: pointer;
	
	&.is-disabled{
		cursor: not-allowed;
	}
}

input[type=checkbox].cookiefox__category-checkbox{
	-webkit-appearance: none;
	position: relative;
	width: 1.325em;
	height: 1.325em;
	display: block;
	margin: 0px 0.45em 0 0;
	padding: 0px;
	border: 2px solid currentColor;
	background: var(--cookiefox--background);
	transition: border-color 0.15s ease-in-out;
	cursor: inherit;
	border-radius: 2px;
	box-shadow: none;
	top: 0px;
	left: 0px;
	
	&:after{
		content: "";
    opacity: 0;
    display: block;
    left: 0.355em;
    top: 0.085em;
    position: absolute;
    width: 0.4em;
    height: 0.7em;
    border: 2.25px solid var(--cookiefox--color-button-primary);
    border-top: 0;
    border-left: 0;
    transform: rotate(32deg);
		transition: opacity 0.1s ease-in-out;
	}
	
	&:before{
		content: none;
	}
	
	&:checked{
		border-color: var(--cookiefox--color-button-primary);
		
		&:after{
			opacity: 1;
		}
	}
	
	&:disabled{
		opacity: 0.65;
	}
}

.cookiefox__meta{
  margin-top: 1em;
  display: flex;
  justify-content: center;
  align-items: center;
}

.cookiefox__spinner{
	text-align: center;
	margin: 1.5em 0;
	svg{
		height: auto;
		width: 40px;
		stroke: currentColor;
	}
}

.cookiefox__details{
	margin: 1.5em 0 0;
}

.cookiefox__details-category{
	margin: 0 0 1em;
	border: 1px solid var(--cookiefox__footer--color-border); 
	background: var(--cookiefox__footer--background);
  padding: 1em;
  border-radius: 4px;
	
	&:last-child{
		margin-bottom: 0px;
	}
}

.cookiefox__details-description{
	font-size: 0.875em;
	margin: 1em 0 0;
}

.cookiefox__details-more{
	font-size: 0.875em;
	margin: 1em 0 0;
}

.cookiefox__details-summary{
	cursor: pointer;
	position: relative;
	display: flex;
	align-items: center;
	
	&::-webkit-details-marker {
	  display: none;
	}

	&:before{
		display: block;
		content: "";
		width: 0.375em;
		height: 0.375em;
    border: 2px solid currentColor;
    border-top: 0;
    border-left: 0;
    transform: rotate(315deg);
		margin: 0 0.5em 0 0;
	}
}

.cookiefox__details-more[open]{
	.cookiefox__details-summary:before{
    transform: rotate(45deg);
	}
}

.cookiefox__details-cookies{
	margin: 0.75em 0 0;
	overflow:scroll;
}

.cookiefox__table{
  width: 100%;
  border-collapse: collapse;
	margin: 0 0 1em;
	
	&:last-child{
		margin-bottom: 0px;
	}	
}

.cookiefox__th, .cookiefox__td{
	padding: 0.35em 0.75em;
	border: 1px solid currentColor;
	text-align: left;
}

.cookiefox__th{
	font-weight: bold;
	font-size: 1em;
	width: 115px;
}

.cookiefox__td{
	font-weight: normal;
	font-size: 1em;
}

.cookiefox__footer{	
	> .cookiefox__button{
		margin-right: 1.5em;
		
		&:last-child{
			margin-right: 0px;
		}
	}
}

.cookiefox__text{
	ul, ol, p, table, h1, h2, h3, h4, h5, h6{
		margin: 0 0 1.25em;
		
		&:last-child{
			margin-bottom: 0px;
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
	letter-spacing: var(--cookiefox__button--letter-spacing);
	line-height: 1;
	box-shadow: none;
	transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
	
	&.is-button{
		padding: 0.6em 1.375em;
	}
	
	&.is-text{
		padding: 0.6em 0em;		
	}
	
	&.is-medium{
		font-size: 0.875em;	
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
			background-color: var(--cookiefox--color-text-primary) !important;
			border: 1px solid var(--cookiefox--color-text-primary) !important;
		}
	}
}

.cookiefox__button--secondary{
	&.is-button{
		border: 1px solid var(--cookiefox--color-button-secondary) !important;
		background-color: var(--cookiefox--color-button-secondary) !important;
		color: var(--cookiefox--background) !important;
		
		&:hover{
			background-color: var(--cookiefox--color-text-primary) !important;
			border: 1px solid var(--cookiefox--color-text-primary) !important;
			color: var(--cookiefox--background) !important;
		}
	}	
	
	&.is-text{
		border: 1px solid transparent !important;
		background-color: transparent !important;
		color: var(--cookiefox--color-text-secondary) !important;
		background-color: transparent;
		transition: color 0.15s ease-in-out;
	
		&:hover{
			border: 1px solid transparent !important;
			background-color: transparent !important;
			color: var(--cookiefox--color-text-primary) !important;
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