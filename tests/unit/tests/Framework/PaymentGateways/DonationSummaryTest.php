<?php

namespace unit\tests\Framework\PaymentGateways;

use Give\Framework\PaymentGateways\DonationSummary;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\ValueObjects\DonorInfo;
use Give_Helper_Payment;
use Give_Unit_Test_Case;

/**
 * @unreleased
 */
class DonationSummaryTest extends Give_Unit_Test_Case
{
    /** @test */
    public function it_summarizes_a_simple_donation()
    {
        $paymentData = new GatewayPaymentData;
        $paymentData->donorInfo = new DonorInfo();
        $paymentData->donationId = Give_Helper_Payment::create_simple_payment();

        $summary = new DonationSummary($paymentData);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($paymentData), false),
            $summary->getSummary()
        );
    }

    /** @test */
    public function it_summarizes_a_multilevel_donation()
    {
        $paymentData = new GatewayPaymentData;
        $paymentData->donorInfo = new DonorInfo();
        $paymentData->donationId = Give_Helper_Payment::create_multilevel_payment(['result_type' => 'object']);
        $paymentData->priceId = 2;

        $summary = new DonationSummary($paymentData);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($paymentData), false),
            $summary->getSummary()
        );
    }

    /** @test */
    public function it_summarizes_a_donation_with_donor_name_and_email()
    {
        $paymentData = new GatewayPaymentData;
        $paymentData->donorInfo = new DonorInfo();
        $paymentData->donorInfo->firstName = 'Tester';
        $paymentData->donorInfo->lastName = 'Test';
        $paymentData->donorInfo->email = 'tester@test.test';
        $paymentData->donationId = Give_Helper_Payment::create_simple_payment();

        $summary = new DonationSummary($paymentData);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($paymentData), true),
            $summary->getSummaryWithDonor()
        );
    }

    /** @test */
    public function it_summarizes_a_donation_with_filter()
    {
        $paymentData = new GatewayPaymentData;
        $paymentData->donorInfo = new DonorInfo();
        $paymentData->donationId = Give_Helper_Payment::create_simple_payment();

        add_filter('give_payment_gateway_donation_summary', function ($summary) {
            return 'FILTERED SUMMARY';
        });

        $summary = new DonationSummary($paymentData);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($paymentData), false),
            $summary->getSummary()
        );
    }

    /** @test */
    public function it_summarizes_a_simple_donation_truncated()
    {
        $length = 10;
        $paymentData = new GatewayPaymentData;
        $paymentData->donorInfo = new DonorInfo();
        $paymentData->donationId = Give_Helper_Payment::create_simple_payment();

        $summary = new DonationSummary($paymentData);
        $summary->setLength($length);

        $this->assertEquals(
            give_payment_gateway_donation_summary($this->get_legacy_donation_data($paymentData), false, $length),
            $summary->getSummary()
        );
    }

    public function get_legacy_donation_data(GatewayPaymentData $paymentData)
    {
        return [
            'source_id' => 'pm_1234',
            'donation_id' => $paymentData->donationId,
            'post_data' => [
                'give-form-id' => give_get_payment_form_id($paymentData->donationId),
                'give-price-id' => $paymentData->priceId,
            ],
            'user_info' => [
                'first_name' => $paymentData->donorInfo->firstName,
                'last_name' => $paymentData->donorInfo->lastName,
            ],
            'user_email' => $paymentData->donorInfo->email,
        ];
    }
}
