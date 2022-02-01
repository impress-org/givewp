<?php

namespace Give\PaymentGateways\PayPalStandard;

use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\PayPalStandard\Actions\CreatePayPalStandardPaymentURL;
use Give\PaymentGateways\PayPalStandard\Actions\RedirectOffsitePayment;
use Give\PaymentGateways\PayPalStandard\Views\PayPalStandardBillingFields;
use Give_Payment;

/**
 * This class handles one-time donation payment processing with PayPal Standard payment gateway
 *
 * @unlreased
 */
class PayPalStandardGateway extends PaymentGateway
{
    public $routeMethods = [
        'handleIpnNotification'
    ];

    public $secureRouteMethods = [
        'handleSuccessPaymentReturn',
        'handleFailedPaymentReturn'
    ];

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId, $args)
    {
        Call::invoke(PayPalStandardBillingFields::class, $formId);
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'paypal';
    }

    /**
     * @inerhitDoc
     */
    public function getId()
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return esc_html__('PayPal Standard', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return esc_html__('PayPal', 'give');
    }

    /**
     * @inheritDoc
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        return new RedirectOffsite(
            Call::invoke(
                CreatePayPalStandardPaymentURL::class,
                $paymentData,
                $this->generateSecureGatewayRouteUrl(
                    'handleSuccessPaymentReturn',
                    ['donation-id' => $paymentData->donationId]
                ),
                $this->generateSecureGatewayRouteUrl(
                    'handleFailedPaymentReturn',
                    ['donation-id' => $paymentData->donationId]
                ),
                $this->generateGatewayRouteUrl(
                    $paymentData->gatewayId,
                    'handleIpnNotification'
                )
            )
        );
    }

    /**
     * Handle payment redirect after successful payment on PayPal standard.
     *
     * @unreleased
     *
     * @param array $queryParams Query params in gateway route. {
     *
     * @type string "donation-id" Donation id.
     *
     * }
     *
     * @return void
     */
    public function handleSuccessPaymentReturn($queryParams)
    {
        $donationId = (int)$queryParams['donation-id'];
        $payment = new Give_Payment($donationId);
        $payment->update_status('processing');

        RedirectOffsitePayment::redirectToReceiptPage( $donationId );
    }

    /**
     * Handle payment redirect after failed payment on PayPal standard.
     *
     * @unreleased
     *
     * @param array $queryParams Query params in gateway route. {
     *
     * @type string "donation-id" Donation id.
     *
     * }
     *
     * @return void
     */
    public function handleFailedPaymentReturn($queryParams)
    {
        $donationId = (int)$queryParams['donation-id'];
        $payment = new Give_Payment($donationId);
        $payment->update_status('failed');

        RedirectOffsitePayment::redirectToFailedPage( $donationId );
    }

    /**
     * Handle PayPal IPN notification.
     *
     * @unreleased
     */
    public function handleIpnNotification()
    {
        give_process_paypal_ipn();
    }
}
