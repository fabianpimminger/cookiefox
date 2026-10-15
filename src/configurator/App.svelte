<script>
	import { onMount } from 'svelte';
	import { defaultState, applyLanguage, textLanguage, LANGUAGES, buildRuntime, headSnippet, bodySnippet, parseImport, encodeShare, decodeShare, CSS_VARIABLES } from './snippet.js';
	import Categories from './Categories.svelte';
	import Preview from './Preview.svelte';

	const STEPS = ["Start", "Basics", "Texts", "Scripts & cookies", "Appearance", "Get the code"];

	let state = defaultState();
	let step = 0;
	let importText = "";
	let importError = "";
	let importNotice = "";
	let copied = "";
	let hashTimer;
	let files = [
		{ name: "main.js", folder: "js", url: new URL("../assets/frontend/js/main.js", document.baseURI).href, size: null, missing: false },
		{ name: "main.css", folder: "css", url: new URL("../assets/frontend/css/main.css", document.baseURI).href, size: null, missing: false }
	];

	$: runtime = buildRuntime(state);
	$: head = headSnippet(state, runtime);
	$: body = bodySnippet(state, runtime);
	$: isCategory = state.data.consent_type === "category";
	$: language = textLanguage(state.data);
	$: syncHash(state);

	onMount(() => {
		files.forEach((file, i) => {
			fetch(file.url, { method: "HEAD", cache: "no-store" })
				.then(response => {
					files[i].missing = !response.ok;
					files[i].size = parseInt(response.headers.get("content-length"), 10) || null;
				})
				.catch(() => files[i].missing = true);
		});

		const match = location.hash.match(/^#config=(.+)$/);
		if (match) {
			try {
				state = decodeShare(match[1]);
				step = 1;
			} catch (e) {
				importError = "The configuration in the link could not be read.";
			}
		}
	});

	// Keeps the URL in sync so a reload never loses work and the address bar is the share link.
	function syncHash(state) {
		clearTimeout(hashTimer);
		hashTimer = setTimeout(() => {
			history.replaceState(null, "", "#config=" + encodeShare(state));
		}, 400);
	}

	function runImport() {
		importError = "";
		importNotice = "";
		try {
			state = parseImport(importText);
			importText = "";
			importNotice = "Configuration loaded. Walk through the steps to review and edit it.";
		} catch (e) {
			importError = e.message;
		}
	}

	function startOver() {
		if (confirm("Discard the current configuration and start with the defaults?")) {
			const lang = language;
			state = defaultState();
			state.data = applyLanguage(state.data, lang);
			importNotice = "";
			step = 1;
		}
	}

	function setLanguage(lang) {
		state.data = applyLanguage(state.data, lang);
	}

	async function copy(text, label) {
		try {
			await navigator.clipboard.writeText(text);
			copied = label;
			setTimeout(() => copied = "", 1500);
		} catch (e) {
			copied = "";
		}
	}

	function download() {
		const html = "<!-- In <head> -->\n" + head + "\n\n<!-- Before </body> -->\n" + body + "\n";
		const link = document.createElement("a");
		link.href = URL.createObjectURL(new Blob([html], { type: "text/html" }));
		link.download = "cookiefox-snippet.html";
		link.click();
		URL.revokeObjectURL(link.href);
	}

	function go(index) {
		step = Math.max(0, Math.min(STEPS.length - 1, index));
		window.scrollTo(0, 0);
	}
</script>

<div class="layout">
<div class="left">
<header class="top">
	<h1>CookieFox configurator</h1>
	<p>Set up the cookie notice for any website, then copy the generated code into your pages.</p>
</header>

<nav class="steps">
	{#each STEPS as label, i}
		<button type="button" class:active={i === step} on:click={() => go(i)}><span>{i + 1}</span> {label}</button>
	{/each}
</nav>

	<main class="form">
		{#if step === 0}
			<section>
				<h2>Start</h2>
				<p>Start with the defaults, or load an existing configuration to edit it.</p>
				<fieldset class="choice">
					<legend>Default texts</legend>
					<div class="segmented">
						{#each LANGUAGES as lang}
							<label class="check"><input type="radio" name="language" checked={language === lang.code} on:change={() => setLanguage(lang.code)}> {lang.label}</label>
						{/each}
					</div>
				</fieldset>
				<button type="button" class="primary" on:click={() => go(1)}>Start with defaults</button>

				<h3>Load an existing configuration</h3>
				<p class="muted">Paste a snippet from this configurator, the page source of a WordPress site running CookieFox (the <code>var cookiefox = …</code> line), or JSON with <code>data</code> and <code>config</code>.</p>
				<textarea class="code" rows="8" spellcheck="false" bind:value={importText} placeholder={'var cookiefox = {"data": {…}, "config": {…}};'}></textarea>
				{#if importError}<p class="error">{importError}</p>{/if}
				{#if importNotice}<p class="notice">{importNotice}</p>{/if}
				<div class="actions">
					<button type="button" on:click={runImport} disabled={!importText.trim()}>Load configuration</button>
					<button type="button" class="link" on:click={startOver}>Reset to defaults</button>
				</div>
			</section>
		{:else if step === 1}
			<section>
				<h2>Basics</h2>
				<fieldset class="choice">
					<legend>Consent type</legend>
					<label class="check"><input type="radio" bind:group={state.data.consent_type} value="simple"> <span><strong>Simple</strong>: one accept/decline choice for everything</span></label>
					<label class="check"><input type="radio" bind:group={state.data.consent_type} value="category"> <span><strong>Category</strong>: visitors choose per category (statistics, marketing, …)</span></label>
				</fieldset>
				<fieldset class="choice">
					<legend>Display</legend>
					<label class="check"><input type="radio" bind:group={state.data.notice_display} value="banner"> Banner at the bottom of the page</label>
					<label class="check"><input type="radio" bind:group={state.data.notice_display} value="modal"> Modal in the center of the page</label>
				</fieldset>
				<div class="grid">
					<label>Delay before showing the notice <small>seconds</small>
						<input type="number" min="0" bind:value={state.data.notice_delay}>
					</label>
					<label>Consent cookie name
						<input type="text" bind:value={state.data.cookie_name}>
					</label>
					<label>Consent cookie lifetime <small>days</small>
						<input type="number" min="1" bind:value={state.data.cookie_expiration}>
					</label>
					<label>Cookie domain <small>optional, e.g. .example.com</small>
						<input type="text" bind:value={state.data.cookie_domain}>
					</label>
					<label>Consent version <small>raise it to ask everyone again</small>
						<input type="number" min="0" bind:value={state.data.consent_version}>
					</label>
				</div>
			</section>
		{:else if step === 2}
			<section>
				<h2>Texts</h2>
				<p class="muted">Switching the language replaces only texts you haven't changed.</p>
				<fieldset class="choice">
					<legend>Default texts</legend>
					<div class="segmented">
						{#each LANGUAGES as lang}
							<label class="check"><input type="radio" name="language-texts" checked={language === lang.code} on:change={() => setLanguage(lang.code)}> {lang.label}</label>
						{/each}
					</div>
				</fieldset>
				<label>Title
					<input type="text" bind:value={state.data.notice_title}>
				</label>
				<label>Text <small>HTML allowed, e.g. a link to your privacy policy</small>
					<textarea rows="4" bind:value={state.data.notice_text}></textarea>
				</label>
				<div class="grid">
					<label>Accept button
						<input type="text" bind:value={state.data.notice_button_accept}>
					</label>
					<label>Decline style
						<select bind:value={state.data.notice_button_decline_type}>
							<option value="button">Button</option>
							<option value="text">Text link</option>
							<option value="none">None</option>
						</select>
					</label>
					{#if state.data.notice_button_decline_type !== "none"}
						<label>Decline button
							<input type="text" bind:value={state.data.notice_button_decline}>
						</label>
					{/if}
					{#if isCategory}
						<label>Save button
							<input type="text" bind:value={state.data.notice_button_save}>
						</label>
						<label>Manage button
							<input type="text" bind:value={state.data.notice_button_manage}>
						</label>
						<label>Back button
							<input type="text" bind:value={state.data.notice_button_back}>
						</label>
						<label>Cookie information button
							<input type="text" bind:value={state.data.notice_button_cookie_information}>
						</label>
					{/if}
				</div>
				{#if isCategory}
					<h3>Cookie detail labels</h3>
					<div class="grid">
						<label>Name <input type="text" bind:value={state.data.notice_text_name}></label>
						<label>Vendor <input type="text" bind:value={state.data.notice_text_vendor}></label>
						<label>Purpose <input type="text" bind:value={state.data.notice_text_purpose}></label>
						<label>Privacy policy <input type="text" bind:value={state.data.notice_text_privacy_policy}></label>
						<label>Cookies <input type="text" bind:value={state.data.notice_text_cookies}></label>
						<label>Duration <input type="text" bind:value={state.data.notice_text_duration}></label>
						<label>Hosts <input type="text" bind:value={state.data.notice_text_hosts}></label>
					</div>
				{/if}
			</section>
		{:else if step === 3}
			<section>
				<h2>Scripts &amp; cookies</h2>
				{#if isCategory}
					<p class="muted">Add a category for each kind of cookie (for example Functional, Statistics, Marketing). Each script runs only after the visitor has accepted its category.</p>
					<Categories bind:categories={state.config.categories} consentMode={state.data.consent_mode} />
				{:else}
					<p class="muted">Paste complete HTML, including the <code>&lt;script&gt;</code> tags.</p>
					<label>Scripts on consent <small>run after the visitor accepts</small>
						<textarea class="code" rows="6" spellcheck="false" bind:value={state.config.scripts_consent}></textarea>
					</label>
					<label>Scripts without consent <small>run after the visitor declines</small>
						<textarea class="code" rows="4" spellcheck="false" bind:value={state.config.scripts_no_consent}></textarea>
					</label>
					<label>Cookies to remove on decline <small>comma-separated names</small>
						<input type="text" bind:value={state.config.cookies} placeholder="_ga, _gid">
					</label>
				{/if}
				<label>Scripts always <small>run on every page view, with or without consent</small>
					<textarea class="code" rows="3" spellcheck="false" bind:value={state.config.scripts_always}></textarea>
				</label>
				<label class="check">
					<input type="checkbox" bind:checked={state.data.consent_mode}>
					<span>Enable Google Consent Mode v2 (adds a snippet for the <code>&lt;head&gt;</code>{isCategory ? " and lets you map categories to signals" : ""})</span>
				</label>
			</section>
		{:else if step === 4}
			<section>
				<h2>Appearance</h2>
				<p class="muted">Colors are set as CSS variables on <code>.cookiefox</code>. Leave a color empty to keep the default. See <code>docs/STANDALONE.md</code> for all variables.</p>
				<div class="grid">
					{#each CSS_VARIABLES as item}
						<label>{item.label}
							<span class="color">
								<input type="color" value={state.colors[item.key] || item.default} on:input={e => state.colors[item.key] = e.currentTarget.value}>
								<input type="text" bind:value={state.colors[item.key]} placeholder={item.default}>
							</span>
						</label>
					{/each}
				</div>
			</section>
		{:else}
			<section>
				<h2>Get the code</h2>
				<h3>1. Download the files</h3>
				<p class="muted">Upload them to your site, keeping the <code>js/</code> and <code>css/</code> folders.</p>
				<div class="actions">
					{#each files as file}
						<a class="button" href={file.url} download={file.name} class:disabled={file.missing}>{file.folder}/{file.name}{file.size ? " (" + Math.round(file.size / 1024) + " KB)" : ""}</a>
					{/each}
				</div>
				{#if files.some(file => file.missing)}
					<p class="error">The compiled files were not found. Run <code>npm run build</code> first.</p>
				{/if}
				<label class="spaced">Where you host the CookieFox files <small>folder containing <code>js/main.js</code> and <code>css/main.css</code></small>
					<input type="text" bind:value={state.assetBase}>
				</label>

				<h3>2. Add to the <code>&lt;head&gt;</code></h3>
				<pre>{head}</pre>
				<button type="button" on:click={() => copy(head, "head")}>{copied === "head" ? "Copied" : "Copy"}</button>

				<h3>3. Add before <code>&lt;/body&gt;</code></h3>
				<pre>{body}</pre>
				<button type="button" on:click={() => copy(body, "body")}>{copied === "body" ? "Copied" : "Copy"}</button>

				<h3>4. Optional</h3>
				<p class="muted">Add <code>class="open-cookiefox"</code> to any link, for example in your footer, to let visitors change their choice later.</p>
				<div class="actions">
					<button type="button" on:click={download}>Download snippet</button>
					<button type="button" on:click={() => copy(location.href, "link")}>{copied === "link" ? "Copied" : "Copy link to this configuration"}</button>
				</div>
				<p class="muted">The link contains the whole configuration. Open it later to edit it again.</p>
			</section>
		{/if}

		<div class="pager">
			{#if step > 0}<button type="button" on:click={() => go(step - 1)}>Back</button>{/if}
			{#if step < STEPS.length - 1}<button type="button" class="primary" on:click={() => go(step + 1)}>Next: {STEPS[step + 1]}</button>{/if}
		</div>
	</main>
</div>

	<aside class="side">
		<Preview {state} />
	</aside>
</div>

<style>
	:global(:root){
		--bg: #f5f5f7;
		--surface: #ffffff;
		--text: #1d1d1f;
		--muted: #6e6e73;
		--border: #d2d2d7;
		--accent: #3d854f;
		--accent-soft: #e3f1e6;
		--on-accent: #ffffff;
		--danger: #b3261e;
		color-scheme: light;
	}
	@media (prefers-color-scheme: dark){
		:global(:root){
			--bg: #161617;
			--surface: #1f1f21;
			--text: #f5f5f7;
			--muted: #a1a1a6;
			--border: #3a3a3c;
			--accent: #6cc183;
			--accent-soft: #1f3a27;
			--on-accent: #0b1f10;
			--danger: #ff8a80;
			color-scheme: dark;
		}
	}
	:global(*){
		box-sizing: border-box;
	}
	:global(body){
		margin: 0;
		background: var(--bg);
		color: var(--text);
		font: 15px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	}
	:global(label){
		display: block;
		font-size: 14px;
		font-weight: 500;
		margin: 0 0 12px;
	}
	:global(label small){
		font-weight: 400;
		color: var(--muted);
	}
	:global(input[type=text]), :global(input[type=number]), :global(select), :global(textarea){
		display: block;
		width: 100%;
		margin-top: 4px;
		padding: 7px 10px;
		font: inherit;
		color: inherit;
		background: var(--surface);
		border: 1px solid var(--border);
		border-radius: 6px;
	}
	:global(textarea.code), :global(pre), :global(code){
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		font-size: 13px;
	}
	:global(label.check){
		display: flex;
		gap: 8px;
		align-items: baseline;
		font-weight: 400;
	}
	:global(button){
		font: inherit;
		font-size: 14px;
		padding: 7px 14px;
		border: 1px solid var(--border);
		border-radius: 6px;
		background: var(--surface);
		color: var(--text);
		cursor: pointer;
	}
	:global(a.button){
		display: inline-block;
		font-size: 14px;
		padding: 7px 14px;
		border: 1px solid var(--accent);
		border-radius: 6px;
		background: var(--accent);
		color: var(--on-accent);
		text-decoration: none;
	}
	:global(a.button.disabled){
		opacity: .5;
		pointer-events: none;
	}
	:global(label.spaced){
		margin-top: 20px;
	}
	:global(button:disabled){
		opacity: .5;
		cursor: default;
	}
	:global(button.primary){
		background: var(--accent);
		border-color: var(--accent);
		color: var(--on-accent);
	}
	:global(button.small){
		font-size: 13px;
		padding: 4px 10px;
	}
	:global(button.danger){
		color: var(--danger);
	}
	:global(button.link){
		border: 0;
		background: none;
		padding: 0;
		color: var(--accent);
		text-decoration: underline;
	}
	:global(.grid){
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
		gap: 0 16px;
	}
	:global(.muted){
		color: var(--muted);
	}
	.top{
		padding: 24px 0 0;
	}
	.top h1{
		margin: 0;
		font-size: 24px;
	}
	.top p{
		margin: 4px 0 0;
		color: var(--muted);
	}
	.steps{
		display: flex;
		flex-wrap: wrap;
		gap: 6px;
		padding: 16px 0;
	}
	.steps button{
		border-radius: 999px;
		font-size: 13px;
	}
	.steps button span{
		display: inline-block;
		min-width: 1.4em;
		font-weight: 600;
		color: var(--muted);
	}
	.steps button.active{
		border-color: var(--accent);
		background: var(--accent-soft);
	}
	.layout{
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
		gap: 24px;
		padding: 0 24px;
		align-items: start;
	}
	.left{
		padding-bottom: 48px;
	}
	.form section{
		background: var(--surface);
		border: 1px solid var(--border);
		border-radius: 10px;
		padding: 20px 24px;
	}
	h2{
		margin: 0 0 16px;
		font-size: 20px;
	}
	h3{
		margin: 24px 0 8px;
		font-size: 15px;
	}
	.choice{
		border: 0;
		padding: 0;
		margin: 0 0 16px;
	}
	.segmented{
		display: flex;
		gap: 20px;
	}
	.segmented label{
		margin: 0;
	}
	.choice legend{
		font-weight: 600;
		font-size: 14px;
		margin-bottom: 6px;
	}
	pre{
		background: var(--bg);
		border: 1px solid var(--border);
		border-radius: 6px;
		padding: 12px;
		overflow: auto;
		max-height: 320px;
		white-space: pre;
		tab-size: 2;
		margin: 0 0 8px;
	}
	.color{
		display: flex;
		gap: 6px;
		align-items: center;
	}
	.color input[type=color]{
		width: 40px;
		height: 34px;
		margin-top: 4px;
		padding: 2px;
		border: 1px solid var(--border);
		border-radius: 6px;
		background: var(--surface);
	}
	.actions, .pager{
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		align-items: center;
		margin-top: 12px;
	}
	.pager{
		justify-content: space-between;
		margin-top: 16px;
	}
	.pager .primary{
		margin-left: auto;
	}
	.error{
		color: var(--danger);
	}
	.notice{
		color: var(--accent);
	}
	.side{
		position: sticky;
		top: 0;
		height: 100vh;
		padding: 24px 0;
	}
	@media (max-width: 900px){
		.layout{
			grid-template-columns: minmax(0, 1fr);
			padding: 0 16px;
		}
		.left{
			padding-bottom: 0;
		}
		.side{
			position: static;
			height: 600px;
			padding: 0 0 32px;
		}
	}
</style>
