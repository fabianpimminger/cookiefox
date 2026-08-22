async function waitForCookieFox(page) {
	await page.waitForFunction(() => window.cookiefox && window.cookiefox.api && typeof window.cookiefox.api.show === 'function');
}

async function loadMode(page, mode, options = {}) {
	let url = '/?cookiefox_e2e_mode=' + mode;
	if (options.rest) {
		url += '&cookiefox_e2e_rest=1';
	}
	if (options.integration) {
		url += '&cookiefox_e2e_integration=1';
	}
	await page.goto(url);
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
