<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayRefundable;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Language;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\DataTransferObjects\StripeGatewayData;
use Stripe\Exception\ApiErrorException;

/**
 * @since 3.0.0
 */
class StripePaymentElementGateway extends PaymentGateway implements PaymentGatewayRefundable
{
    use StripePaymentElementRepository;
    use HasScriptAssetFile;

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'stripe_payment_element';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('Stripe - Payment Element', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Stripe Payment Element', 'give');
    }

    /**
     * @since 3.1.0 set translations for script
     * @since 3.0.0
     */
    public function enqueueScript(int $formId)
    {
        $assets = $this->getScriptAsset(GIVE_PLUGIN_DIR . 'build/stripePaymentElementGateway.asset.php');
        $handle = $this::id();

        wp_enqueue_script(
            $handle,
            GIVE_PLUGIN_URL . 'build/stripePaymentElementGateway.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );

        Language::setScriptTranslations($handle);
    }

    /**
     * @since 3.0.0
     */
    public function formSettings(int $formId): array
    {
        $this->setUpStripeAppInfo($formId);

        $stripePublishableKey = $this->getStripePublishableKey($formId);
        $stripeConnectedAccountKey = $this->getStripeConnectedAccountKey($formId);

        return [
            'formId' => $formId,
            'stripeKey' => $stripePublishableKey,
            'stripeConnectedAccountId' => $stripeConnectedAccountKey,
        ];
    }

    /**
     * @since 3.12.1 updated to send billing address details to Stripe
     * @since 3.0.0
     * @inheritDoc
     * @throws ApiErrorException
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        /**
         * Initialize the Stripe SDK using Stripe::setAppInfo()
         */
        $this->setUpStripeAppInfo($donation->formId);

        /**
         * Get data from client request
         */
        $stripeGatewayData = StripeGatewayData::fromRequest($gatewayData);

        /**
         * Get or create a Stripe customer
         */
        $customer = $this->getOrCreateStripeCustomerFromDonation(
            $stripeGatewayData->stripeConnectedAccountId,
            $donation
        );


        /**
         * Setup Stripe Payment Intent args
         */
        $intentData = $this->getPaymentIntentDataFromDonation(
            $donation,
            $customer
        );

        /**
         * Generate Payment Intent
         */
        $intent = $this->generateStripePaymentIntent(
            $stripeGatewayData->stripeConnectedAccountId,
            $intentData
        );

        /**
         * Update Donation Meta
         */
        $this->updateDonationMetaFromPaymentIntent($donation, $intent);

        /**
         * Return response to client.
         * 'clientSecret' is required to confirm payment intent on client side.
         * 'returnUrl' is required to redirect user to success page.
         */
        return new RespondToBrowser([
            'clientSecret' => $intent->client_secret,
            'returnUrl' => $stripeGatewayData->successUrl,
            'billingDetails' => [
                'name' => trim("$donation->firstName $donation->lastName"),
                'email' => $donation->email,
                'address' => [
                    'city' => $donation->billingAddress->city,
                    'country' => $donation->billingAddress->country,
                    'line1' => $donation->billingAddress->address1,
                    'line2' => $donation->billingAddress->address2,
                    'postal_code' => $donation->billingAddress->zip,
                    'state' => $donation->billingAddress->state,
                ],
            ],
        ]);
    }

    /**
     * @since 4.7.0
     *
     * @throws Exception
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        try {
            $refund = $this->refundStripePayment($donation);

            if ($refund->status !== 'succeeded') {
                throw new Exception(__('Refund failed. Please check the Stripe dashboard for more details.', 'give'));
            }

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(
                    __('Donation refunded in Stripe for transaction ID: %s', 'give'),
                    $donation->gatewayTransactionId
                ),
            ]);

            return new PaymentRefunded();
        } catch (Exception $e) {
            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(
                    __(
                        'Error! Donation %s was NOT refunded. Find more details on the error in the logs at Donations > Tools > Logs. To refund the donation, use the Stripe dashboard tools.',
                        'give'
                    ),
                    $donation->id
                ),
            ]);

            throw new PaymentGatewayException(sprintf(__('Stripe API error: %s', 'give'), $e->getMessage()));
        }
    }
}

