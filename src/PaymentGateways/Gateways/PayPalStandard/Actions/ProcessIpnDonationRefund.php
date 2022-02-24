<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Actions;

use Give\ValueObjects\Money;
use Give_Payment;
use stdClass;

/**
 * @since 2.19.0
 */
class ProcessIpnDonationRefund
{
    /**
     * @since 2.19.0
     *
     * @param stdClass $ipnEventData
     * @param int $donationId
     *
     * @return void
     */
    public function __invoke(stdClass $ipnEventData, $donationId)
    {
        $donation = new Give_Payment($donationId);
        if ($this->isPartialRefund($ipnEventData->mc_gross, $donation->currency, $donation->total)) {
            $donation->add_note(
                sprintf( /* translators: %s: Paypal parent transaction ID */
                    __('Partial PayPal refund processed: %s', 'give'),
                    $ipnEventData->parent_txn_id
                )
            );
        } else {
            $donation->add_note(
                sprintf( /* translators: 1: Paypal parent transaction ID 2. Paypal reason code */
                    __('PayPal Payment #%1$s Refunded for reason: %2$s', 'give'),
                    $ipnEventData->parent_txn_id,
                    $ipnEventData->reason_code
                )
            );

            $donation->add_note(
                sprintf( /* translators: %s: Paypal transaction ID */
                    __('PayPal Refund Transaction ID: %s', 'give'),
                    $ipnEventData->txn_id
                )
            );

            $donation->update_status('refunded');
        }
    }

    /**
     * @since 2.19.0
     *
     * @param string $refundedAmount
     * @param $currency
     * @param $donationAmount
     *
     * @return bool
     */
    protected function isPartialRefund($refundedAmount, $currency, $donationAmount)
    {
        $donationAmount = Money::of($donationAmount, $currency);
        $refundedAmountOnPayPal = Money::of(
        // PayPal Standard sends negative amount when refund payment.
        // Check details https://developer.paypal.com/api/nvp-soap/ipn/IPNandPDTVariables/
            $refundedAmount * -1,
            $currency
        );

        return $refundedAmountOnPayPal->getMinorAmount() < $donationAmount->getMinorAmount();
    }
}
