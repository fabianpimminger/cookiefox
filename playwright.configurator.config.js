const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
	testDir: 'tests/configurator',
	fullyParallel: false,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: 1,
	timeout: 30000,
	expect: {
		timeout: 10000,
	},
	reporter: [['list']],
	use: {
		baseURL: 'http://127.0.0.1:9401',
		trace: 'on-first-retry',
		viewport: { width: 1280, height: 800 },
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],
	webServer: {
		command: 'node tests/static-server.js 9401',
		url: 'http://127.0.0.1:9401/configurator/',
		reuseExistingServer: !process.env.CI,
		timeout: 30000,
	},
});
