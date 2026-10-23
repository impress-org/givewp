import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {donationForm, expectReceipt, fillDonorDetails, payWithTestGateway, waitForForm} from './utils/donation-form';
import {editForm} from './utils/form';
import {createStandaloneForm} from './utils/standalone-form';
import {wp} from './utils/wp-cli';

/**
 * The Recurring add-on, installed from its latest GitHub release by the E2E workflow.
 *
 * Like the Peer-to-Peer spec, this checks the add-on is really active and that the Subscriptions
 * screen it adds to the menu still mounts core's list table.
 * It also takes a subscription on a form with no campaign: Recurring creates subscriptions and
 * renewals through core, and core looks up the campaign on that path, so a standalone form is where
 * a lookup that returns nothing would show.
 */
const SLUG = 'give-recurring';

test.describe('Recurring add-on', () => {
    test.skip(!(process.env.E2E_ADDONS ?? '').split(/\s+/).includes(SLUG), `E2E_ADDONS does not name ${SLUG}`);

    test('is active', () => {
        expect(wp('plugin', 'get', SLUG, '--field=status')).toBe('active');
    });

    test('subscriptions admin page mounts', async ({page, admin}) => {
        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-subscriptions');

        await expect(page.getByRole('heading', {level: 1, name: 'Subscriptions'})).toBeVisible({timeout: 15_000});
    });

    test('a form with no campaign takes a monthly subscription', async ({page, admin, requestUtils}) => {
        const {formId} = await createStandaloneForm(page, admin);

        // One billing period and no one-time option makes the donation recurring without a choice.
        await editForm(requestUtils, formId, (form) => {
            const amount = form.blocks
                .flatMap((block) => block.innerBlocks ?? [])
                .find((block) => block.name === 'givewp/donation-amount');

            Object.assign(amount.attributes, {
                recurringEnabled: true,
                recurringBillingPeriodOptions: ['month'],
                recurringEnableOneTimeDonations: false,
            });
        });

        await page.context().clearCookies();
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);

        await form.getByRole('button', {name: 'Donate now'}).click();
        await form.getByRole('button', {name: 'Continue'}).click();

        await fillDonorDetails(form);
        await form.getByRole('button', {name: 'Continue'}).click();

        await payWithTestGateway(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        await expectReceipt(form, '$10.00');

        // The v3 donate route stores no campaign as NULL and the legacy one as 0, so read either as 0.
        const subscription = wp(
            'eval',
            `$subscription = \\Give\\Subscriptions\\Models\\Subscription::query()->where('product_id', ${formId})->get();
            echo $subscription ? $subscription->status->getValue() . ':' . (int) $subscription->campaignId : 'none';`
        );

        expect(subscription).toBe('active:0');
    });
});
