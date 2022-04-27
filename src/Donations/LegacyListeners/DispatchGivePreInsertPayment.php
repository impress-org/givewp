<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Provides support for the legacy give_pre_insert_payment hook
 *
 * @unreleased
 */
class DispatchGivePreInsertPayment
{
    public function __invoke(Donation $donation)
    {
        if ( !has_filter('give_pre_insert_payment') ) {
            return;
        }

        $donor = $donation->donor;

        $paymentData = [
            'price' => $donation->amount->formatToDecimal(),
            'formTitle' => $donation->formTitle,
            'formId' => $donation->formId,
            'priceId' => give_get_price_id($donation->formId, $donation->amount->formatToDecimal()),
            'date' => $donation->createdAt,
            'donorEmail' => $donor->email,
            'purchaseKey' => $donation->purchaseKey,
            'currency' => $donation->amount->getCurrency()->getCode(),
            'gateway' => $donation->gatewayId,
            'status' => $donation->status->getValue(),
            'donor_id' => $donor->id,
            'userInfo' => [
                'id' => $donor->userId,
                'firstName' => $donor->firstName,
                'lastName' => $donor->lastName,
                'title' => $donor->prefix,
                'email' => $donor->email,
            ],
        ];

        $paymentData = apply_filters('give_pre_insert_payment', $paymentData);

        $donation->amount = Money::fromDecimal($paymentData['price'], $paymentData['currency']);
        $donation->status = new DonationStatus($paymentData['status']);
        $donation->formTitle = $paymentData['formTitle'];
        $donation->formId = $paymentData['formId'];
        $donation->purchaseKey = $paymentData['purchaseKey'];
        $donation->gatewayId = $paymentData['gateway'];
        $donation->donor->id = $paymentData['donor_id'];
        $donation->donor->userId = $paymentData['userInfo']['id'];
        $donation->donor->firstName = $paymentData['userInfo']['firstName'];
        $donation->donor->lastName = $paymentData['userInfo']['lastName'];
        $donation->donor->prefix = $paymentData['userInfo']['title'];
        $donation->donor->email = $paymentData['userInfo']['email'];
    }
}
