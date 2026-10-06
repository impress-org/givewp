<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Actions;

use Exception;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;
use Give_Payment;
use stdClass;

/**
 * @since 2.19.0
 */
class ProcessIpnDonationRefund
{
    /**
     * @since TBD Skip refunds with a missing, non-numeric, non-negative, or over-total amount.
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
        $refundedAmount = $this->getRefundedAmount($ipnEventData, $donation->currency);
        $donationAmount = Money::fromDecimal($donation->total, $donation->currency);

        if ( ! $refundedAmount || ! $this->isValidRefundAmount($refundedAmount, $donationAmount)) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => sprintf(
                        'Refund amount (%s) for donation #%d is not valid.',
                        $ipnEventData->mc_gross ?? '(not set)',
                        $donationId
                    ),
                    'Event Data' => $ipnEventData,
                ]
            );

            return;
        }

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
     * @since TBD Replace the deprecated Give\ValueObjects\Money with the framework Money.
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
        /*
         * PayPal Standard sends negative amount when refund payment.
         * Check details https://developer.paypal.com/api/nvp-soap/ipn/IPNandPDTVariables/
         */
        $refundedAmountOnPayPal = Money::fromDecimal($refundedAmount, $currency)->absolute();

        return $refundedAmountOnPayPal->lessThan(Money::fromDecimal($donationAmount, $currency));
    }

    /**
     * @since TBD
     *
     * @param stdClass $ipnEventData PayPal IPN data.
     * @param string   $currency     Donation currency code.
     *
     * @return Money|null The IPN mc_gross in the donation currency, or null when it is missing or not a decimal amount.
     */
    private function getRefundedAmount(stdClass $ipnEventData, string $currency): ?Money
    {
        try {
            return Money::fromDecimal($ipnEventData->mc_gross ?? '', $currency);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * PayPal Standard sends refunds as a negative amount that cannot exceed the donation total.
     *
     * @since TBD
     *
     * @param Money $refundedAmount IPN mc_gross amount.
     * @param Money $donationAmount Donation total.
     *
     * @return bool
     */
    private function isValidRefundAmount(Money $refundedAmount, Money $donationAmount): bool
    {
        return $refundedAmount->isNegative() && $refundedAmount->absolute()->lessThanOrEqual($donationAmount);
    }
}
