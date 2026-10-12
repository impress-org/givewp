import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import type {Page} from '@playwright/test';
import {createDonorAccount, deleteDonorAccount, DonorAccount} from './utils/donor';
import {failedCalls, restCallsAfter, watchRestCalls} from './utils/rest';

/**
 * The donor dashboard, served on the front end at ?give-embed=donor-dashboard.
 *
 * It is the only GiveWP React app that runs outside wp-admin, and the embed template builds its
 * own document rather than calling wp_head() and wp_footer(), so what the page enqueues is
 * decided by GiveWP rather than by WordPress. That makes it the one screen where the REST client
 * can be correct and still never authenticate.
 */
test.describe('Donor dashboard', () => {
    /*
     * A rejected login is the cheapest way to prove the REST client end to end on any site: the
     * route answers 200 with the failure described in the body, so reaching the error message
     * means the request carried a valid nonce and the client read the body shape it expects.
     * Succeeding would need a donor account this suite has no way to assume.
     */
    test('front end reaches the login route', async ({page}) => {
        const calls = watchRestCalls(page);

        await page.context().clearCookies();
        await page.goto('/?give-embed=donor-dashboard');

        await expect(page.locator('#give-donor-dashboard')).toBeVisible();

        /*
         * Email access and username login are separate settings and each renders its own form,
         * so the login form is the one holding a password field rather than the first one.
         */
        const loginForm = page.locator('.give-donor-dashboard__auth-modal-form').filter({
            has: page.locator('input[type="password"]'),
        });

        if ((await loginForm.count()) === 0) {
            test.skip(true, 'Donor dashboard login is disabled on this site.');
        }

        await loginForm.locator('input[type="text"]').fill('not-a-donor');
        await loginForm.locator('input[type="password"]').fill('not-a-password');
        await loginForm.getByRole('button', {name: 'Log in'}).click();

        await expect(page.locator('.give-donor-dashboard__auth-modal-error')).toBeVisible();

        /*
         * The route reports a rejected login in the body at HTTP 200, so a status here means the
         * request itself failed - a route that moved, or a nonce the embed template never supplied.
         */
        expect(failedCalls(await restCallsAfter(calls, '/give-api/v2/donor-dashboard/login'))).toEqual([]);
    });
});

/*
 * The app swaps layouts at 920px wide, and the two reach the logout link differently: the desktop
 * layout shows the tab menu, the mobile one tucks it behind a hamburger toggle.
 */
const LAYOUTS = [
    {name: 'desktop', viewport: {width: 1280, height: 800}, openMenu: async (_page: Page) => {}},
    {
        name: 'mobile',
        viewport: {width: 390, height: 844},
        openMenu: async (page: Page) => page.locator('.give-donor-dashboard-mobile-menu__toggle').click(),
    },
];

test.describe('Donor dashboard logout', () => {
    // Signed in as the donor below, not as the suite's admin.
    test.use({storageState: {cookies: [], origins: []}});

    let donor: DonorAccount;

    test.beforeAll(() => {
        donor = createDonorAccount();
    });

    test.afterAll(() => {
        deleteDonorAccount(donor);
    });

    for (const layout of LAYOUTS) {
        test(`logs the donor out from the ${layout.name} layout`, async ({page}) => {
            await page.setViewportSize(layout.viewport);

            await page.goto('/wp-login.php');
            await page.locator('#user_login').fill(donor.username);
            await page.locator('#user_pass').fill(donor.password);
            await Promise.all([
                page.waitForURL((url) => !url.pathname.endsWith('/wp-login.php')),
                page.locator('#wp-submit').click(),
            ]);

            await page.goto('/?give-embed=donor-dashboard');
            await expect(page.locator('.give-donor-dashboard__auth-modal')).toHaveCount(0);

            await layout.openMenu(page);
            await page.locator('.give-donor-dashboard-logout').getByText('Logout').click();

            const dialog = page.getByRole('dialog', {name: 'Are you sure you want to logout?'});
            await expect(dialog).toBeVisible();

            const [logout] = await Promise.all([
                page.waitForResponse((response) => response.url().includes('/give-api/v2/donor-dashboard/logout')),
                dialog.getByRole('button', {name: 'Yes, logout'}).click(),
            ]);
            expect(logout.ok()).toBe(true);

            // The session is gone once the dashboard asks the visitor to sign in again.
            await page.goto('/?give-embed=donor-dashboard');
            await expect(page.locator('.give-donor-dashboard__auth-modal')).toBeVisible();
        });
    }
});
