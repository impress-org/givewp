<?php

namespace Give\NextGen\Gateways\PayPal\PayPalStandardGateway;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\EnqueueScript;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Contracts\NextGenPaymentGatewayInterface;
use Give\NextGen\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;

use function add_query_arg;

class PayPalStandardGateway extends PayPalStandard implements NextGenPaymentGatewayInterface
{
    use HandleHttpResponses;

    /**
     * @inheritdoc
     */
    public function supportsLegacyForm(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function enqueueScript(): EnqueueScript
    {
        return new EnqueueScript(
            self::id(),
            'build/payPalStandardGateway.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );
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
        return __('PayPal Standard (Next Gen)', 'give');
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