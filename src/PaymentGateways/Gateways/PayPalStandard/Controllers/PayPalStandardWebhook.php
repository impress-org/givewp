<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Controllers;

use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookRegister;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookValidator;

/**
 * This class use to handle PayPal ipn.
 *
 * @since 2.19.0
 */
class PayPalStandardWebhook
{

    /**
     * @var WebhookValidator
     */
    private $webhookValidator;

    public function __construct(WebhookValidator $webhookValidator)
    {
        $this->webhookValidator = $webhookValidator;
    }

    /**
     * Handle PayPal ipn
     *
     * @since 2.19.0
     * @since 2.19.3 Respond with 200 http status to ipn.
     * @since 4.16.6.1 Add IPN event-data validation before processing.
     * @since TBD Default the transaction type when the IPN doesn't include one.
     */
    public function handle()
    {
        $eventData = file_get_contents('php://input');
        $eventData = wp_parse_args($eventData);

        if ( ! $this->webhookValidator->verifyEventSignature($eventData)) {
            exit();
        }

        $donationId = isset($eventData['custom']) ? absint($eventData['custom']) : 0;
        $txnType = $eventData['txn_type'] ?? '';

        // ipn verification can be disabled in GiveWP (<=2.15.0).
        // This check will prevent anonymous requests from editing donation, if ipn verification disabled.
        if ( ! $this->verifyDonationId($donationId)) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'Donation id (from IPN) does not exist.',
                    'Event Data' => $eventData,
                ]
            );
            exit();
        }

        if ( ! $this->verifyEventData($eventData, $donationId, $txnType)) {
            exit();
        }

        $this->recordIpn($eventData, $donationId);
        $this->recordIpnInDonation($donationId);

        /* @var WebhookRegister $webhookRegisterer */
        $webhookRegisterer = give(WebhookRegister::class);
        if ($webhookRegisterer->hasEventRegistered($txnType)) {
            $webhookRegisterer->getEventHandler($txnType)->processEvent((object)$eventData);
        }

        $this->supportLegacyActions($txnType, $eventData, $donationId);

        exit;
    }

    /**
     * @since 2.19.0
     *
     * @param int   $donationId
     *
     * @param array $eventData
     */
    private function recordIpn(array $eventData, $donationId)
    {
        update_option(
            'give_last_paypal_ipn_received',
            [
                'auth_status' => 'VERIFIED',
                'transaction_id' => isset($eventData['txn_id']) ? $eventData['txn_id'] : 'N/A',
                'payment_id' => $donationId,
            ],
            false
        );
    }

    /**
     * @since 2.19.0
     *
     * @param int $donationId
     */
    private function recordIpnInDonation($donationId)
    {
        $currentTimestamp = current_time('timestamp');

        give_insert_payment_note(
            $donationId,
            sprintf(
                __('IPN received on %1$s at %2$s', 'give'),
                date_i18n('m/d/Y', $currentTimestamp),
                date_i18n('H:i', $currentTimestamp)
            )
        );

        give_update_meta($donationId, 'give_last_paypal_ipn_received', $currentTimestamp);
    }

    /**
     * @param $donationId
     *
     * @return bool
     */
    private function verifyDonationId($donationId)
    {
        return $donationId && PayPalStandard::id() === give_get_payment_gateway($donationId);
    }

    /**
     * @since 2.19.0
     *
     * @param string $txnType
     * @param array  $eventData
     * @param int    $donationId
     *
     * @return void
     */
    private function supportLegacyActions($txnType, array $eventData, $donationId)
    {
        if (has_action('give_paypal_' . $txnType)) {
            /**
             * Fires while processing PayPal IPN $txnType.
             *
             * Allow PayPal IPN types to be processed separately.
             *
             * @since 1.0
             *
             * @param array $eventData  Encoded data.
             * @param int   $donationId donation id.
             */
            do_action("give_paypal_{$txnType}", $eventData, $donationId);
        } else {
            /**
             * Fires while process PayPal IPN.
             *
             * Fallback to web accept just in case the txn_type isn't present.
             *
             * @since 1.0
             *
             * @param array $eventData  Encoded data.
             * @param int   $donationId donation id.
             */
            do_action('give_paypal_web_accept', $eventData, $donationId);
        }
    }

    /**
     * @since TBD Pass the transaction type to the payment amount check, link refunds to renewals, and verify refund amounts.
     * @since 4.16.6.1
     */
    private function verifyEventData(array $eventData, int $donationId, $txnType): bool
    {
        $paymentStatus = strtolower($eventData['payment_status'] ?? '');

        if ( ! $this->verifyReceiverEmail($eventData)) {
            return false;
        }

        if (in_array($paymentStatus, ['completed', 'pending'], true)) {
            if ( ! $this->verifyPaymentAmount($eventData, $donationId, $txnType)) {
                return false;
            }
        }

        if (in_array($paymentStatus, ['refunded', 'reversed'], true)) {
            $refundedDonation = $this->findRefundedDonation($eventData, $donationId);

            if ( ! $refundedDonation || ! $this->verifyRefundAmount($eventData, $refundedDonation)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @since TBD Reject the IPN when the site PayPal email or both IPN merchant emails are missing.
     * @since 4.16.6.1
     */
    private function verifyReceiverEmail(array $eventData)
    {
        $sitePaypalEmail = trim((string) give_get_option('paypal_email', ''));
        if ($sitePaypalEmail === '') {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'The site PayPal email is not configured, so the IPN merchant cannot be verified.',
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        $receiverEmail = strtolower(trim((string) ($eventData['receiver_email'] ?? '')));
        $business = strtolower(trim((string) ($eventData['business'] ?? '')));
        $siteEmail = strtolower($sitePaypalEmail);

        if ($receiverEmail === '' && $business === '') {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'IPN receiver_email and business are both missing, so the IPN merchant cannot be verified.',
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        if ($receiverEmail !== $siteEmail && $business !== $siteEmail) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => sprintf(
                        'IPN receiver_email (%s) / business (%s) does not match the site PayPal email (%s).',
                        $eventData['receiver_email'] ?? '(not set)',
                        $eventData['business'] ?? '(not set)',
                        $sitePaypalEmail
                    ),
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @since TBD Compare against the gross amount charged by PayPal, which includes recovered fees.
     * @since 4.16.6.1
     *
     * @param array  $eventData  PayPal IPN data.
     * @param int    $donationId Donation ID from the IPN "custom" field.
     * @param string $txnType    PayPal IPN transaction type.
     *
     * @return bool
     */
    private function verifyPaymentAmount(array $eventData, $donationId, $txnType = '')
    {
        try {
            $donation = Donation::find($donationId);

            if ( ! $donation) {
                Log::error(
                    'PayPal Standard IPN Error',
                    [
                        'Message' => sprintf(
                            'Donation #%d not found.',
                            $donationId
                        ),
                        'Event Data' => $eventData,
                    ]
                );

                return false;
            }

            $currency = strtoupper(trim((string) ($eventData['mc_currency'] ?? '')));
            $donationCurrency = strtoupper(trim($donation->amount->getCurrency()->getCode()));

            if ($currency !== $donationCurrency) {
                Log::error(
                    'PayPal Standard IPN Error',
                    [
                        'Message' => sprintf(
                            'IPN currency (%s) does not match donation #%d currency (%s).',
                            $currency,
                            $donationId,
                            $donationCurrency
                        ),
                        'Event Data' => $eventData,
                    ]
                );

                return false;
            }

            $ipnAmount = Money::fromDecimal((float)($eventData['mc_gross'] ?? 0), $currency);
            $chargedAmounts = $this->getChargedAmounts($donation, $txnType);
            $matchingAmounts = array_filter($chargedAmounts, static function (Money $chargedAmount) use ($ipnAmount) {
                return $ipnAmount->equals($chargedAmount);
            });

            if ( ! $matchingAmounts) {
                Log::error(
                    'PayPal Standard IPN Error',
                    [
                        'Message' => sprintf(
                            'IPN amount (%s %s) does not match donation #%d amount (%s %s).',
                            $eventData['mc_gross'] ?? '0',
                            $currency,
                            $donationId,
                            implode(' or ', array_map(static function (Money $chargedAmount) {
                                return $chargedAmount->formatToDecimal();
                            }, $chargedAmounts)),
                            $donationCurrency
                        ),
                        'Event Data' => $eventData,
                    ]
                );

                return false;
            }
        } catch (\Exception $e) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'Failed to compare IPN amount to donation amount.',
                    'Exception' => $e->getMessage(),
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * Subscription payments reference the initial donation through "custom", but PayPal charges them
     * the subscription amount, which can differ from the initial donation amount.
     *
     * @since TBD
     *
     * @param Donation $donation Donation referenced by the IPN.
     * @param string   $txnType  PayPal IPN transaction type.
     *
     * @return Money[] Amounts PayPal was asked to charge for this donation.
     */
    private function getChargedAmounts(Donation $donation, $txnType): array
    {
        $chargedAmounts = [$donation->amount];

        if ('subscr_payment' === $txnType) {
            $subscription = $donation->subscription()->get();

            if ($subscription) {
                $chargedAmounts[] = $subscription->amount;
            }
        }

        return $chargedAmounts;
    }

    /**
     * Refunds of subscription renewals reference the initial donation through "custom", while
     * "parent_txn_id" holds the transaction ID of the renewal being refunded.
     *
     * @since TBD Renamed from verifyParentTransactionId(). Require parent_txn_id and accept renewals of the same subscription.
     * @since 4.16.6.1
     *
     * @param array $eventData  PayPal IPN data.
     * @param int   $donationId Donation ID from the IPN "custom" field.
     *
     * @return Donation|null The donation being refunded or reversed, or null when the IPN can't be linked to it.
     */
    private function findRefundedDonation(array $eventData, int $donationId): ?Donation
    {
        $parentTxnId = trim((string) ($eventData['parent_txn_id'] ?? ''));
        if ($parentTxnId === '') {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => sprintf(
                        'IPN payment_status is %s but parent_txn_id is missing for donation #%d.',
                        strtolower($eventData['payment_status'] ?? ''),
                        $donationId
                    ),
                    'Event Data' => $eventData,
                ]
            );

            return null;
        }

        $donation = Donation::find($donationId);
        $storedTxnId = $donation ? trim((string) $donation->gatewayTransactionId) : '';

        if ($storedTxnId === '') {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => sprintf(
                        'IPN payment_status is %s but donation #%d has no stored transaction ID — cannot process a refund for a donation that was never completed.',
                        strtolower($eventData['payment_status'] ?? ''),
                        $donationId
                    ),
                    'Event Data' => $eventData,
                ]
            );

            return null;
        }

        if ($parentTxnId === $storedTxnId) {
            return $donation;
        }

        $renewal = give()->donations->getByGatewayTransactionId($parentTxnId);
        $subscription = $donation->subscription()->get();

        if ($renewal && $subscription && $renewal->type->isRenewal() && $renewal->subscriptionId === $subscription->id) {
            return $renewal;
        }

        Log::error(
            'PayPal Standard IPN Error',
            [
                'Message' => sprintf(
                    'IPN parent_txn_id (%s) does not match donation #%d stored transaction ID (%s) or any of its renewals.',
                    $parentTxnId,
                    $donationId,
                    $storedTxnId
                ),
                'Event Data' => $eventData,
            ]
        );

        return null;
    }

    /**
     * PayPal reports refunds and reversals with a negative mc_gross. Partial refunds are allowed.
     *
     * @since TBD
     *
     * @param array    $eventData        PayPal IPN data.
     * @param Donation $refundedDonation Donation being refunded or reversed.
     *
     * @return bool
     */
    private function verifyRefundAmount(array $eventData, Donation $refundedDonation): bool
    {
        $currency = strtoupper(trim((string) ($eventData['mc_currency'] ?? '')));
        $refundAmount = $eventData['mc_gross'] ?? '';

        try {
            if (is_numeric($refundAmount) && $currency === $refundedDonation->amount->getCurrency()->getCode()) {
                $refundAmount = Money::fromDecimal($refundAmount, $currency);

                if ($refundAmount->isNegative() && $refundAmount->absolute()->lessThanOrEqual($refundedDonation->amount)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'Failed to compare IPN refund amount to donation amount.',
                    'Exception' => $e->getMessage(),
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        Log::error(
            'PayPal Standard IPN Error',
            [
                'Message' => sprintf(
                    'IPN refund amount (%s %s) is not valid for donation #%d (%s %s).',
                    $eventData['mc_gross'] ?? '(not set)',
                    $currency,
                    $refundedDonation->id,
                    $refundedDonation->amount->formatToDecimal(),
                    $refundedDonation->amount->getCurrency()->getCode()
                ),
                'Event Data' => $eventData,
            ]
        );

        return false;
    }
}
