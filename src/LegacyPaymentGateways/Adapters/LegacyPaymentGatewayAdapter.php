<?php

namespace Give\LegacyPaymentGateways\Adapters;

use Exception;
use Give\DonationForms\Actions\GetOrCreateDonor;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\Controllers\GatewayPaymentController;
use Give\Framework\PaymentGateways\Controllers\GatewaySubscriptionController;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Helpers\Form\Utils;
use Give\PaymentGateways\Actions\GetGatewayDataFromRequest;
use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\DataTransferObjects\SubscriptionData;
use Give\Session\SessionDonation\DonationAccessor;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Class LegacyPaymentGatewayAdapter
 * @since 2.18.0
 */
class LegacyPaymentGatewayAdapter
{
    use HandleHttpResponses;

    /**
     * Get legacy form field markup to display gateway specific payment fields
     *
     * @since 2.18.0
     * @since 2.19.0 Added missing $args parameter for ID prefixing and general backwards compatibility.
     */
    public function getLegacyFormFieldMarkup(
        int $formId,
        array $args,
        PaymentGateway $registeredGateway
    ): string {
        return $registeredGateway->getLegacyFormFieldMarkup($formId, $args);
    }

    /**
     * First we create a payment, then move on to the gateway processing
     *
     * @since 3.9.0 Add support to "phone" property
     * @since 3.2.0  Capture exceptions when get gateway data.
     * @since 3.0.0 Catch and handle errors from the gateway here
     * @since 2.30.0  Add success, cancel and failed URLs to gateway data.  This will be used in both v2 and v3 forms so gateways can just refer to the gateway data.
     * @since 2.24.0 add support for payment mode
     * @since 2.21.0 Replace give_insert_payment with donation model. Store legacy subscription data in donation meta.
     *             Attach subscription id to donation.
     * @since 2.19.0 Replace is_recurring with is_donation_recurring to detect recurring donations.
     * @since 2.18.0
     *
     * @throws Exception
     */
    public function handleBeforeGateway(array $legacyDonationData, PaymentGateway $registeredGateway)
    {
        $formData = FormData::fromRequest($legacyDonationData);

        $this->validateGatewayNonce($formData->gatewayNonce);

        $this->validateDonationFormStatus($formData->formId);

        /**
         * Fires at the start of donation form processing, before any data is processed.
         *
         * @since 3.4.0
         *
         * @param  FormData  $formData
         * @param  string  $gatewayId
         */
        do_action('give_donation_form_processing_start', $formData, $formData->paymentGateway);

        $donor = $this->getOrCreateDonor(
            $formData->formId,
            $formData->donorInfo->wpUserId,
            $formData->donorInfo->email,
            $formData->donorInfo->firstName,
            $formData->donorInfo->lastName,
            $formData->donorInfo->honorific,
            ''
        );

        $donation = $formData->toDonation($donor->id);

        if (give_recurring_is_donation_recurring($legacyDonationData)) {
            $subscriptionData = SubscriptionData::fromRequest($legacyDonationData);

            $paymentMode = !empty($legacyDonationData['payment_mode']) ? new SubscriptionMode(
                $legacyDonationData['payment_mode']
            ) : null;

            if ($paymentMode === null) {
                $paymentMode = give_is_test_mode() ? SubscriptionMode::TEST() : SubscriptionMode::LIVE();
            }

            $subscription = Subscription::create([
                'amount' => $donation->amount,
                'period' => new SubscriptionPeriod($subscriptionData->period),
                'frequency' => (int)$subscriptionData->frequency,
                'donorId' => $donor->id,
                'installments' => (int)$subscriptionData->times,
                'status' => SubscriptionStatus::PENDING(),
                'mode' => $paymentMode,
                'donationFormId' => $formData->formId,
            ]);

            $donation->type = DonationType::SUBSCRIPTION();
            $donation->subscriptionId = $subscription->id;
            $donation->save();

            give()->subscriptions->updateLegacyParentPaymentId($subscription->id, $donation->id);

            $this->setSession($donation->id);

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
             * Fires after a subscription is created during donation form processing.
             *
             * @since 3.4.0
             *
             * @param  Subscription  $subscription
             * @param  Donation  $donation
             */
            do_action('givewp_donation_form_processing_subscription_created', $subscription, $donation);

            try {
                /**
                 * Filter hook to provide gateway data before initial transaction for subscription is processed by the gateway.
                 *
                 * @since 2.21.2
                 */
                $gatewayData = apply_filters(
                    "givewp_create_subscription_gateway_data_{$registeredGateway::id()}",
                    (new GetGatewayDataFromRequest())(),
                    $donation,
                    $subscription
                );

                $gatewayData = $this->addUrlsToGatewayData($donation, $gatewayData, $registeredGateway);

                $controller = new GatewaySubscriptionController($registeredGateway);

                $controller->create($donation, $subscription, $gatewayData);
            } catch (Exception $exception) {
                PaymentGatewayLog::error(
                    $exception->getMessage(),
                    [
                       'Payment Gateway' => $registeredGateway::id(),
                       'Donation' => $donation->toArray(),
                       'Subscription' => $subscription->toArray(),
                    ]
                );

                $message = __(
                    'An unexpected error occurred while processing the subscription.  Please try again or contact the site administrator.',
                    'give'
                );

                $this->handleExceptionResponse($exception, $message);
            }
        } else {
            $donation->type = DonationType::SINGLE();
            $donation->save();

            $this->setSession($donation->id);

            /**
             * Fires after a donation is created during donation form processing.
             *
             * @since 3.4.0
             *
             * @param  Donation  $donation
             * @param  Subscription|null  $subscription
             */
            do_action('givewp_donation_form_processing_donation_created', $donation, null);

            try {
                /**
                 * Filter hook to provide gateway data before transaction is processed by the gateway.
                 *
                 * @since 2.21.2
                 */
                $gatewayData = apply_filters(
                    "givewp_create_payment_gateway_data_{$registeredGateway::id()}",
                    (new GetGatewayDataFromRequest())(),
                    $donation
                );

                $gatewayData = $this->addUrlsToGatewayData($donation, $gatewayData, $registeredGateway);

                $controller = new GatewayPaymentController($registeredGateway);

                $controller->create($donation, $gatewayData);
            } catch (Exception $exception) {
                PaymentGatewayLog::error(
                    $exception->getMessage(),
                    [
                        'Payment Gateway' => $registeredGateway::id(),
                        'Donation' => $donation->toArray(),
                    ]
                );

                $message = __(
                    'An unexpected error occurred while processing the donation.  Please try again or contact the site administrator.',
                    'give'
                );

                $this->handleExceptionResponse($exception, $message);
            }
        }
    }

