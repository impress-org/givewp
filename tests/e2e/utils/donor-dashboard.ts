import {expect, Page} from '@wordpress/e2e-test-utils-playwright';
import {wp} from './wp-cli';

/**
 * Fixtures for the donor dashboard's Recurring Donations tab.
 *
 * The dashboard shows the donor tied to the logged-in WordPress user, so every spec needs a user, a
 * donor record pointing at it, and subscriptions that donor owns. No REST route creates a
 * subscription and the `test-donations` WP-CLI command writes rows with no gateway, which leaves
 * every action on the tab switched off, so these go through the models over WP-CLI the way
 * `donation.ts` does.
 */

export type DonorAccount = {login: string; password: string; donorId: number};

export type SubscriptionFixture = {
    donorId: number;
    formId: number;
    gatewayId: 'test-offsite-gateway' | 'manual';
    status?: 'active' | 'failing' | 'paused' | 'cancelled';
    amount?: string;
};

/*
 * The test offsite gateway is the one local gateway whose subscription module edits the amount, so
 * the dashboard renders Manage, the amount select and Update for it and an update really persists.
 * wp-env registers it through GIVEWP_ENABLE_TEST_OFFSITE_GATEWAY in .wp-env.json. The Test
 * Donation gateway (`manual`) can only cancel, which puts the Cancel button on the list row instead.
 */
export const EDITABLE_GATEWAY = 'test-offsite-gateway';

/**
 * A suffix that keeps fixtures from one test apart from every other run against the same site.
 */
function uniqueSuffix(): string {
    return `${Date.now()}${Math.floor(Math.random() * 1000)}`;
}

/**
 * Creates a v2 form with three set levels and returns its id.
 *
 * The manage screen builds its amount options from the form's `_give_donation_levels` meta and
 * renders no select at all for fewer than two, so a v3 form, which stores its amounts in blocks,
 * would leave nothing to choose.
 */
export function createMultiLevelForm(): {formId: number; title: string} {
    const title = `E2E recurring levels ${uniqueSuffix()}`;
    const levels = ['10', '25', '50'].map((amount, index) => ({
        _give_id: {level_id: index},
        _give_amount: `${amount}.000000`,
    }));

    const formId = Number(
        wp(
            'post',
            'create',
            '--post_type=give_forms',
            '--post_status=publish',
            `--post_title=${title}`,
            `--meta_input=${JSON.stringify({_give_price_option: 'multi', _give_donation_levels: levels})}`,
            '--porcelain'
        )
    );

    if (!formId) {
        throw new Error('WP-CLI did not return the id of the multi-level form it was asked to create.');
    }

    return {formId, title};
}

/**
 * Creates a WordPress user with a donor record linked to it.
 */
export function createDonorAccount(): DonorAccount {
    const suffix = uniqueSuffix();
    const login = `e2e-donor-${suffix}`;
    const password = `e2e-pass-${suffix}`;

    const donorId = wp(
        'eval',
        `$userId = wp_insert_user([
            'user_login' => '${login}', 'user_pass' => '${password}',
            'user_email' => '${login}@example.test', 'role' => 'give_donor',
        ]);
        $donor = \\Give\\Donors\\Models\\Donor::create([
            'name' => 'Rae Recurring', 'firstName' => 'Rae', 'lastName' => 'Recurring',
            'email' => '${login}@example.test', 'userId' => $userId,
        ]);
        echo $donor->id;`
    );

    return {login, password, donorId: Number(donorId)};
}

/**
 * Creates a monthly subscription with its parent donation and returns the subscription id.
 *
 * The parent donation is not optional: the legacy subscription the dashboard routes load reads its
 * capabilities through the parent payment, and without one Cancel is refused.
 */
export function createSubscription({
    donorId,
    formId,
    gatewayId,
    status = 'active',
    amount = '25.00',
}: SubscriptionFixture): number {
    const id = wp(
        'eval',
        `$donor = \\Give\\Donors\\Models\\Donor::find(${donorId});
        $subscription = \\Give\\Subscriptions\\Models\\Subscription::create([
            'donorId' => $donor->id,
            'donationFormId' => ${formId},
            'gatewayId' => '${gatewayId}',
            'status' => new \\Give\\Subscriptions\\ValueObjects\\SubscriptionStatus('${status}'),
            'amount' => \\Give\\Framework\\Support\\ValueObjects\\Money::fromDecimal('${amount}', 'USD'),
            'period' => \\Give\\Subscriptions\\ValueObjects\\SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'mode' => \\Give\\Subscriptions\\ValueObjects\\SubscriptionMode::TEST(),
        ]);
        $donation = \\Give\\Donations\\Models\\Donation::create([
            'type' => \\Give\\Donations\\ValueObjects\\DonationType::SUBSCRIPTION(),
            'status' => \\Give\\Donations\\ValueObjects\\DonationStatus::COMPLETE(),
            'mode' => \\Give\\Donations\\ValueObjects\\DonationMode::TEST(),
            'gatewayId' => '${gatewayId}',
            'amount' => $subscription->amount,
            'donorId' => $donor->id,
            'firstName' => $donor->firstName, 'lastName' => $donor->lastName, 'email' => $donor->email,
            'formId' => ${formId},
            'subscriptionId' => $subscription->id,
        ]);
        give()->subscriptions->updateLegacyParentPaymentId($subscription->id, $donation->id);
        echo $subscription->id;`
    );

    return Number(id);
}

/**
 * Reads a subscription's status and amount back from the database.
 *
 * The dashboard reports "Updated" whatever the update route answers, so what is stored is the only
 * proof an action took effect.
 */
export function readSubscription(id: number): {status: string; amount: string} {
    return JSON.parse(
        wp(
            'eval',
            `$subscription = \\Give\\Subscriptions\\Models\\Subscription::find(${id});
            echo json_encode([
                'status' => $subscription->status->getValue(),
                'amount' => $subscription->amount->formatToDecimal(),
            ]);`
        )
    );
}

/**
 * Replaces the admin session every spec starts with by a session for `account`.
 */
export async function logInAsDonor(page: Page, account: DonorAccount): Promise<void> {
    await page.context().clearCookies();
    await page.goto('/wp-login.php');

    await page.locator('#user_login').fill(account.login);
    await page.locator('#user_pass').fill(account.password);
    await page.locator('#wp-submit').click();

    /*
     * Only leaving the login screen matters; waiting for wherever WordPress sends a donor to finish
     * loading would spend the test on a page it never looks at.
     */
    await page.waitForURL((url) => !url.pathname.endsWith('/wp-login.php'), {waitUntil: 'commit'});
}

/**
 * Opens the Recurring Donations tab and waits for the list to replace its loading heading.
 */
export async function openRecurringDonations(page: Page): Promise<void> {
    await page.goto('/?give-embed=donor-dashboard#/recurring-donations');

    await expect(
        page.locator('.give-donor-dashboard-heading', {hasText: /Total Subscriptions|No Subscriptions/})
    ).toBeVisible({timeout: 20_000});
}
