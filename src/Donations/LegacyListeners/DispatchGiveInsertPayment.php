<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Helpers\Hooks;
use Give\PaymentGateways\DataTransferObjects\GiveInsertPaymentData;

class DispatchGiveInsertPayment
{
    /**
     * @since 2.19.6
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        $donor = $donation->donor;

        $giveInsertPaymentData = GiveInsertPaymentData::fromArray([
            'price' => $donation->getMinorAmount(),
            'formTitle' => $donation->formTitle,
            'formId' => $donation->formId,
            'priceId' => give_get_price_id($donation->formId, $donation->getMinorAmount()->getAmount()),
            'date' => $donation->createdAt,
            'donorEmail' => $donor->email,
            'purchaseKey' => $donation->purchaseKey,
            'currency' => $donation->currency,
            'paymentGateway' => $donation->gateway,
            'userInfo' => [
                'id' => $donor->id,
                'firstName' => $donor->firstName,
                'lastName' => $donor->lastName,
                'title' => $donor->prefix,
                'email' => $donor->email
            ],
        ]);

         /**
         * @deprecated
         */
        Hooks::doAction('give_insert_payment', $donation->id, $giveInsertPaymentData->toArray());
    }
}
