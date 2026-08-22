const { test, expect } = require('@playwright/test');
const { loadMode, getConsentCookie, parseConsentCookie } = require('./helpers');

test.describe('simple consent', () => {
	test.beforeEach(async ({ page }) => {
		await loadMode(page, 'simple');
	});

	test('shows the notice and runs always-on scripts before a choice', async ({ page }) => {
		await expect(page.locator('.cookiefox--notice')).toBeVisible();
		await expect(page.getByRole('button', { name: 'Accept' })).toBeVisible();
		await expect(page.getByRole('button', { name: 'Decline' })).toBeVisible();

		expect(await page.evaluate(() => window.__cookiefoxAlways)).toBe(true);
		expect(await page.evaluate(() => window.__cookiefoxConsent)).toBeUndefined();
		expect(await getConsentCookie(page.context())).toBeUndefined();
	});

	test('runs opt-in scripts and stores consent after accept', async ({ page }) => {
		await page.getByRole('button', { name: 'Accept' }).click();

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxConsent)).toBe('accept');

		const payload = await parseConsentCookie(page.context());
		expect(payload.consent).toBe(true);
		expect(typeof payload.ts).toBe('number');
		expect(payload.v).toBe(0);
	});

	test('runs opt-in scripts again on the next page view', async ({ page }) => {
		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();

		await page.reload();
		await page.waitForFunction(() => window.cookiefox && window.cookiefox.api && typeof window.cookiefox.api.show === 'function');

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxAlways)).toBe(true);
		expect(await page.evaluate(() => window.__cookiefoxConsent)).toBe('accept');
	});

	test('runs opt-out scripts after decline', async ({ page }) => {
		await page.getByRole('button', { name: 'Decline' }).click();

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxConsent)).toBe('decline');

		const payload = await parseConsentCookie(page.context());
		expect(payload.consent).toBe(false);
	});

	test('Escape declines in simple mode', async ({ page }) => {
		await page.keyboard.press('Escape');

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxConsent)).toBe('decline');
	});

	test('open-cookiefox reopens the notice after a choice', async ({ page }) => {
		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();

		await page.getByRole('link', { name: 'Open CookieFox' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeVisible();
	});

	test('blocks a YouTube embed until accept', async ({ page }) => {
		await expect(page.locator('.cookiefox__embed.is-blocked')).toBeVisible();
		await expect(page.locator('iframe[title="e2e-youtube"]')).toHaveCount(0);

		await page.getByRole('button', { name: 'Accept' }).click();

		await expect(page.locator('.cookiefox__embed.is-blocked')).toHaveCount(0);
		await expect(page.locator('iframe[title="e2e-youtube"]')).toBeVisible();
	});
});
