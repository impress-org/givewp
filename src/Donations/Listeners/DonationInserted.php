<?php

namespace Give\Donations\Listeners;

use Give\Donations\Models\Donation;
use Give\Helpers\Hooks;
use Give\PaymentGateways\DataTransferObjects\GiveInsertPaymentData;

class DonationInserted {
    /**
     * @unreleased
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        $donor = $donation->donor();

        $giveInsertPaymentData = GiveInsertPaymentData::fromArray([
            'price' =>  $donation->getMinorAmount(),
            'formTitle' => $donation->formTitle,
            'formId' => $donation->formId,
            'priceId' => give_get_price_id( $donation->formId, $donation->getMinorAmount()->getAmount() ),
            'date' => $donation->createdAt,
            'donorEmail' => $donor->email,
            'purchaseKey' => $this->getPurchaseKey( $donation->email ),
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
        Hooks::dispatch('give_insert_payment', $donation->id, $giveInsertPaymentData->toArray());
    }

    /**
	 * @since 1.0.0
	 *
	 * @param  string  $email
	 *
	 * @return string
	 */
	private function getPurchaseKey( $email ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

		return strtolower( md5( $email . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'give', true ) ) );
	}
}
