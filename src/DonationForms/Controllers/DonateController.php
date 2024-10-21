<?php

namespace Give\DonationForms\Controllers;

use Exception;
use Give\DonationForms\Actions\GetOrCreateDonor;
use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\Donations\Models\Donation;
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
     * @since 3.2.0 Pass the form ID to match updated signature for getOrCreateDonor().
     * @since 3.0.0
     *
     * @return void
     * @throws Exception|PaymentGatewayException
     */
    public function donate(DonateControllerData $formData, PaymentGateway $gateway)
    {
        /**
         * Fires at the start of donation form processing, before any data is processed.
         *
         * @since 3.4.0
         *
         * @param  DonateControllerData  $formData
         * @param  string  $gatewayId
         */
        do_action('givewp_donation_form_processing_start', $formData, $gateway::id());

        $donor = $this->getOrCreateDonor(
            $formData->formId,
            $formData->wpUserId,
            $formData->email,
            $formData->firstName,
            $formData->lastName,
            $formData->honorific,
            $formData->phone
        );

        if ($formData->donationType->isSingle()) {
            $donation = $formData->toDonation($donor->id);
            $donation->save();

            /**
             * Internal hook that fires after a donation is created during the donate controller.
             *
             * @since 3.0.0
             *
             * @param  DonateControllerData  $formData
             * @param  Donation  $donation
             * @param  Subscription|null  $subscription
             */
            do_action('givewp_donate_controller_donation_created', $formData, $donation, null);

            /**
             * Fires after a donation is created during donation form processing.
             *
             * @since 3.4.0
             *
             * @param  Donation  $donation
             * @param  Subscription|null  $subscription
             */
            do_action('givewp_donation_form_processing_donation_created', $donation, null);

            /**
             * Filter for adding modifying custom $gatewayData sent to the $gateway->createPayment() method.
             *
             * @since 3.0.0
             */
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

            /**
             * Internal hook that fires after a donation is created in the donate controller.
             *
             * @since 3.0.0
             *
             * @param  DonateControllerData  $formData
             * @param  Donation  $donation
             * @param  Subscription  $subscription
             */
            do_action('givewp_donate_controller_donation_created', $formData, $donation, $subscription);

            /**
             * Fires after a donation is created during donation form processing.
             *
             * @since 3.4.0
             *
             * @param  Donation  $donation
             * @param  Subscription|null  $subscription
             */
            do_action('givewp_donation_form_processing_donation_created', $donation, $subscription);

            /**
             * Internal hook that fires after a subscription is created in the donate controller.
             *
             * @since 3.0.0
             *
             * @param  DonateControllerData  $formData
             * @param  Subscription  $subscription
             * @param  Donation  $donation
             */
            do_action('givewp_donate_controller_subscription_created', $formData, $subscription, $donation);

            /**
             * Fires after a subscription is created during donation form processing.
             *
             * @since 3.4.0
             *
             * @param  Subscription  $subscription
             * @param  Donation  $donation
             */
            do_action('givewp_donation_form_processing_subscription_created', $subscription, $donation);

            /**
             * Filter for adding modifying custom $gatewayData sent to the $gateway->createSubscription() method.
             *
             * @since 3.0.0
             */
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
     * @since 3.9.0 Add support for "phone" property
     * @since 3.2.0 Added $formId to the signature for passing to do_action hooks. Added honorific and use GetOrCreateDonor action
     * @since 3.0.0
     *
     * @throws Exception
     */
    private function getOrCreateDonor(
        int $formId,
        ?int $userId,
        string $donorEmail,
        string $firstName,
        string $lastName,
        ?string $honorific,
        ?string $donorPhone
    ): Donor {
        $getOrCreateDonorAction = new GetOrCreateDonor();

        $donor = $getOrCreateDonorAction(
            $userId,
            $donorEmail,
            $firstName,
            $lastName,
            $honorific,
            $donorPhone
        );

        if ($getOrCreateDonorAction->donorCreated) {
            /**
             * Internal hook to differentiate when a v3 form creates a new donor.
             *
             * @since 3.2.0
             * @param  Donor  $donor
             * @param  int  $formId
             */
            do_action('givewp_donate_controller_donor_created', $donor, $formId);

            /**
             * Fires after a donor is created during donation form processing.
             *
             * @since 3.4.0
             *
             * @param  Donor  $donor
             * @param  int  $formId
             */
            do_action('givewp_donation_form_processing_donor_created', $donor, $formId);
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
