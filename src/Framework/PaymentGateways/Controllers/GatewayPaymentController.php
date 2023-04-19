<?php

namespace Give\Framework\PaymentGateways\Controllers;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\PaymentGateways\Actions\HandleGatewayPaymentCommand;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\PaymentGateways\Actions\GetGatewayDataFromRequest;

/**
 * @unreleased
 */
class GatewayPaymentController
{
    use HandleHttpResponses;

    /**
     * @var PaymentGateway
     */
    protected $gateway;

    /**
     * @unreleased
     */
    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @unreleased
     */
    public function create(Donation $donation)
    {
        try {
            /**
             * Filter hook to provide gateway data before transaction is processed by the gateway.
             *
             * @since 2.21.2
             */
            $gatewayData = apply_filters(
                "givewp_create_payment_gateway_data_{$donation->gatewayId}",
                (new GetGatewayDataFromRequest)(),
                $donation
            );

            $command = $this->gateway->createPayment($donation, $gatewayData);
            $this->handleGatewayCommand($command, $donation);
        } catch (\Exception $exception) {
            PaymentGatewayLog::error(
                $exception->getMessage(),
                [
                    'Payment Gateway' => $this->gateway::id(),
                    'Donation' => $donation->toArray(),
                ]
            );

            $message = __(
                'An unexpected error occurred while processing the donation.  Please try again or contact a site administrator.',
                'give'
            );

            $this->handleExceptionResponse($exception, $message);
        }
    }

    /**
     * Handle gateway command
     *
     * @unreleased move logic into action
     * @since 2.18.0
     *
     * @throws TypeNotSupported
     * @throws Exception
     */
    protected function handleGatewayCommand(GatewayCommand $command, Donation $donation)
    {
        (new HandleGatewayPaymentCommand())($command, $donation);
    }
}
