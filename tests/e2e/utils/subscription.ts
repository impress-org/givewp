import {wp} from './wp-cli';

/**
 * Records a donor with a monthly subscription to `formId`, its first donation and one renewal, and
 * returns the donor's id.
 *
 * A renewal is stored with a status of its own (`give_subscription`), which is what the admin
 * screens that label a donation have to know about. Making one takes a donor and a subscription,
 * and no REST route creates a donor, so this goes through the models over WP-CLI the way
 * `createDonation` does. Everything is in test mode, like the site the suite runs against.
 */
export function createDonorWithRenewal(formId: number): number {
    const id = wp(
        'eval',
        `$donor = \\Give\\Donors\\Models\\Donor::create([
            'name' => 'Rae Renewal', 'firstName' => 'Rae', 'lastName' => 'Renewal',
            'email' => 'rae' . time() . rand(100, 999) . '@example.test',
        ]);
        $amount = \\Give\\Framework\\Support\\ValueObjects\\Money::fromDecimal('12.00', 'USD');
        $subscription = \\Give\\Subscriptions\\Models\\Subscription::create([
            'donorId' => $donor->id,
            'donationFormId' => ${formId},
            'amount' => $amount,
            'period' => \\Give\\Subscriptions\\ValueObjects\\SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'status' => \\Give\\Subscriptions\\ValueObjects\\SubscriptionStatus::ACTIVE(),
            'mode' => \\Give\\Subscriptions\\ValueObjects\\SubscriptionMode::TEST(),
            'gatewayId' => 'manual',
        ]);
        \\Give\\Donations\\Models\\Donation::create([
            'type' => \\Give\\Donations\\ValueObjects\\DonationType::SUBSCRIPTION(),
            'status' => \\Give\\Donations\\ValueObjects\\DonationStatus::COMPLETE(),
            'mode' => \\Give\\Donations\\ValueObjects\\DonationMode::TEST(),
            'gatewayId' => 'manual',
            'amount' => $amount,
            'donorId' => $donor->id,
            'firstName' => 'Rae', 'lastName' => 'Renewal', 'email' => $donor->email,
            'formId' => ${formId},
            'subscriptionId' => $subscription->id,
        ]);
        $subscription->createRenewal();
        echo $donor->id;`
    );

    return Number(id);
}
