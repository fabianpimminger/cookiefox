const { test, expect } = require('@playwright/test');
const { loadMode, lastConsentModeEvent, waitForCookieFox } = require('./helpers');

const denied = {
	analytics_storage: 'denied',
	ad_storage: 'denied',
	ad_user_data: 'denied',
	ad_personalization: 'denied',
};

const granted = {
	analytics_storage: 'granted',
	ad_storage: 'granted',
	ad_user_data: 'granted',
	ad_personalization: 'granted',
};

const adsGranted = {
	analytics_storage: 'denied',
	ad_storage: 'granted',
	ad_user_data: 'granted',
	ad_personalization: 'granted',
};

test.describe('google consent mode', () => {
	test('is off by default and does not print a gtag default', async ({ page }) => {
		await loadMode(page, 'simple');
		const html = await page.content();
		expect(html).not.toContain("gtag('consent', 'default'");
		expect(await lastConsentModeEvent(page)).toBeNull();
	});

	test('prints a denied default before a choice', async ({ page }) => {
		await loadMode(page, 'simple', { consentMode: true });
		const html = await page.content();
		expect(html).toContain("gtag('consent', 'default'");
		expect(html).toContain('"analytics_storage":"denied"');
		expect(html).toContain('"ad_storage":"denied"');
		expect(html).toContain('"ad_user_data":"denied"');
		expect(html).toContain('"ad_personalization":"denied"');
	});

	test('updates all signals after simple accept', async ({ page }) => {
		await loadMode(page, 'simple', { consentMode: true });
		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await lastConsentModeEvent(page)).toEqual(granted);
	});

	test('keeps signals denied after simple decline', async ({ page }) => {
		await loadMode(page, 'simple', { consentMode: true });
		await page.getByRole('button', { name: 'Decline' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await lastConsentModeEvent(page)).toEqual(denied);
	});

	test('prints an update in the head on the next visit after accept', async ({ page }) => {
		await loadMode(page, 'simple', { consentMode: true });
		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();

		await page.reload();
		await waitForCookieFox(page);
		const html = await page.content();
		expect(html).toContain("gtag('consent', 'update'");
		expect(html).toContain('"analytics_storage":"granted"');
		expect(await lastConsentModeEvent(page)).toEqual(granted);
	});

	test('grants mapped ads signals after category accept all', async ({ page }) => {
		await loadMode(page, 'category', { consentMode: true });
		await page.getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await lastConsentModeEvent(page)).toEqual(adsGranted);
	});

	test('does not grant ads signals when only External Media is saved', async ({ page }) => {
		await loadMode(page, 'category', { consentMode: true });
		await page.getByRole('checkbox', { name: 'External Media' }).check();
		await page.getByRole('button', { name: 'Save' }).click();
		await expect(page.locator('.cookiefox--notice')).toBeHidden();
		expect(await lastConsentModeEvent(page)).toEqual(denied);
	});
});
