<?php

namespace Give\LegacyPaymentGateways\Adapters;

use Exception;
use Give\Donors\Models\Donor;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\PaymentGateways\Actions\CreateSubscriptionAction;
use Give\PaymentGateways\DataTransferObjects\FormData;
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
     * @unreleased Added missing $args parameter for ID prefixing and general backwards compatibility.
     *
     * @param int $formId
     * @param array $args
     * @param PaymentGatewayInterface  $registeredGateway
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
     * @unreleased Replace is_recurring with is_donation_recurring to detect recurring donations.
     * @unreleased Replace give_insert_payment with donation model.
     *
     * @param  array  $legacyDonationData  Legacy Donation Data
     * @param  PaymentGatewayInterface  $registeredGateway
     *
     * @return void
     * @throws Exception
     */
    public function handleBeforeGateway($legacyDonationData, $registeredGateway)
    {
        $formData = FormData::fromRequest($legacyDonationData);

        $this->validateGatewayNonce($formData->gatewayNonce);

        $donor = $this->getOrCreateDonor(
            $formData->donorInfo->wpUserId,
            $formData->donorInfo->email,
            $formData->donorInfo->firstName,
            $formData->donorInfo->lastName
        );

        $donation = $formData->toDonation($donor->id)->save();

        $this->setSession($donation->id);

        $gatewayPaymentData = $formData->toGatewayPaymentData($donation->id);

        if (give_recurring_is_donation_recurring($formData->legacyDonationData)) {
            $subscriptionData = SubscriptionData::fromRequest($legacyDonationData);
            $subscriptionId = $this->createSubscription($donation->id, $formData, $subscriptionData);

            $gatewaySubscriptionData = $subscriptionData->toGatewaySubscriptionData($subscriptionId);

            $registeredGateway->handleCreateSubscription($gatewayPaymentData, $gatewaySubscriptionData);
        }

        $registeredGateway->handleCreatePayment($gatewayPaymentData);
    }

    /**
     * Create the subscription
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

    /**
     * Set donation id to purchase session for use in the donation receipt.
     *
     * @unreleased
     *
     * @param $donationId
     * @return void
     */
    private function setSession($donationId)
    {
        $purchaseSession = (array)give()->session->get('give_purchase');

        if ($purchaseSession && array_key_exists('purchase_key', $purchaseSession)) {
            $purchaseSession['donation_id'] = $donationId;
            give()->session->set('give_purchase', $purchaseSession);
        }
    }

    /**
     * @unreleased
     *
     * @param  int|null  $userId
     * @param  string  $donorEmail
     * @param  string  $firstName
     * @param  string  $lastName
     * @return Donor
     * @throws Exception
     */
    private function getOrCreateDonor($userId, $donorEmail, $firstName, $lastName)
    {
        // first check if donor exists as a user
        $donor = Donor::whereUserId($userId);

        // If they exist as a donor & user then make sure they don't already own this email before adding to their additional emails list..
        if ($donor && !$donor->hasEmail($donorEmail)) {
            $donor->addAdditionalEmail($donorEmail);
        }

        // if donor is not a user than check for any donor matching this email
        if (!$donor) {
            $donor = Donor::whereEmail($donorEmail);
        }

        // if no donor exists then create a new one using their personal information from the form.
        if (!$donor) {
            $donor = Donor::create([
                'name' => trim("$firstName $lastName"),
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $donorEmail,
                'userId' => $userId ?: null
            ]);
        }

        return $donor;
    }
}
