<?php

namespace Give\Framework\PaymentGateways\Controllers;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\PaymentGateways\Actions\HandleGatewayPaymentCommand;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;

/**
 * @since 2.27.0
 */
class GatewayPaymentController
{
    use HandleHttpResponses;

    /**
     * @var PaymentGateway
     */
    protected $gateway;

    /**
     * @since 2.27.0
     */
    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @since 3.0.0 Catch and handle errors from the gateway upstream
     * @since 2.27.0
     *
     * @throws Exception|TypeNotSupported|PaymentGatewayException
     */
    public function create(Donation $donation, array $gatewayData = [])
    {
        $command = $this->gateway->createPayment($donation, $gatewayData);

        $this->handleGatewayCommand($command, $donation);
    }

    /**
     * @since 2.29.0
     */
    public function refund(Donation $donation)
    {
        try {
            $command = $this->gateway->refundDonation($donation);

            if ($command instanceof GatewayCommand) {
                $this->handleGatewayCommand($command, $donation);
            }
        } catch (\Exception $exception) {
            PaymentGatewayLog::error(
                $exception->getMessage(),
                [
                    'Payment Gateway' => $this->gateway::id(),
                    'Donation' => $donation->toArray(),
                ]
            );

            $message = __(
                'An unexpected error occurred while refunding the donation.  Please try again or contact a site administrator.',
                'give'
            );

            $this->handleExceptionResponse($exception, $message);
        }
    }

    /**
     * Handle gateway command
     *
     * @since 2.27.0 move logic into action
     * @since 2.18.0
     *
     * @throws TypeNotSupported
     * @throws Exception
     */
    protected function handleGatewayCommand(GatewayCommand $command, Donation $donation)
    {
        $response = (new HandleGatewayPaymentCommand())($command, $donation);

        $this->handleResponse($response);
    }
}
