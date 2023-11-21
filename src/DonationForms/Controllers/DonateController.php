<?php

namespace Give\DonationForms\Controllers;

use Exception;
use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\Donors\Models\Donor;
use Give\Framework\PaymentGateways\Controllers\GatewayPaymentController;
use Give\Framework\PaymentGateways\Controllers\GatewaySubscriptionController;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\Actions\GetGatewayDataFromRequest;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 3.0.0
 */
class DonateController
{
    /**
     * First we create a donation and/or subscription, then move on to the gateway processing
     *
     * @unreleased Pass the form ID to match updated signature for getOrCreateDonor().
     * @since 3.0.0
     *
     * @return void
     * @throws Exception|PaymentGatewayException
     */
    public function donate(DonateControllerData $formData, PaymentGateway $gateway)
    {
        $donor = $this->getOrCreateDonor(
            $formData->formId,
            $formData->wpUserId,
            $formData->email,
            $formData->firstName,
            $formData->lastName
        );

        if ($formData->donationType->isSingle()) {
            $donation = $formData->toDonation($donor->id);
            $donation->save();

            do_action('givewp_donate_controller_donation_created', $formData, $donation, null);

            $gatewayData = apply_filters(
                "givewp_create_payment_gateway_data_{$gateway::id()}",
                (new GetGatewayDataFromRequest)(),
                $donation
            );

            $controller = new GatewayPaymentController($gateway);
            $controller->create($donation, $gatewayData);
        }

        if ($formData->donationType->isSubscription()) {
            $this->validateGatewaySupportsSubscriptions($gateway);

            $subscription = $formData->toSubscription($donor->id);
            $subscription->save();

            $donation = $formData->toInitialSubscriptionDonation($donor->id, $subscription->id);
            $donation->save();

            do_action('givewp_donate_controller_donation_created', $formData, $donation, $subscription);

            do_action('givewp_donate_controller_subscription_created', $formData, $subscription, $donation);

            $gatewayData = apply_filters(
                "givewp_create_subscription_gateway_data_{$gateway::id()}",
                (new GetGatewayDataFromRequest)(),
                $donation,
                $subscription
            );

            $controller = new GatewaySubscriptionController($gateway);
            $controller->create($donation, $subscription, $gatewayData);
        }
    }

    /**
     * @unreleased Added $formId to the signature for passing to do_action hooks.
     * @since 3.0.0
     *
     * @param  int  $formId
     * @param  int|null  $userId
     * @param  string  $donorEmail
     * @param  string  $firstName
     * @param  string  $lastName
     *
     * @return Donor
     * @throws Exception
     */
    private function getOrCreateDonor(
        int $formId,
        int $userId,
        string $donorEmail,
        string $firstName,
        string $lastName
    ): Donor {
        // first check if donor exists as a user
        $donor = Donor::whereUserId($userId);

        // If they exist as a donor & user then make sure they don't already own this email before adding to their additional emails list..
        if ($donor && !$donor->hasEmail($donorEmail)) {
            $donor->additionalEmails = array_merge($donor->additionalEmails ?? [], [$donorEmail]);
            $donor->save();
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

            /**
             * @unreleased Add a new do_action hook to differentiate when a v3 form creates a new donor.
             * @param Donor $donor
             * @param int $formId
             */
            do_action('givewp_donate_controller_donor_created', $donor, $formId);
        }

        return $donor;
    }

    /**
     * @throws PaymentGatewayException
     */
    private function validateGatewaySupportsSubscriptions(PaymentGateway $gateway)
    {
        if (!$gateway->supportsSubscriptions()) {
            $gatewayName = $gateway->getName();

            throw new PaymentGatewayException(
                sprintf(
                    __(
                        "[%s] This payment gateway does not support recurring payments, please try selecting another payment gateway.",
                        'give'
                    ),
                    $gatewayName
                )
            );
        }
    }
}
