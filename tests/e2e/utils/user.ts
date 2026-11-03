import type {Browser, BrowserContext, Page, Request} from '@playwright/test';
import {expect, RequestUtils} from '@wordpress/e2e-test-utils-playwright';
import {STORAGE_STATE_PATH, WP_BASE_URL} from '../environment';

/**
 * Helpers for specs that need more than the one signed-in admin: the same admin in a second
 * browser, or a different user with a role of its own.
 */

export type SignedInUser = {id: number; context: BrowserContext; page: Page};

/**
 * A new browser context with the admin's saved session, as if the admin opened a second browser.
 * It shares no tab history or session storage with the test's own page.
 */
export async function adminInNewBrowser(browser: Browser): Promise<BrowserContext> {
    return browser.newContext({baseURL: WP_BASE_URL, ignoreHTTPSErrors: true, storageState: STORAGE_STATE_PATH});
}

/**
 * Creates a user with `role`, signs it in through the login form in a new browser context, and
 * returns the user's id with that context. Call `deleteUser` when the test is done.
 */
export async function signInNewUser(browser: Browser, requestUtils: RequestUtils, role: string): Promise<SignedInUser> {
    const username = `e2e-${role}-${Date.now()}`;
    const password = 'e2e-password';
    const user = await requestUtils.createUser({
        username,
        email: `${username}@example.test`,
        password,
        roles: [role],
    });

    const context = await browser.newContext({
        baseURL: WP_BASE_URL,
        ignoreHTTPSErrors: true,
        storageState: {cookies: [], origins: []},
    });
    const page = await context.newPage();

    await page.goto('/wp-login.php');
    await page.locator('#user_login').fill(username);
    await page.locator('#user_pass').fill(password);
    await page.locator('#wp-submit').click();
    await expect(page).not.toHaveURL(/wp-login\.php/);

    return {id: Number(user.id), context, page};
}

/**
 * Closes the user's browser context and deletes the user, giving its content to the admin.
 */
export async function deleteUser(requestUtils: RequestUtils, user: SignedInUser): Promise<void> {
    await user.context.close();
    await requestUtils.rest({method: 'DELETE', path: `/wp/v2/users/${user.id}`, params: {force: true, reassign: 1}});
}

/**
 * Clears the signed-in admin's stored layout for a list screen, so a test starts from the defaults.
 */
export async function resetLayout(requestUtils: RequestUtils, screen: string): Promise<void> {
    await requestUtils.rest({method: 'DELETE', path: `/givewp/v3/list-table-layouts/${screen}`});
}

/**
 * Whether a request stores a layout. `apiFetch` sends a PUT as a POST with a method-override header.
 */
export function isLayoutWrite(request: Request): boolean {
    const method = request.headers()['x-http-method-override'] ?? request.method();

    return method.toUpperCase() === 'PUT';
}
