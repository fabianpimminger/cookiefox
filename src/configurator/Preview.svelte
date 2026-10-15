<script>
	import { onDestroy } from 'svelte';
	import { buildRuntime, headSnippet, bodySnippet } from './snippet.js';

	export let state;

	const COOKIE_NAME = "cookiefox_preview";
	const assetBase = new URL("../assets/frontend/", document.baseURI).href;

	let srcdoc = "";
	let timer;

	function clearPreviewCookie() {
		document.cookie = COOKIE_NAME + "=; Max-Age=0; path=/";
	}

	function withoutScripts(item) {
		return Object.assign({}, item, {
			scripts_always: item.scripts_always === null ? null : "",
			scripts_consent: item.scripts_consent === null ? null : "",
			scripts_no_consent: item.scripts_no_consent === null ? null : ""
		});
	}

	// The preview runs the real bundle, but never the configured scripts.
	function previewDocument(state) {
		const previewState = Object.assign({}, state, { assetBase });
		const runtime = buildRuntime(previewState);
		runtime.data = Object.assign({}, runtime.data, { cookie_name: COOKIE_NAME, cookie_domain: "" });
		runtime.config = withoutScripts(runtime.config);
		runtime.config.categories = runtime.config.categories.map(category => Object.assign({}, category, {
			cookies: category.cookies.map(withoutScripts)
		}));

		return `<!doctype html>
<html lang="${runtime.data.lang || "en"}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>
	// The notice's focus trap would pull focus out of the form on every refresh, so
	// focus() and select() (focus-trap calls both) only work once the visitor is in the preview.
	(function(){
		[[HTMLElement.prototype, "focus"], [HTMLInputElement.prototype, "select"]].forEach(function(item){
			var original = item[0][item[1]];
			item[0][item[1]] = function(){
				if (document.hasFocus()) {
					return original.apply(this, arguments);
				}
			};
		});
	})();
<\/script>
${headSnippet(previewState, runtime)}
<style>
	body{margin:0;padding:32px 24px 240px;font:16px/1.6 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#1d1d1f;background:#f5f5f7}
	.page{max-width:640px;margin:0 auto}
	.line{height:12px;border-radius:6px;background:#dcdce1;margin:12px 0}
	.line.short{width:60%}
	a{color:inherit}
</style>
</head>
<body>
<div class="page">
	<h1>Your website</h1>
	<div class="line"></div><div class="line"></div><div class="line short"></div>
	<p><a href="#" class="open-cookiefox">Cookie settings</a></p>
</div>
${bodySnippet(previewState, runtime)}
</body>
</html>`;
	}

	export function reload() {
		clearPreviewCookie();
		srcdoc = previewDocument(state) + "<!-- " + Date.now() + " -->";
	}

	$: {
		state;
		clearTimeout(timer);
		timer = setTimeout(reload, 300);
	}

	onDestroy(() => clearTimeout(timer));
</script>

<div class="preview">
	<div class="preview__bar">
		<span>Live preview</span>
		<button type="button" class="link" on:click={reload}>Reset consent</button>
	</div>
	<iframe title="Cookie notice preview" {srcdoc}></iframe>
	<p class="hint">Configured scripts are not run in the preview.</p>
</div>

<style>
	.preview{
		display: flex;
		flex-direction: column;
		height: 100%;
	}
	.preview__bar{
		display: flex;
		justify-content: space-between;
		align-items: center;
		font-size: 13px;
		font-weight: 600;
		color: var(--muted);
		padding: 0 0 8px;
	}
	iframe{
		flex: 1;
		width: 100%;
		min-height: 0;
		border: 1px solid var(--border);
		border-radius: 8px;
		background: #fff;
	}
	.hint{
		font-size: 12px;
		color: var(--muted);
		margin: 6px 0 0;
	}
</style>