    /**
     * @since 2.30.0
     */
    protected function getGatewayDataSuccessUrl(int $donationId): string
    {
        $formId = give_get_payment_form_id($donationId);
        $isEmbedDonationForm = !Utils::isLegacyForm($formId);
        $donationFormPageUrl = (new DonationAccessor())->get()->formEntry->currentUrl ?: get_permalink($formId);

        return $isEmbedDonationForm ?
            Utils::createSuccessPageURL($donationFormPageUrl) :
            give_get_success_page_url();
    }

    /**
     * @since 2.30.0
     */
    protected function getGatewayDataFailedUrl(int $donationId): string
    {
        $formId = give_get_payment_form_id($donationId);
        $isEmbedDonationForm = !Utils::isLegacyForm($formId);
        $donationFormPageUrl = (new DonationAccessor())->get()->formEntry->currentUrl ?: get_permalink($formId);

        return $isEmbedDonationForm ?
            Utils::createFailedPageURL($donationFormPageUrl) :
            give_get_failed_transaction_uri();
    }

    /**
     * @since 2.30.0
     */
    protected function getGatewayDataCancelUrl(int $donationId): string
    {
        $formId = give_get_payment_form_id($donationId);

        return (new DonationAccessor())->get()->formEntry->currentUrl ?: get_permalink($formId);
    }

