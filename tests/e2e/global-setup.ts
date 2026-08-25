import {RequestUtils} from '@wordpress/e2e-test-utils-playwright';
import {STORAGE_STATE_PATH, WP_BASE_URL} from './environment';

/**
 * Logs in once as the wp-env admin and writes the session to disk. Every spec reuses that state
 * through `storageState` in playwright.config.ts, so no test pays for a login round trip.
 */
async function globalSetup(): Promise<void> {
    /*
     * `RequestUtils` builds its own request context and offers no way to pass `ignoreHTTPSErrors`,
     * so a local TLS proxy with a self-signed certificate fails the login before any spec runs,
     * while the browser, which does get `ignoreHTTPSErrors`, would have been fine. Match the two
     * for local runs only. CI talks to wp-env over plain HTTP and keeps verification on.
     */
    if (!process.env.CI && WP_BASE_URL.startsWith('https://')) {
        process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
    }

    const requestUtils = await RequestUtils.setup({
        baseURL: WP_BASE_URL,
        storageStatePath: STORAGE_STATE_PATH,
    });

    await requestUtils.setupRest();

    /*
     * GiveWP sends the first authenticated admin request after activation to the onboarding wizard
     * and then clears the flag. Spend it here so the redirect never lands in the middle of a spec.
     */
    await requestUtils.request.get(`${WP_BASE_URL}/wp-admin/`);
}

export default globalSetup;
