const { test, expect } = require('@playwright/test');
const { loadMode } = require('./helpers');

test.describe('plugin integrations', () => {
	test('simple mode does not run a blocked integration before accept', async ({ page }) => {
		await loadMode(page, 'simple', { integration: true });

		expect(await page.evaluate(() => window.__cookiefoxIntegration)).toBeUndefined();
		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxIntegration)).toBe(true);
	});

	test('category mode runs the integration only after accept', async ({ page }) => {
		await loadMode(page, 'category');

		expect(await page.evaluate(() => window.__cookiefoxIntegration)).toBeUndefined();
		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await page.evaluate(() => window.__cookiefoxIntegration)).toBe(true);
	});
});