    /**
     * Validate Gateway Nonce
     *
     * @since 2.18.0
     */
    private function validateGatewayNonce(string $gatewayNonce)
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
     * Validate Donation Form Status
     *
     * @since 2.33.2
     */
    private function validateDonationFormStatus(int $formId)
    {
        $donationForm = DonationForm::find($formId);

        if (!$donationForm || $donationForm->status->isTrash()) {
            wp_die(
                esc_html__(
                    'This donation form is not accepting donations.',
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
     * @since 2.21.0
     *
     * @param $donationId
     *
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
     * @since 3.9.0 Add support to "phone" property
     * @since 3.4.0 add $formId and do_action
     * @since 3.2.0 add honorific and use GetOrCreateDonor action
     * @since 2.21.0
     *
     * @throws Exception
     */
    private function getOrCreateDonor(
        ?int $formId,
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
     * @since 2.29.0
     */
    public function addOptRefundCheckbox(int $donationId, PaymentGatewayInterface $registeredGateway)
    {
        $donation = Donation::find($donationId);
        if ($donation->gatewayId === $registeredGateway::id()) {
            ?>
          <div id="give-gateway-opt-refund-wrap"
               class="give-gateway-opt-refund give-admin-box-inside give-hidden">
            <p>
              <input type="checkbox" id="give-gateway-opt-refund" name="give_gateway_opt_refund" value="1" />
              <label for="give-gateway-opt-refund">
                  <?php
                    esc_html_e(sprintf('Refund the donation at %s?', $registeredGateway->getName()), 'give');
                    ?>
              </label>
            </p>
          </div>
          <script>
            if (!!document.getElementById("give-payment-status") &&
              1 === document.querySelectorAll("div.give-admin-box > div.give-hidden[id*=\"opt-refund\"] input[type=\"checkbox\"]").length
            ) {
              document.getElementById("give-payment-status").addEventListener("change", function(event) {
                const refundCheckbox = document.getElementById("give-gateway-opt-refund");

                if (null === refundCheckbox) {
                  return;
                }

                refundCheckbox.checked = false;

                if ("refunded" === event.target.value) {
                  document.getElementById("give-gateway-opt-refund-wrap").style.display = "block";
                } else {
                  document.getElementById("give-gateway-opt-refund-wrap").style.display = "none";
                }
              });
            }
          </script>
            <?php
        }
    }

    /**
     * @since 2.29.0
     */
    public function maybeRefundOnGateway(
        int $donationId,
        string $newStatus,
        string $oldStatus,
        PaymentGateway $registeredGateway
    ) {
        $gatewayOptRefund = !empty($_POST['give_gateway_opt_refund']) ? give_clean(
            $_POST['give_gateway_opt_refund']
        ) : '';
        $canProcessRefund = !empty($gatewayOptRefund) ? $gatewayOptRefund : false;

        // Only move forward if refund requested.
        if (!$canProcessRefund) {
            return;
        }

        $donation = Donation::find($donationId);
        if (
            $donation->gatewayId === $registeredGateway::id() &&
            'refunded' === $newStatus &&
            'refunded' !== $oldStatus
        ) {
            $controller = new GatewayPaymentController($registeredGateway);
            $controller->refund($donation);
        }
    }

    /**
     * @since 2.30.0
     */
    protected function addUrlsToGatewayData(Donation $donation, $gatewayData, PaymentGateway $registeredGateway)
    {
        return array_merge($gatewayData, [
            'successUrl' => add_query_arg(
                ['payment-confirmation' => $registeredGateway::id()],
                $this->getGatewayDataSuccessUrl($donation->id)
            ),
            'cancelUrl' => $this->getGatewayDataCancelUrl($donation->id),
            'failedUrl' => $this->getGatewayDataFailedUrl($donation->id)
        ]);
    }
}
