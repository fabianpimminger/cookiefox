const { test, expect } = require('@playwright/test');
const { loadMode, parseConsentCookie } = require('./helpers');

test.describe('category consent', () => {
	test.beforeEach(async ({ page }) => {
		await loadMode(page, 'category');
		await expect(page.getByText('Marketing', { exact: true })).toBeVisible();
	});

	test('shows categories and does not run optional scripts before a choice', async ({ page }) => {
		await expect(page.locator('.cookiefox--notice')).toBeVisible();
		await expect(page.getByRole('checkbox', { name: 'Functional' })).toBeDisabled();
		await expect(page.getByRole('checkbox', { name: 'Marketing' })).not.toBeChecked();
		await expect(page.getByRole('checkbox', { name: 'External Media' })).not.toBeChecked();

		expect(await page.evaluate(() => window.__cookiefoxMarketing)).toBeUndefined();
		await expect(page.locator('.cookiefox__embed.is-blocked')).toBeVisible();
		await expect(page.locator('iframe[title="e2e-youtube"]')).toHaveCount(0);
	});

	test('accept all runs marketing scripts and unblocks embeds', async ({ page }) => {
		await page.getByRole('button', { name: 'Accept' }).click();

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxMarketing)).toBe(true);
		await expect(page.locator('iframe[title="e2e-youtube"]')).toBeVisible();

		const payload = await parseConsentCookie(page.context());
		expect(payload.consent.categories.functional).toBe(true);
		expect(payload.consent.categories.marketing).toBe(true);
		expect(payload.consent.categories['external-media']).toBe(true);
	});

	test('decline does not run marketing scripts or unblock embeds', async ({ page }) => {
		await page.getByRole('button', { name: 'Decline' }).click();

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxMarketing)).toBe(false);
		await expect(page.locator('.cookiefox__embed.is-blocked')).toBeVisible();
		await expect(page.locator('iframe[title="e2e-youtube"]')).toHaveCount(0);

		const payload = await parseConsentCookie(page.context());
		expect(payload.consent.categories.functional).toBe(true);
		expect(payload.consent.categories.marketing).toBe(false);
		expect(payload.consent.categories['external-media']).toBe(false);
	});

	test('saving only External Media unblocks embeds without marketing scripts', async ({ page }) => {
		await page.getByRole('checkbox', { name: 'External Media' }).check();
		await page.getByRole('button', { name: 'Save' }).click();

		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxMarketing)).toBe(false);
		await expect(page.locator('iframe[title="e2e-youtube"]')).toBeVisible();

		const payload = await parseConsentCookie(page.context());
		expect(payload.consent.categories.marketing).toBe(false);
		expect(payload.consent.categories['external-media']).toBe(true);
	});
});
