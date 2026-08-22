const { test, expect } = require('@playwright/test');
const { loadMode, waitForCookieFox, parseConsentCookie } = require('./helpers');

function collectConfigRequests(page) {
	const requests = [];
	page.on('request', (request) => {
		if (request.url().includes('cookiefox/v1/cookies')) {
			requests.push(request.url());
		}
	});
	return requests;
}

test.describe('REST config loader', () => {
	test('simple mode fetches config and runs always-on scripts', async ({ page }) => {
		const requests = collectConfigRequests(page);
		await loadMode(page, 'simple', { rest: true });

		expect(requests.length).toBeGreaterThan(0);
		await expect(page.locator('.cookiefox--notice')).toBeVisible();
		expect(await page.evaluate(() => window.__cookiefoxAlways)).toBe(true);
		expect(await page.evaluate(() => window.cookiefox.data.scripts_consent)).toBeUndefined();
	});

	test('simple mode accept still injects opt-in scripts', async ({ page }) => {
		await loadMode(page, 'simple', { rest: true });
		await page.getByRole('button', { name: 'Accept' }).click();

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxConsent)).toBe('accept');
	});

	test('reuses sessionStorage and skips a second fetch', async ({ page }) => {
		const requests = collectConfigRequests(page);
		await loadMode(page, 'simple', { rest: true });
		const afterFirst = requests.length;
		expect(afterFirst).toBeGreaterThan(0);

		await page.reload();
		await waitForCookieFox(page);

		expect(requests.length).toBe(afterFirst);
		expect(await page.evaluate(() => window.__cookiefoxAlways)).toBe(true);
	});

	test('category mode fetches config and can accept all', async ({ page }) => {
		const requests = collectConfigRequests(page);
		await loadMode(page, 'category', { rest: true });

		expect(requests.length).toBeGreaterThan(0);
		await expect(page.getByText('Marketing', { exact: true })).toBeVisible();

		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxMarketing)).toBe(true);

		const payload = await parseConsentCookie(page.context());
		expect(payload.consent.categories.marketing).toBe(true);
	});
});
