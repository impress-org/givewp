import {expect, Page} from '@wordpress/e2e-test-utils-playwright';

/**
 * Helpers for the screens that fetch their own data over the REST API from the browser.
 *
 * A wrong route, a missing nonce, or a response shape the client no longer unwraps all produce a
 * page that builds and mounts cleanly and then shows nothing. Asserting on the traffic catches
 * that where asserting on rendered records cannot, and it holds against both a fresh CI install
 * and a populated developer site, which do not agree on what is on screen.
 */

const REST_ROUTE_PATTERN = /\/wp-json\/|[?&]rest_route=/;

export type RestCall = {method: string; url: string; status: number};

/**
 * Records every REST response the page receives, from the moment it is called.
 */
export function watchRestCalls(page: Page): RestCall[] {
    const calls: RestCall[] = [];

    page.on('response', (response) => {
        const url = response.url();

        if (REST_ROUTE_PATTERN.test(url)) {
            calls.push({method: response.request().method(), url, status: response.status()});
        }
    });

    return calls;
}

/**
 * Waits for the screen to call `route`, then returns every REST call it made.
 *
 * Failing here rather than on a later assertion keeps the message pointed at the request that
 * never happened, which is what a broken route or a client that stopped firing looks like.
 */
export async function restCallsAfter(calls: RestCall[], route: string): Promise<RestCall[]> {
    await expect
        .poll(() => calls.some((call) => call.url.includes(route)), {
            message: `Expected the screen to request ${route}`,
            timeout: 20_000,
        })
        .toBe(true);

    return calls;
}

/**
 * The failed calls among `calls`, formatted for an assertion message that names them.
 */
export function failedCalls(calls: RestCall[]): string[] {
    return calls.filter((call) => call.status >= 400).map((call) => `${call.status} ${call.method} ${call.url}`);
}
