const { test, expect } = require('@playwright/test');

const WORDPRESS_SOURCE = `<script>
			var cookiefox = {data: {"consent_type":"category","notice_title":"Datenschutz","cookie_name":"cookiefox_consent","api_base":"https:\\/\\/example.com\\/wp-json\\/","x_custom":"keep, {me}"}, config: {"scripts_always":"","scripts_consent":"","scripts_no_consent":"","cookies":"","categories":[{"name":"Statistik","slug":"statistik","description":"<p>Stats<\\/p>","order":0,"cookies":[{"name":"Matomo","slug":"matomo","scripts_consent":"<script>window.__stats = true;<\\/script>"}],"always_on":false,"integrations":[]}]}};
		</script>
		<div id="cookiefox" data-nosnippet></div>`;

async function snippetTexts(page) {
	await page.getByRole('button', { name: /Get the code/ }).first().click();
	const blocks = page.locator('pre');
	return { head: await blocks.nth(0).textContent(), body: await blocks.nth(1).textContent() };
}

test('previews the notice with the default settings', async ({ page }) => {
	await page.goto('/configurator/');
	const preview = page.frameLocator('iframe[title="Cookie notice preview"]');

	await expect(preview.locator('.cookiefox--notice')).toBeVisible();
	await expect(preview.getByRole('button', { name: 'Accept' })).toBeVisible();

	await page.getByRole('button', { name: /Texts/ }).first().click();
	await page.getByLabel('Accept button').fill('Alle akzeptieren');
	await expect(preview.getByRole('button', { name: 'Alle akzeptieren' })).toBeVisible();
});

test('generates a snippet that works on a plain HTML page', async ({ page }) => {
	await page.goto('/configurator/');
	await page.getByRole('button', { name: /Scripts & cookies/ }).first().click();
	await page.getByLabel(/Scripts on consent/).fill('<script>window.__accepted = true;</script>');
	await page.getByRole('button', { name: /Get the code/ }).first().click();
	await page.getByLabel(/Where you host the CookieFox files/).fill('/assets/frontend/');

	const { head, body } = await snippetTexts(page);
	await page.route('**/standalone.html', route => route.fulfill({
		contentType: 'text/html',
		body: '<!doctype html><html><head>' + head + '</head><body><h1>Plain page</h1>' + body + '</body></html>',
	}));

	await page.goto('/standalone.html');
	await expect(page.locator('.cookiefox--notice')).toBeVisible();
	expect(await page.evaluate(() => window.__accepted)).toBeUndefined();

	await page.getByRole('button', { name: 'Accept' }).click();
	await expect(page.locator('.cookiefox--notice')).toBeHidden();
	expect(await page.evaluate(() => window.__accepted)).toBe(true);

	const cookie = (await page.context().cookies()).find(c => c.name === 'cookiefox_consent');
	expect(JSON.parse(decodeURIComponent(cookie.value)).consent).toBe(true);
});

test('imports a WordPress configuration and keeps it in the share link', async ({ page, context }) => {
	await page.goto('/configurator/');
	await page.getByPlaceholder(/var cookiefox/).fill(WORDPRESS_SOURCE);
	await page.getByRole('button', { name: 'Load configuration' }).click();
	await expect(page.getByText('Configuration loaded')).toBeVisible();

	const preview = page.frameLocator('iframe[title="Cookie notice preview"]');
	await expect(preview.getByText('Datenschutz')).toBeVisible();

	await page.getByRole('button', { name: /Scripts & cookies/ }).first().click();
	await expect(page.locator('summary', { hasText: 'Statistik' })).toBeVisible();

	await expect(page).toHaveURL(/#config=/);
	const reopened = await context.newPage();
	await reopened.goto(page.url());
	const { body } = await snippetTexts(reopened);

	expect(body).toContain('"x_custom": "keep, {me}"');
	expect(body).toContain('"consent_type": "category"');
	expect(body).toContain('\\u003cscript>window.__stats = true;\\u003c/script>');
	expect(body.match(/<\/script>/g)).toHaveLength(2);
});

for (const mode of ['simple', 'category']) {
	test(`keeps focus in the form while the preview refreshes (${mode})`, async ({ page }) => {
		await page.goto('/configurator/');
		await page.getByRole('button', { name: /Basics/ }).first().click();
		await page.locator(`input[value=${mode}]`).check();
		if (mode === 'category') {
			await page.getByRole('button', { name: /Scripts & cookies/ }).first().click();
			await page.getByRole('button', { name: '+ Add category' }).click();
			await page.getByLabel('Name').first().fill('Statistics');
		}
		await page.getByRole('button', { name: /Texts/ }).first().click();
		const title = page.getByLabel('Title');
		await title.fill('');
		await title.pressSequentially('Cookies', { delay: 30 });
		await expect(page.frameLocator('iframe[title="Cookie notice preview"]').getByText('Cookies', { exact: true })).toBeVisible();
		await page.keyboard.type(' & privacy');

		await expect(title).toBeFocused();
		await expect(title).toHaveValue('Cookies & privacy');
	});
}

test('switches the default texts to German and keeps edited texts', async ({ page }) => {
	await page.goto('/configurator/');
	await page.getByRole('button', { name: /Texts/ }).first().click();
	await page.getByLabel('Accept button').fill('OK');
	await page.getByLabel('Deutsch').click();

	await expect(page.getByLabel('Title')).toHaveValue('Datenschutz-Einstellungen');
	await expect(page.getByLabel('Decline button')).toHaveValue('Ablehnen');
	await expect(page.getByLabel('Accept button')).toHaveValue('OK');
	const preview = page.frameLocator('iframe[title="Cookie notice preview"]');
	await expect(preview.getByRole('button', { name: 'Ablehnen' })).toBeVisible();

	await page.getByLabel('English').click();
	await expect(page.getByLabel('Title')).toHaveValue('Privacy Settings');
});

test('offers the compiled files for download', async ({ page }) => {
	await page.goto('/configurator/');
	await page.getByRole('button', { name: /Get the code/ }).first().click();

	for (const name of ['js/main.js', 'css/main.css']) {
		const [download] = await Promise.all([
			page.waitForEvent('download'),
			page.getByRole('link', { name: new RegExp(name.replace('.', '\\.')) }).click(),
		]);
		expect(download.suggestedFilename()).toBe(name.split('/')[1]);
	}
	await expect(page.getByText('The compiled files were not found')).toBeHidden();
});
