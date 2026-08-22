const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
	testDir: 'tests/e2e',
	fullyParallel: false,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: 1,
	timeout: 45000,
	expect: {
		timeout: 10000,
	},
	reporter: [['list']],
	use: {
		baseURL: 'http://127.0.0.1:9400',
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
		command: 'npx @wp-playground/cli server --auto-mount --blueprint=tests/e2e/blueprint.json --blueprint-may-read-adjacent-files --port=9400',
		url: 'http://127.0.0.1:9400',
		reuseExistingServer: !process.env.CI,
		timeout: 180000,
		stdout: 'pipe',
		stderr: 'pipe',
	},
});
