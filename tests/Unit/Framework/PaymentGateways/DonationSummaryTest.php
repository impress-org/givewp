<?php

namespace unit\tests\Framework\PaymentGateways;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\DonationSummary;
use Give_Helper_Payment;

/**
 * @since 2.19.0
 */
class DonationSummaryTest extends \Give\Tests\TestCase
{
    /** @test */
    public function it_summarizes_a_simple_donation()
    {
        $donationId = Give_Helper_Payment::create_simple_payment();
        $donation = Donation::find($donationId);

        $summary = new DonationSummary($donation);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($donation), false),
            $summary->getSummary()
        );
    }

    /** @test */
    public function it_summarizes_a_multilevel_donation()
    {
        $donationId = Give_Helper_Payment::create_multilevel_payment(['result_type' => 'object']);
        $donation = Donation::find($donationId);
        $donation->levelId = '2';

        $donation->save();

        $summary = new DonationSummary($donation);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($donation), false),
            $summary->getSummary()
        );
    }

    /** @test */
    public function it_summarizes_a_donation_with_donor_name_and_email()
    {
        $donationId = Give_Helper_Payment::create_simple_payment();
        $donation = Donation::find($donationId);
        $donation->firstName = 'Tester';
        $donation->lastName = 'Test';
        $donation->email = 'tester@test.test';

        $donation->save();

        $summary = new DonationSummary($donation);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($donation), true),
            $summary->getSummaryWithDonor()
        );
    }

    /** @test */
    public function it_summarizes_a_donation_with_filter()
    {
        $donationId = Give_Helper_Payment::create_simple_payment();
        $donation = Donation::find($donationId);

        add_filter('give_payment_gateway_donation_summary', function ($summary) {
            return 'FILTERED SUMMARY';
        });

        $summary = new DonationSummary($donation);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($donation), false),
            $summary->getSummary()
        );
    }

    /** @test */
    public function it_summarizes_a_donation_with_donor_filtered_once()
    {
        $donationId = Give_Helper_Payment::create_simple_payment();
        $donation = Donation::find($donationId);

        $count = 0;
        add_filter('give_payment_gateway_donation_summary', function ($summary) use(&$count) {
            $count += 1;
            return $summary;
        });

        $summary = (new DonationSummary($donation))->getSummaryWithDonor();

        $this->assertEquals(1,$count);
    }

    /** @test */
    public function it_summarizes_a_simple_donation_truncated()
    {
        $length = 10;
        $donationId = Give_Helper_Payment::create_simple_payment();
        $donation = Donation::find($donationId);

        $summary = new DonationSummary($donation);
        $summary->setLength($length);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($donation), false, $length),
            $summary->getSummary()
        );
    }

    public function get_legacy_donation_data(Donation $donation)
    {
        $formId = give_get_payment_form_id($donation->id);
        return [
            'source_id' => 'pm_1234',
            'donation_id' => $donation->id,
            'post_data' => [
                'give-form-title' => get_the_title($formId),
                'give-form-id' => $formId,
                'give-price-id' => $donation->levelId,
            ],
            'user_info' => [
                'first_name' => $donation->firstName,
                'last_name' => $donation->lastName,
            ],
            'user_email' => $donation->email,
        ];
    }
}
