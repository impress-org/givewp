<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;
use stdClass;

/**
 * @since 2.19.0
 */
class ProcessIpnDonationRefund
{
    /**
     * @since TBD Use the Donation model and skip refunds with a missing, non-negative, or over-total amount.
     * @since 2.19.0
     *
     * @param stdClass $ipnEventData PayPal IPN data.
     * @param int      $donationId   ID of the donation being refunded.
     *
     * @return void
     */
    public function __invoke(stdClass $ipnEventData, $donationId)
    {
        $donation = Donation::find($donationId);

        if ( ! $donation) {
            return;
        }

        $refundedAmount = $this->getRefundedAmount($ipnEventData, $donation->amount->getCurrency()->getCode());

        if ( ! $refundedAmount || ! $this->isValidRefundAmount($refundedAmount, $donation->amount)) {
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

        if ($refundedAmount->absolute()->lessThan($donation->amount)) {
            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf( /* translators: %s: Paypal parent transaction ID */
                    __('Partial PayPal refund processed: %s', 'give'),
                    $ipnEventData->parent_txn_id
                ),
            ]);

            return;
        }

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf( /* translators: 1: Paypal parent transaction ID 2. Paypal reason code */
                __('PayPal Payment #%1$s Refunded for reason: %2$s', 'give'),
                $ipnEventData->parent_txn_id,
                $ipnEventData->reason_code
            ),
        ]);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf( /* translators: %s: Paypal transaction ID */
                __('PayPal Refund Transaction ID: %s', 'give'),
                $ipnEventData->txn_id
            ),
        ]);

        $donation->status = DonationStatus::REFUNDED();
        $donation->save();
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
