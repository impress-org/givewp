<?php

namespace Give\PaymentGateways\Gateways\PayPal\PayPalStandardGateway;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\Gateways\PayPalStandard\Views\PayPalStandardBillingFields;

class PayPalStandardGateway extends PayPalStandard
{
    use HandleHttpResponses;
    use HasScriptAssetFile;

    /**
     * @since 0.5.0
     */
    public function enqueueScript(int $formId)
    {
        $assets = $this->getScriptAsset(GIVE_NEXT_GEN_DIR . 'build/payPalStandardGateway.asset.php');

        wp_enqueue_script(
            self::id(),
            GIVE_NEXT_GEN_URL . 'build/payPalStandardGateway.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return (new PayPalStandardBillingFields())($formId);
    }

    /**
     * @inheritdoc
     */
    public function formSettings(int $formId): array
    {
        return [
            'fields' => [
                'heading' => __('Make your donation quickly and securely with PayPal', 'give'),
                'subheading' => __('How it works', 'give'),
                'body' => __(
                    'You will be redirected to PayPal to complete your donation with your debit card, credit card, or with your PayPal account. Once complete, you will be redirected back to this site to view your receipt.',
                    'give'
                ),
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('PayPal Standard', 'give');
    }

    /**
     * @inheritDoc
     * @param  array{successUrl: string, cancelUrl: string}  $gatewayData
     */
    public function createPayment(Donation $donation, $gatewayData = []): RedirectOffsite
    {
        /**
         * Add additional query args to PayPal redirect URLs.
         * This does not affect the core PayPal Standard gateway functionality.
         * Later in our routeMethods, there are conditionals to check for these query args
         * and proceed accordingly if they exist or not making this gateway backwards compatible with legacy forms.
         */
        add_filter(
            'give_gateway_paypal_redirect_args',
            static function ($paypalPaymentArguments) use ($gatewayData) {
                $paypalPaymentArguments['return'] = add_query_arg(
                    ['givewp-return-url' => $gatewayData['successUrl']],
                    $paypalPaymentArguments['return']
                );

                $paypalPaymentArguments['cancel_return'] = add_query_arg(
                    ['givewp-return-url' => $gatewayData['cancelUrl']],
                    $paypalPaymentArguments['cancel_return']
                );

                return $paypalPaymentArguments;
            }
        );

        return parent::createPayment($donation, $gatewayData);
    }

    /**
     * @inheritDoc
     */
    protected function handleSuccessPaymentReturn(array $queryParams): RedirectResponse
    {
        if (!empty($queryParams['givewp-return-url'])) {
            return new RedirectResponse(esc_url_raw($queryParams['givewp-return-url']));
        }

        return parent::handleSuccessPaymentReturn($queryParams);
    }

    /**
     * This method is called when the user cancels the payment on PayPal.
     *
     * It should really be called handleCancelledPaymentReturn.
     *
     * @inheritDoc
     */
    protected function handleFailedPaymentReturn(array $queryParams): RedirectResponse
    {
        if (!empty($queryParams['givewp-return-url'])) {
            $donationId = (int)$queryParams['donation-id'];

            /** @var Donation $donation */
            $donation = Donation::find($donationId);
            $donation->status = DonationStatus::CANCELLED();
            $donation->save();

            return new RedirectResponse(esc_url_raw($queryParams['givewp-return-url']));
        }

        return parent::handleFailedPaymentReturn($queryParams);
    }
}