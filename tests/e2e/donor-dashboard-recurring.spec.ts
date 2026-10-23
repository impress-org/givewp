import {expect, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {
    createDonorAccount,
    createMultiLevelForm,
    createSubscription,
    EDITABLE_GATEWAY,
    logInAsDonor,
    openRecurringDonations,
    readSubscription,
} from './utils/donor-dashboard';
import {failedCalls, restCallsAfter, watchRestCalls} from './utils/rest';

/**
 * The Recurring Donations tab of the donor dashboard, which the Recurring add-on registers.
 *
 * It is where a donor looks after their own subscriptions: the list, a receipt per subscription,
 * and a manage screen that changes the amount or cancels. Every spec logs in as a donor of its own
 * with subscriptions created for it, so what is on screen is known exactly.
 *
 * Pausing and changing the payment method are not covered: only gateways that call a remote API
 * implement them, and this suite has no account with any of them.
 */
const SLUG = 'give-recurring';

const SUBSCRIPTIONS_ROUTE = '/give-api/v2/donor-dashboard/recurring-donations/subscriptions';

test.describe('Donor dashboard: Recurring Donations', () => {
    test.skip(!(process.env.E2E_ADDONS ?? '').split(/\s+/).includes(SLUG), `E2E_ADDONS does not name ${SLUG}`);

    /*
     * Each fixture and each read-back is a WP-CLI call through wp-env, a few seconds apiece, and a
     * test here makes up to six of them on top of driving the page.
     */
    test.describe.configure({timeout: 120_000});

    let form: {formId: number; title: string};

    test.beforeAll(() => {
        form = createMultiLevelForm();
    });

    /**
     * The subscription rows on the list, one per subscription.
     */
    function rows(page: Page) {
        return page.locator('.give-donor-dashboard-table__row');
    }

    test('lists the donor’s own subscriptions and no one else’s', async ({page}) => {
        const donor = createDonorAccount();
        const otherDonor = createDonorAccount();

        createSubscription({donorId: donor.donorId, formId: form.formId, gatewayId: EDITABLE_GATEWAY});
        createSubscription({donorId: otherDonor.donorId, formId: form.formId, gatewayId: EDITABLE_GATEWAY});

        await logInAsDonor(page, donor);

        const calls = watchRestCalls(page);
        await openRecurringDonations(page);

        await expect(page.locator('.give-donor-dashboard-heading')).toContainText('1 Total Subscriptions');
        await expect(rows(page)).toHaveCount(1);
        await expect(rows(page).locator('.give-donor-dashboard-table__donation-amount')).toContainText(
            '$25.00 / Monthly'
        );
        await expect(rows(page)).toContainText(form.title);
        await expect(rows(page).locator('.give-donor-dashboard-table__donation-status-label')).toHaveText('Active');

        expect(failedCalls(await restCallsAfter(calls, SUBSCRIPTIONS_ROUTE))).toEqual([]);
    });

    test('says so when the donor has no subscriptions', async ({page}) => {
        await logInAsDonor(page, createDonorAccount());
        await openRecurringDonations(page);

        await expect(page.locator('.give-donor-dashboard-heading')).toContainText('No Subscriptions');
        await expect(rows(page)).toHaveCount(0);
    });

    test('opens a subscription’s receipt and comes back to the list', async ({page}) => {
        const donor = createDonorAccount();
        createSubscription({donorId: donor.donorId, formId: form.formId, gatewayId: EDITABLE_GATEWAY});

        await logInAsDonor(page, donor);
        await openRecurringDonations(page);

        await rows(page).getByRole('button', {name: 'View Subscription'}).click();

        await expect(page.locator('.give-donor-dashboard-heading')).toContainText('Subscription #');
        await expect(page.locator('.give-donor-dashboard-donation-receipt__table').first()).toBeVisible();

        await page.getByRole('link', {name: 'Back to Recurring Donations'}).click();

        await expect(rows(page)).toHaveCount(1);
    });

    test('changes the amount of a subscription', async ({page}) => {
        const donor = createDonorAccount();
        const subscriptionId = createSubscription({
            donorId: donor.donorId,
            formId: form.formId,
            gatewayId: EDITABLE_GATEWAY,
            amount: '25.00',
        });

        await logInAsDonor(page, donor);
        await openRecurringDonations(page);

        await rows(page).getByRole('button', {name: 'Manage Subscription'}).click();
        await expect(page.locator('.give-donor-dashboard-heading')).toContainText('Manage Subscription');

        // The react-select version the dashboard ships gives its options no ARIA role, so pick by typing.
        await page.getByLabel('Subscription Amount').fill('50.00');
        await page.keyboard.press('Enter');
        await page.getByRole('button', {name: 'Update Subscription'}).click();

        await expect(page.getByRole('button', {name: 'Updated'})).toBeVisible();

        /*
         * "Updated" shows whatever the route answered, including a gateway that refused the change
         * in the response body, so the stored amount is what says the update went through.
         */
        await expect.poll(() => readSubscription(subscriptionId).amount).toBe('50.00');

        await openRecurringDonations(page);
        await expect(rows(page).locator('.give-donor-dashboard-table__donation-amount')).toContainText(
            '$50.00 / Monthly'
        );
    });

    /*
     * A failing subscription is the one whose donor most needs to replace a card, so it must stay
     * updatable alongside an active one. A paused subscription is resumed before it is changed.
     */
    for (const [status, label, enabled] of [
        ['active', 'Active', true],
        ['failing', 'Failed', true],
        ['paused', 'Paused', false],
    ] as const) {
        test(`${enabled ? 'allows' : 'blocks'} updating when the subscription is ${status}`, async ({page}) => {
            const donor = createDonorAccount();
            createSubscription({donorId: donor.donorId, formId: form.formId, gatewayId: EDITABLE_GATEWAY, status});

            await logInAsDonor(page, donor);
            await openRecurringDonations(page);

            await rows(page).getByRole('button', {name: 'Manage Subscription'}).click();

            await expect(page.locator(`.givewp-dashboard-subscription-status--${status}`)).toHaveText(label);

            const update = page.getByRole('button', {name: 'Update Subscription'});

            if (enabled) {
                await expect(update).toBeEnabled();
            } else {
                await expect(update).toBeDisabled();
            }
        });
    }

    test('cancels a subscription from the manage screen once confirmed', async ({page}) => {
        const donor = createDonorAccount();
        const subscriptionId = createSubscription({
            donorId: donor.donorId,
            formId: form.formId,
            gatewayId: EDITABLE_GATEWAY,
        });

        await logInAsDonor(page, donor);
        await openRecurringDonations(page);

        await rows(page).getByRole('button', {name: 'Manage Subscription'}).click();

        const dialog = page.getByRole('dialog', {name: 'Cancel Subscription'});

        await page.getByRole('button', {name: 'Cancel Subscription'}).click();
        await dialog.getByRole('button', {name: 'Nevermind'}).click();

        await expect(dialog).toBeHidden();
        expect(readSubscription(subscriptionId).status).toBe('active');

        await page.getByRole('button', {name: 'Cancel Subscription'}).click();
        await dialog.getByRole('button', {name: 'Yes, cancel'}).click();

        await expect(dialog).toBeHidden();
        await expect.poll(() => readSubscription(subscriptionId).status).toBe('cancelled');

        await openRecurringDonations(page);
        await expect(rows(page).locator('.give-donor-dashboard-table__donation-status-label')).toHaveText('Cancelled');
        await expect(rows(page).getByRole('button', {name: 'Manage Subscription'})).toHaveCount(0);
    });

    /*
     * A gateway that can cancel but not update gets no manage screen, so the list row carries the
     * Cancel button itself. Test Donation is that gateway.
     */
    test('cancels from the list a subscription it cannot manage', async ({page}) => {
        const donor = createDonorAccount();
        const subscriptionId = createSubscription({donorId: donor.donorId, formId: form.formId, gatewayId: 'manual'});

        await logInAsDonor(page, donor);
        await openRecurringDonations(page);

        await expect(rows(page).getByRole('button', {name: 'Manage Subscription'})).toHaveCount(0);

        await rows(page).getByRole('button', {name: 'Cancel Subscription'}).click();
        await page
            .getByRole('dialog', {name: 'Cancel Subscription'})
            .getByRole('button', {name: 'Yes, cancel'})
            .click();

        await expect.poll(() => readSubscription(subscriptionId).status).toBe('cancelled');
        await expect(rows(page).locator('.give-donor-dashboard-table__donation-status-label')).toHaveText('Cancelled');
    });
});
