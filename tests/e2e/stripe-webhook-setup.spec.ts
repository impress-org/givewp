import {Admin, expect, Locator, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {wp} from './utils/wp-cli';

/**
 * Settings > Payment Gateways > Stripe: the webhook setup modal and the Webhooks group.
 *
 * After a Stripe account is added, Stripe Connect sends the admin back with `stripe_account=connected`,
 * and the page asks the server to set up each account's webhook endpoint and shows the result in a
 * modal. The Webhooks group runs the same request from a button. What the server answers is covered
 * by PHPUnit; what is not is whether the page sends the request with its nonce, renders each level the
 * server can answer with, offers the retry, and falls back to the manual steps when the request fails.
 *
 * The setup request is answered in the browser by `fakeWebhookSetup`, so no run reaches Stripe and the
 * site needs no Stripe account: the element that starts the flow renders on the accounts group whether
 * or not one exists.
 */
const STRIPE_SETTINGS = 'post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings';
const SETUP_ACTION = 'givewp_stripe_ensure_webhook_endpoints';
const HEALTH_EMAIL_SETTING = 'stripe_webhook_health_email';

type SetupResult = {level: 'success' | 'warning' | 'error'; title: string; body: string; retry: boolean};

/**
 * The answer a server gives when the request dies before the controller can reply, which is what
 * makes the page fall back to the manual steps it printed into `#give-stripe-connected`.
 */
const SERVER_ERROR = 'server-error';

type FakeAnswer = SetupResult | typeof SERVER_ERROR;

type SetupRequest = {action: string | null; nonce: string | null};

/*
 * The results below copy the wording WebhookSetupNotice builds, so a failure screenshot reads like the
 * modal an admin would see.
 */
const ENDPOINT_LINE =
    '<span>Webhook endpoint for test mode: <code>http://localhost/?give-listener=stripe</code></span>';

const CREATED: SetupResult = {
    level: 'success',
    title: 'You are connected! Your Stripe webhook is set up.',
    body: `${ENDPOINT_LINE}<ul class="give-stripe-webhook-results"><li><strong>E2E Stripe Account</strong> GiveWP created the endpoint for this site.</li></ul>`,
    retry: false,
};

const RATE_LIMITED: SetupResult = {
    level: 'warning',
    title: 'You are connected, but the Stripe webhook needs your attention.',
    body: `${ENDPOINT_LINE}<ul class="give-stripe-webhook-results"><li><strong>E2E Stripe Account</strong> Stripe received too many requests from this site. Wait a minute, then try again.</li></ul>`,
    retry: true,
};

const NO_ACCOUNTS: SetupResult = {
    level: 'error',
    title: 'We could not set up your Stripe webhook automatically.',
    body: '<span>Add the endpoint yourself: visit the Webhooks section of your Stripe Dashboard.</span>',
    retry: true,
};

test.describe('Stripe webhook setup modal', () => {
    test('opens after an account is added and shows the result of the setup request', async ({page, admin}) => {
        const setup = await fakeWebhookSetup(page, [CREATED]);
        const release = setup.hold();

        await visitAfterAccountAdded(admin);

        // The loader stays up for as long as the request is out, which is what the hold is for.
        await expect(modal(page, 'notice')).toContainText('Setting up your Stripe webhook');

        release();

        await expect(modal(page, 'success').locator('.give-modal__title')).toHaveText(CREATED.title);
        await expect(modal(page, 'success')).toContainText('GiveWP created the endpoint for this site.');
        await expect(modal(page, 'notice')).toHaveCount(0);
        await expect(retryButton(page)).toHaveCount(0);

        /*
         * The page swaps its URL for the one without `stripe_account`, so a reload does not start the setup
         * again.
         */
        await expect(page).not.toHaveURL(/stripe_account=/);
        await expect(page).toHaveURL(/section=stripe-settings/);

        const nonce = await page.locator('#give-stripe-connected').getAttribute('data-webhook-nonce');

        expect(nonce).toBeTruthy();
        expect(setup.requests).toEqual([{action: SETUP_ACTION, nonce}]);
    });

    test('offers a retry on a warning, and the retry sends the request again', async ({page, admin}) => {
        const setup = await fakeWebhookSetup(page, [RATE_LIMITED, CREATED]);

        await visitAfterAccountAdded(admin);

        await expect(modal(page, 'warning').locator('.give-modal__title')).toHaveText(RATE_LIMITED.title);
        await expect(modal(page, 'warning')).toContainText('Stripe received too many requests from this site.');

        await retryButton(page).click();

        await expect(modal(page, 'success').locator('.give-modal__title')).toHaveText(CREATED.title);
        await expect(modal(page, 'warning')).toHaveCount(0);

        // The retry button carries the nonce of the request it repeats.
        expect(setup.requests).toHaveLength(2);
        expect(setup.requests[1]).toEqual(setup.requests[0]);
    });

    test('shows an error result with a retry', async ({page, admin}) => {
        await fakeWebhookSetup(page, [NO_ACCOUNTS]);

        await visitAfterAccountAdded(admin);

        await expect(modal(page, 'error').locator('.give-modal__title')).toHaveText(NO_ACCOUNTS.title);
        await expect(modal(page, 'error')).toContainText('Add the endpoint yourself');
        await expect(retryButton(page)).toBeVisible();
    });

    test('falls back to the manual steps when the request fails', async ({page, admin}) => {
        await fakeWebhookSetup(page, [SERVER_ERROR]);

        await visitAfterAccountAdded(admin);

        /*
         * The title attribute holds a <strong> around the sentence and the modal renders it as markup, so
         * the heading's text is the attribute without its tags.
         */
        const title = await page.locator('#give-stripe-connected').getAttribute('data-title');
        const titleText = (title ?? '').replace(/<[^>]+>/g, '').trim();

        expect(titleText).toBeTruthy();

        await expect(modal(page, 'error').locator('.give-modal__title')).toHaveText(titleText);
        await expect(modal(page, 'error')).toContainText('?give-listener=stripe');
        await expect(modal(page, 'error')).toContainText(
            'Stripe webhooks are required so GiveWP can communicate properly with the payment gateway'
        );
        await expect(retryButton(page)).toBeVisible();
    });

    test('does not open a result when the loader was closed first', async ({page, admin}) => {
        const setup = await fakeWebhookSetup(page, [CREATED]);
        const release = setup.hold();

        await visitAfterAccountAdded(admin);

        await modal(page, 'notice').getByRole('button', {name: 'Close'}).click();

        // Magnific Popup keeps its wrapper for the 300 ms close animation, and the wrapper is what the page checks.
        await expect(page.locator('.mfp-wrap')).toHaveCount(0);

        const answered = page.waitForEvent('requestfinished', (request) => isSetupRequest(request.postData()));
        release();
        await answered;

        /*
         * Nothing on the page changes when the answer is dropped, so there is no event to wait for. The
         * page parses the body and decides within a few tasks of the response; this gives it far longer.
         */
        await page.evaluate(() => new Promise((resolve) => setTimeout(resolve, 500)));

        await expect(page.locator('.give-modal')).toHaveCount(0);
        expect(setup.requests).toHaveLength(1);
    });

    test('does not start without stripe_account=connected', async ({page, admin}) => {
        const setup = await fakeWebhookSetup(page, [CREATED]);

        await admin.visitAdminPage('edit.php', STRIPE_SETTINGS);

        // The setup starts on DOMContentLoaded, so by the load event `visitAdminPage` waits for it would have run.
        await expect(page.locator('#give-stripe-connected')).toHaveAttribute('data-display', '1');
        await expect(page.locator('.give-modal')).toHaveCount(0);
        expect(setup.requests).toEqual([]);
    });
});

test.describe('Stripe Webhooks settings group', () => {
    test('is in the Stripe menu and its button runs the setup', async ({page, admin}) => {
        const setup = await fakeWebhookSetup(page, [CREATED]);

        await admin.visitAdminPage('edit.php', STRIPE_SETTINGS);
        await page.locator('.give-settings-section-group-menu').getByRole('link', {name: 'Webhooks'}).click();

        const group = webhooksGroup(page);
        await expect(group).toBeVisible();
        await expect(page).toHaveURL(/group=webhooks/);

        // The manual instructions stay next to the button for sites that cannot use it.
        await expect(group.locator('.give-stripe-webhooks-tr')).toContainText('Webhook URL:');

        const button = group.getByRole('button', {name: 'Set up webhooks'});
        const nonce = await button.getAttribute('data-nonce');

        expect(nonce).toBeTruthy();

        await button.click();

        await expect(modal(page, 'success').locator('.give-modal__title')).toHaveText(CREATED.title);
        expect(setup.requests).toEqual([{action: SETUP_ACTION, nonce}]);
    });
});

test.describe('Stripe Webhooks settings group button while the request runs', () => {
    test('is disabled until the response arrives and enabled again after', async ({page, admin}) => {
        const setup = await fakeWebhookSetup(page, [CREATED]);
        const release = setup.hold();

        await admin.visitAdminPage('edit.php', STRIPE_SETTINGS + '&group=webhooks');

        const button = webhooksGroup(page).getByRole('button', {name: 'Set up webhooks'});

        await button.click();
        await expect(modal(page, 'notice')).toContainText('Setting up your Stripe webhook');
        await expect(button).toBeDisabled();

        release();

        await expect(modal(page, 'success').locator('.give-modal__title')).toHaveText(CREATED.title);
        await expect(button).toBeEnabled();
        expect(setup.requests).toHaveLength(1);
    });
});

test.describe('Stripe webhook health email setting', () => {
    /*
     * The option is one key in `give_settings`. Removing it puts the site back on the default, whatever
     * an earlier run saved.
     */
    test.beforeEach(() => resetHealthEmailSetting());
    test.afterEach(() => resetHealthEmailSetting());

    test('defaults to Enabled and keeps Disabled once saved', async ({page, admin}) => {
        await visitWebhooksGroup(admin);

        await expect(healthEmailOption(page, 'Enabled')).toBeChecked();

        await healthEmailOption(page, 'Disabled').check();
        await page.getByRole('button', {name: 'Save changes'}).click();
        /*
         * The saved notice is only in the page the save returns. This screen keeps it hidden, so the check
         * is for its presence.
         */
        await expect(page.getByText('Your settings have been saved.').first()).toBeAttached();

        await visitWebhooksGroup(admin);

        await expect(healthEmailOption(page, 'Disabled')).toBeChecked();
        expect(wp('eval', `echo give_get_option('${HEALTH_EMAIL_SETTING}');`)).toBe('disabled');
    });
});

/**
 * Answers the webhook setup request in the browser with `answers`, in order, and records each request.
 *
 * Every other admin-ajax call, heartbeat included, goes to the site as usual. A request past the end of
 * `answers` gets the last one again. `hold()` keeps the next answers back until the function it returns
 * is called, for asserting on the loader or closing it while the request is out.
 */
async function fakeWebhookSetup(
    page: Page,
    answers: FakeAnswer[]
): Promise<{requests: SetupRequest[]; hold: () => () => void}> {
    const requests: SetupRequest[] = [];
    let gate: Promise<void> = Promise.resolve();

    await page.route('**/wp-admin/admin-ajax.php', async (route) => {
        const request = route.request();
        const body = request.postData();

        if (request.method() !== 'POST' || !isSetupRequest(body)) {
            await route.fallback();

            return;
        }

        requests.push({action: formField(body, 'action'), nonce: formField(body, '_ajax_nonce')});

        const answer = answers[Math.min(requests.length, answers.length) - 1];

        await gate;

        if (answer === SERVER_ERROR) {
            await route.fulfill({
                status: 500,
                contentType: 'text/html',
                body: '<p>There has been a critical error.</p>',
            });

            return;
        }

        await route.fulfill({json: {success: true, data: answer}});
    });

    return {
        requests,
        hold: () => {
            let release: () => void = () => {};
            gate = new Promise((resolve) => (release = resolve));

            return release;
        },
    };
}

/**
 * Whether a request body is the webhook setup request.
 */
function isSetupRequest(body: string | null): boolean {
    return formField(body, 'action') === SETUP_ACTION;
}

/**
 * Reads one field out of a multipart body. The page sends FormData, which the browser encodes as
 * multipart/form-data rather than as a query string.
 */
function formField(body: string | null, name: string): string | null {
    const match = body?.match(new RegExp(`name="${name}"\\r\\n\\r\\n([^\\r\\n]*)`));

    return match ? match[1] : null;
}

/**
 * Visits the page Stripe Connect sends the admin back to after an account is added.
 */
async function visitAfterAccountAdded(admin: Admin): Promise<void> {
    await admin.visitAdminPage('edit.php', `${STRIPE_SETTINGS}&stripe_account=connected`);
}

async function visitWebhooksGroup(admin: Admin): Promise<void> {
    await admin.visitAdminPage('edit.php', `${STRIPE_SETTINGS}&group=webhooks`);
}

/**
 * The open Give modal of one level. The loader is the `notice` level.
 */
function modal(page: Page, level: 'notice' | 'success' | 'warning' | 'error'): Locator {
    return page.locator(`.mfp-wrap .give-modal--${level}`);
}

function retryButton(page: Page): Locator {
    return page.locator('.mfp-wrap .give-stripe-webhook-retry-button');
}

function webhooksGroup(page: Page): Locator {
    return page.locator('#give-settings-section-group-webhooks');
}

/**
 * Other groups in the same form have Enabled and Disabled radios of their own, so this one is found
 * through its input name.
 */
function healthEmailOption(page: Page, label: 'Enabled' | 'Disabled'): Locator {
    return webhooksGroup(page).locator(`input[name="${HEALTH_EMAIL_SETTING}"][value="${label.toLowerCase()}"]`);
}

function resetHealthEmailSetting(): void {
    wp('eval', `give_delete_option('${HEALTH_EMAIL_SETTING}');`);
}
