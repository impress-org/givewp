import {defineConfig, devices} from '@playwright/test';
import {STORAGE_STATE_PATH, WP_BASE_URL} from './tests/e2e/environment';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 60_000,
    retries: process.env.CI ? 2 : 0,
    /*
     * GiveWP's admin screens share one site's options and database, so parallel workers would see
     * each other's state. Revisit per-worker isolation when the suite grows enough to need it.
     */
    workers: 1,
    reporter: process.env.CI ? 'github' : 'list',
    globalSetup: './tests/e2e/global-setup.ts',
    outputDir: './artifacts/test-results',
    use: {
        baseURL: WP_BASE_URL,
        // Local environments that terminate TLS in front of wp-env use a self-signed certificate.
        ignoreHTTPSErrors: true,
        storageState: STORAGE_STATE_PATH,
        video: 'retain-on-failure',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: {...devices['Desktop Chrome']},
        },
        {
            name: 'headed',
            use: {...devices['Desktop Chrome'], headless: false, launchOptions: {slowMo: 800}},
        },
    ],
});
