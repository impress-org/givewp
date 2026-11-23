<?php

namespace Give\Tests\Unit\Receipt;

use Give\Donations\Models\Donation;
use Give\Receipt\DonationReceipt;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class DonationReceiptBillingAddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testShowsTheBillingAddressWhenTheDonationHasOne(): void
    {
        $donationId = Donation::factory()->create()->id;

        give_update_payment_meta($donationId, '_give_donor_billing_address1', '123 Main St');
        give_update_payment_meta($donationId, '_give_donor_billing_city', 'Austin');
        give_update_payment_meta($donationId, '_give_donor_billing_state', 'TX');
        give_update_payment_meta($donationId, '_give_donor_billing_zip', '78701');
        give_update_payment_meta($donationId, '_give_donor_billing_country', 'US');

        $lineItems = (new DonationReceipt($donationId))[DonationReceipt::DONORSECTIONID]->getLineItems();

        $this->assertArrayHasKey('billingAddress', $lineItems);
        $this->assertSame("123 Main St\r\nAustin, TX78701\r\nUS", $lineItems['billingAddress']->value);
    }

    /**
     * @since TBD
     */
    public function testHidesTheBillingAddressWhenTheDonationHasNone(): void
    {
        $donationId = Donation::factory()->create()->id;

        $lineItems = (new DonationReceipt($donationId))[DonationReceipt::DONORSECTIONID]->getLineItems();

        $this->assertArrayNotHasKey('billingAddress', $lineItems);
    }

    /**
     * @since TBD
     */
    public function testHidesTheBillingAddressWhenItIsOnlyMarkup(): void
    {
        $donationId = Donation::factory()->create()->id;

        give_update_payment_meta($donationId, '_give_donor_billing_address1', '<b></b>');
        give_update_payment_meta($donationId, '_give_donor_billing_city', '<style>p{color:red}</style>');

        $lineItems = (new DonationReceipt($donationId))[DonationReceipt::DONORSECTIONID]->getLineItems();

        $this->assertArrayNotHasKey('billingAddress', $lineItems);
    }
}
