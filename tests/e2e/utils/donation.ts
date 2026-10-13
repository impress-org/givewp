import {wp} from './wp-cli';

/**
 * Records a completed test donation to `formId` and returns its id.
 *
 * The admin screens that list and edit donations need donations to look at, and driving the donate
 * flow for each one would spend most of a spec on the thing `donation-forms.spec.ts` already covers.
 * There is a REST route that creates a donation, but it wants an existing donor and no route
 * creates one, so this goes through the models over WP-CLI - the same fallback `legacy-form.ts`
 * uses for what has no route. The model writes the revenue row the way a real donation does, so
 * the list endpoints see it.
 */
export function createDonation(formId: number): number {
    const id = wp(
        'eval',
        `$donor = \\Give\\Donors\\Models\\Donor::create([
            'name' => 'Eve Explorer', 'firstName' => 'Eve', 'lastName' => 'Explorer',
            'email' => 'eve' . time() . rand(100, 999) . '@example.test',
        ]);
        $donation = \\Give\\Donations\\Models\\Donation::create([
            'type' => \\Give\\Donations\\ValueObjects\\DonationType::SINGLE(),
            'status' => \\Give\\Donations\\ValueObjects\\DonationStatus::COMPLETE(),
            'mode' => \\Give\\Donations\\ValueObjects\\DonationMode::TEST(),
            'gatewayId' => 'manual',
            'amount' => \\Give\\Framework\\Support\\ValueObjects\\Money::fromDecimal('12.00', 'USD'),
            'donorId' => $donor->id,
            'firstName' => 'Eve', 'lastName' => 'Explorer', 'email' => $donor->email,
            'formId' => ${formId},
        ]);
        echo $donation->id;`
    );

    return Number(id);
}
