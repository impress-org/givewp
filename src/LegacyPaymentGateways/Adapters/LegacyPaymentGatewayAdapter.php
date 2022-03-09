<?php

namespace Give\LegacyPaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\PaymentGateways\Actions\CreatePaymentAction;
use Give\PaymentGateways\Actions\CreateSubscriptionAction;
use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\DataTransferObjects\GiveInsertPaymentData;
use Give\PaymentGateways\DataTransferObjects\SubscriptionData;

/**
 * Class LegacyPaymentGatewayAdapter
 * @since 2.18.0
 */
class LegacyPaymentGatewayAdapter
{

    /**
     * Get legacy form field markup to display gateway specific payment fields
     *
     * @since 2.18.0
     * @since 2.19.0 Added missing $args parameter for ID prefixing and general backwards compatibility.
     *
     * @param  int  $formId
     * @param  array  $args
     * @param  PaymentGatewayInterface  $registeredGateway
     *
     * @return string|bool
     */
    public function getLegacyFormFieldMarkup($formId, $args, $registeredGateway)
    {
        return $registeredGateway->getLegacyFormFieldMarkup($formId, $args);
    }

    /**
     * First we create a payment, then move on to the gateway processing
     *
     * @since 2.18.0
     * @since 2.19.0 Replace is_recurring with is_donation_recurring to detect recurring donations.
     *
     * @param  array  $legacyDonationData  Legacy Donation Data
     * @param  PaymentGatewayInterface  $registeredGateway
     *
     * @return void
     */
    public function handleBeforeGateway($legacyDonationData, $registeredGateway)
    {
        $formData = FormData::fromRequest($legacyDonationData);

        $this->validateGatewayNonce($formData->gatewayNonce);

        $donationId = $this->createPayment($formData->toGiveInsertPaymentData());

        $gatewayPaymentData = $formData->toGatewayPaymentData($donationId);

        if (give_recurring_is_donation_recurring($formData->legacyDonationData)) {
            $subscriptionData = SubscriptionData::fromRequest($legacyDonationData);
            $subscriptionId = $this->createSubscription($donationId, $formData, $subscriptionData);

            $gatewaySubscriptionData = $subscriptionData->toGatewaySubscriptionData($subscriptionId);

            $registeredGateway->handleCreateSubscription($gatewayPaymentData, $gatewaySubscriptionData);
        }

        $registeredGateway->handleCreatePayment($gatewayPaymentData);
    }

    /**
     * Create the payment
     *
     * @since 2.18.0
     *
     * @param  GiveInsertPaymentData  $giveInsertPaymentData
     *
     * @return int
     */
    private function createPayment(GiveInsertPaymentData $giveInsertPaymentData)
    {
        /** @var CreatePaymentAction $createPaymentAction */
        $createPaymentAction = give(CreatePaymentAction::class);

        return $createPaymentAction($giveInsertPaymentData);
    }

    /**
     * Create the payment
     *
     * @since 2.18.0
     *
     * @param  int  $donationId
     * @param  FormData  $formData
     * @param  SubscriptionData  $subscriptionData
     *
     * @return int
     */
    private function createSubscription($donationId, FormData $formData, SubscriptionData $subscriptionData)
    {
        /** @var CreateSubscriptionAction $createSubscriptionAction */
        $createSubscriptionAction = give(CreateSubscriptionAction::class);

        return $createSubscriptionAction($donationId, $formData, $subscriptionData);
    }

    /**
     * Validate Gateway Nonce
     *
     * @since 2.18.0
     *
     * @param  string  $gatewayNonce
     */
    private function validateGatewayNonce($gatewayNonce)
    {
        if (!wp_verify_nonce($gatewayNonce, 'give-gateway')) {
            wp_die(
                esc_html__(
                    'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.',
                    'give'
                ),
                esc_html__('Error', 'give'),
                ['response' => 403]
            );
        }
    }
}
