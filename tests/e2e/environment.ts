import {existsSync, readFileSync} from 'fs';
import path from 'path';

/**
 * The URL wp-env is serving on.
 *
 * This suite is written for CI, where the workflow starts wp-env on the port declared in
 * `.wp-env.json` and nothing else is in play. Reading that port here rather than hardcoding it
 * keeps the two from drifting apart. `WP_BASE_URL` overrides everything, which is what a local
 * run against a different port, host, or TLS proxy uses.
 */
function resolveBaseUrl(): string {
    if (process.env.WP_BASE_URL) {
        return process.env.WP_BASE_URL;
    }

    const configPath = path.join(process.cwd(), '.wp-env.json');
    const port = existsSync(configPath) ? JSON.parse(readFileSync(configPath, 'utf8'))?.port : null;

    return `http://localhost:${port ?? 8888}`;
}

export const WP_BASE_URL = resolveBaseUrl();

/*
 * `@wordpress/e2e-test-utils-playwright` reads `WP_BASE_URL` off the environment once, at module
 * load, and otherwise defaults to wp-env's tests environment on port 8889. The `baseURL` passed to
 * `RequestUtils.setup()` does not reach it, so REST API discovery would go to 8889 no matter what
 * the rest of the suite is pointed at. Write the resolved URL back so both agree. Playwright loads
 * playwright.config, which imports this module, before any spec in every process.
 */
process.env.WP_BASE_URL = WP_BASE_URL;

/*
 * `RequestUtils` builds its own request context with no way to pass `ignoreHTTPSErrors`, so a local
 * TLS proxy with a self-signed certificate fails every REST call it makes - in global setup and in
 * the worker-scoped `requestUtils` fixture alike, which are separate processes. Both load this
 * module through playwright.config.ts, so relaxing it here covers both. Local runs only; CI talks
 * to wp-env over plain HTTP and keeps verification on.
 */
if (!process.env.CI && WP_BASE_URL.startsWith('https://')) {
    process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
}

export const STORAGE_STATE_PATH =
    process.env.STORAGE_STATE_PATH ?? path.join(process.cwd(), 'artifacts/storage-states/admin.json');
