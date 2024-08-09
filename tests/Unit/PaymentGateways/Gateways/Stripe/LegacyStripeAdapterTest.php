<?php

use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\ValueObjects\CardInfo;

class LegacyStripeAdapterTest extends \Give\Tests\TestCase
{
    public function testValidatesCardInformationExists()
    {
        $formData = new FormData;
        $formData->paymentGateway = 'stripe';
        $formData->cardInfo = CardInfo::fromArray([
            'name' => 'Tester T. Test',
            'cvc' => '123',
            'expMonth' => '01',
            'expYear' => '99',
            'number' => '4242 4242 4242 4242',
        ]);

        do_action('give_donation_form_processing_start', $formData);

        $this->assertTrue(true); // No exception thrown
    }

    public function testThrowsExceptionForEmptyCardInformation()
    {
        $formData = new FormData;
        $formData->paymentGateway = 'stripe';
        $formData->cardInfo = CardInfo::fromArray([
            'name' => 'Spammer P. Spam',
            'cvc' => '',
            'expMonth' => '',
            'expYear' => '',
            'number' => '',
        ]);

        $this->expectException(WPDieException::class);

        do_action('give_donation_form_processing_start', $formData);
    }
}
