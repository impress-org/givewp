<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Provides support for the legacy give_pre_insert_payment hook
 *
 * @since 2.20.0
 */
class DispatchGivePreInsertPayment
{
    public function __invoke(Donation $donation)
    {
        if ( !has_filter('give_pre_insert_payment') ) {
            return;
        }

        $donor = Donor::find($donation->donorId);

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
        $donation->donorId = $paymentData['donor_id'];

        // It's possible in the old hook to set donor attributes as well. We don't really
        // want to encourage this moving forward, but this preserves backwards compatibility.
        $donor = $donation->donor;
        $donor->userId = $paymentData['userInfo']['id'];
        $donor->firstName = $paymentData['userInfo']['firstName'];
        $donor->lastName = $paymentData['userInfo']['lastName'];
        $donor->prefix = $paymentData['userInfo']['title'];
        $donor->email = $paymentData['userInfo']['email'];
        $donor->save();
    }
}
