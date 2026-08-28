import {RequestUtils} from '@wordpress/e2e-test-utils-playwright';
import {STORAGE_STATE_PATH, WP_BASE_URL} from './environment';

const GIVEWP_REST_NAMESPACE = 'givewp/v3';
const WHERE_TO_POINT_IT = 'Start wp-env, or point the run at the right environment with WP_BASE_URL.';

/**
 * Logs in once as the wp-env admin and writes the session to disk. Every spec reuses that state
 * through `storageState` in playwright.config.ts, so no test pays for a login round trip.
 */
async function globalSetup(): Promise<void> {
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
    await dismissFormBuilderTours(requestUtils);
}

/*
 * The form builder opens a guided tour over itself the first time a user reaches each of its two
 * editor modes, which on a fresh install is every run and which covers the canvas the builder specs
 * click into. The tour is the product's, not the suite's, and marking it seen is what the builder
 * does when a person finishes it.
 */
async function dismissFormBuilderTours(requestUtils: RequestUtils): Promise<void> {
    for (const mode of ['design', 'schema']) {
        await requestUtils.request.post(`${WP_BASE_URL}/wp-admin/admin-ajax.php`, {
            form: {action: 'givewp_tour_completed', mode},
        });
    }
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
