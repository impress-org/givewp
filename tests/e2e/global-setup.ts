import {RequestUtils} from '@wordpress/e2e-test-utils-playwright';
import {STORAGE_STATE_PATH, WP_BASE_URL} from './environment';

const GIVEWP_REST_NAMESPACE = 'givewp/v3';
const WHERE_TO_POINT_IT = 'Start wp-env, or point the run at the right environment with WP_BASE_URL.';

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

    await assertGiveWpIsActive(requestUtils);
}

/*
 * A WordPress install answering on the expected port is not necessarily the one under test. Another
 * wp-env environment can hold the port, in which case every spec would run green against a site
 * that has nothing to do with this branch. Fail here instead, where the message can say so.
 */
async function assertGiveWpIsActive(requestUtils: RequestUtils): Promise<void> {
    let index;

    try {
        /*
         * `rest()` goes through the REST root discovered during setup rather than assuming
         * /wp-json/, which a site with a custom prefix or one installed in a subdirectory does not
         * serve, and it throws on a non-2xx response instead of handing back a body to misread.
         */
        index = await requestUtils.rest({path: '/'});
    } catch (error) {
        // `rest()` rejects with the parsed error body rather than an Error, so interpolating it directly reads as [object Object].
        const reason = error instanceof Error ? error.message : JSON.stringify(error);

        throw new Error(`Could not read the REST API index at ${WP_BASE_URL}: ${reason}. ${WHERE_TO_POINT_IT}`);
    }

    if (!Array.isArray(index?.namespaces) || !index.namespaces.includes(GIVEWP_REST_NAMESPACE)) {
        throw new Error(
            `GiveWP is not active at ${WP_BASE_URL}. The REST API there does not expose ` +
                `"${GIVEWP_REST_NAMESPACE}", so this is either the wrong site or the plugin failed ` +
                `to load. ${WHERE_TO_POINT_IT}`
        );
    }
}

export default globalSetup;
