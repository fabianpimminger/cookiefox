<script>
	import { CONSENT_MODE_TYPES, newCategory, newCookie, slugify } from './snippet.js';

	export let categories;
	export let consentMode = false;

	const COOKIE_TEXT_FIELDS = [
		{ key: "vendor", label: "Vendor" },
		{ key: "cookies", label: "Cookies", placeholder: "_ga, _gid" },
		{ key: "duration", label: "Duration", placeholder: "2 years" },
		{ key: "hosts", label: "Hosts", placeholder: "google-analytics.com" }
	];

	const COOKIE_SCRIPT_FIELDS = [
		{ key: "scripts_consent", label: "Scripts on consent" },
		{ key: "scripts_no_consent", label: "Scripts without consent" },
		{ key: "scripts_always", label: "Scripts always" }
	];

	function addCategory() {
		categories = [...categories, newCategory()];
	}

	function removeCategory(index) {
		if (confirm("Remove this category and its cookies?")) {
			categories = categories.filter((_, i) => i !== index);
		}
	}

	function move(list, index, offset) {
		const target = index + offset;
		if (target < 0 || target >= list.length) {
			return list;
		}
		const copy = [...list];
		[copy[index], copy[target]] = [copy[target], copy[index]];
		return copy;
	}

	function addCookie(category) {
		category.cookies = [...category.cookies, newCookie()];
		categories = categories;
	}

	function removeCookie(category, index) {
		category.cookies = category.cookies.filter((_, i) => i !== index);
		categories = categories;
	}

	function fillSlug(item) {
		if (!item.slug) {
			item.slug = slugify(item.name);
			categories = categories;
		}
	}

	// Opens new (unnamed) items once; afterwards the visitor controls open/closed.
	function openIfNew(node, isNew) {
		node.open = isNew;
	}

	function toggleType(category, type, checked) {
		const types = category.consent_mode.filter(t => t !== type);
		category.consent_mode = checked ? [...types, type] : types;
		categories = categories;
	}
</script>

{#each categories as category, ci (category)}
	<details class="card" use:openIfNew={!category.name}>
		<summary>
			<span class="card__title">{category.name || "New category"}</span>
			{#if category.always_on}<span class="badge">always on</span>{/if}
			<span class="card__meta">{category.cookies.length} {category.cookies.length === 1 ? "cookie" : "cookies"}</span>
		</summary>

		<div class="grid">
			<label>Name
				<input type="text" bind:value={category.name} on:blur={() => fillSlug(category)} placeholder="Statistics">
			</label>
			<label>Slug
				<input type="text" bind:value={category.slug} placeholder={slugify(category.name) || "statistics"}>
			</label>
		</div>
		<label>Description <small>HTML allowed</small>
			<textarea rows="3" bind:value={category.description}></textarea>
		</label>
		<label class="check">
			<input type="checkbox" bind:checked={category.always_on}>
			Always on (required, can't be declined)
		</label>

		{#if consentMode}
			<fieldset>
				<legend>Google Consent Mode signals granted by this category</legend>
				{#each CONSENT_MODE_TYPES as type}
					<label class="check">
						<input type="checkbox" checked={category.consent_mode.includes(type)} on:change={e => toggleType(category, type, e.currentTarget.checked)}>
						<code>{type}</code>
					</label>
				{/each}
			</fieldset>
		{/if}

		<h4>Cookies &amp; services</h4>
		{#each category.cookies as cookie, ki (cookie)}
			<details class="card card--inner" use:openIfNew={!cookie.name}>
				<summary><span class="card__title">{cookie.name || "New cookie / service"}</span></summary>
				<div class="grid">
					<label>Name
						<input type="text" bind:value={cookie.name} on:blur={() => fillSlug(cookie)} placeholder="Google Analytics">
					</label>
					<label>Slug
						<input type="text" bind:value={cookie.slug} placeholder={slugify(cookie.name) || "google-analytics"}>
					</label>
					{#each COOKIE_TEXT_FIELDS as field}
						<label>{field.label}
							<input type="text" bind:value={cookie[field.key]} placeholder={field.placeholder || ""}>
						</label>
					{/each}
				</div>
				<label>Purpose <small>HTML allowed</small>
					<textarea rows="2" bind:value={cookie.description}></textarea>
				</label>
				<label>Privacy policy <small>HTML allowed, e.g. a link</small>
					<input type="text" bind:value={cookie.privacy_policy}>
				</label>
				{#each COOKIE_SCRIPT_FIELDS as field}
					<label>{field.label}
						<textarea class="code" rows="3" spellcheck="false" bind:value={cookie[field.key]}></textarea>
					</label>
				{/each}
				<div class="actions">
					<button type="button" class="small" disabled={ki === 0} on:click={() => { category.cookies = move(category.cookies, ki, -1); categories = categories; }}>↑</button>
					<button type="button" class="small" disabled={ki === category.cookies.length - 1} on:click={() => { category.cookies = move(category.cookies, ki, 1); categories = categories; }}>↓</button>
					<button type="button" class="small danger" on:click={() => removeCookie(category, ki)}>Remove</button>
				</div>
			</details>
		{/each}
		<button type="button" class="small" on:click={() => addCookie(category)}>+ Add cookie / service</button>

		<div class="actions">
			<button type="button" class="small" disabled={ci === 0} on:click={() => categories = move(categories, ci, -1)}>Move up</button>
			<button type="button" class="small" disabled={ci === categories.length - 1} on:click={() => categories = move(categories, ci, 1)}>Move down</button>
			<button type="button" class="small danger" on:click={() => removeCategory(ci)}>Remove category</button>
		</div>
	</details>
{/each}

<button type="button" class="add" on:click={addCategory}>+ Add category</button>

<style>
	.card{
		border: 1px solid var(--border);
		border-radius: 8px;
		padding: 12px 16px;
		margin: 0 0 12px;
		background: var(--surface);
	}
	.card--inner{
		background: var(--bg);
	}
	summary{
		cursor: pointer;
		display: flex;
		gap: 8px;
		align-items: center;
	}
	.card[open] > summary{
		margin-bottom: 12px;
	}
	.card__title{
		font-weight: 600;
	}
	.card__meta{
		margin-left: auto;
		font-size: 13px;
		color: var(--muted);
	}
	.badge{
		font-size: 11px;
		padding: 1px 6px;
		border-radius: 4px;
		background: var(--accent-soft);
		color: var(--accent);
	}
	h4{
		margin: 16px 0 8px;
		font-size: 14px;
	}
	fieldset{
		border: 1px solid var(--border);
		border-radius: 6px;
		margin: 0 0 12px;
		padding: 8px 12px;
	}
	legend{
		font-size: 13px;
		color: var(--muted);
	}
	.add{
		margin-bottom: 20px;
	}
	.actions{
		display: flex;
		gap: 8px;
		margin-top: 12px;
	}
</style>
