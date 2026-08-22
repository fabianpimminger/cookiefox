async function waitForCookieFox(page) {
	await page.waitForFunction(() => window.cookiefox && window.cookiefox.api);
}

async function loadMode(page, mode) {
	await page.goto('/?cookiefox_e2e_mode=' + mode);
	await waitForCookieFox(page);
}

async function getConsentCookie(context) {
	const cookies = await context.cookies();
	return cookies.find((cookie) => cookie.name === 'cookiefox_consent');
}

async function parseConsentCookie(context) {
	const cookie = await getConsentCookie(context);
	if (!cookie) {
		return null;
	}
	return JSON.parse(decodeURIComponent(cookie.value));
}

module.exports = {
	waitForCookieFox,
	loadMode,
	getConsentCookie,
	parseConsentCookie,
};
