<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Traits\Responseable;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\LegacyPaymentGateways\Contracts\LegacyPaymentGatewayInterface;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

/**
 * @unreleased
 */
abstract class PaymentGateway implements PaymentGatewayInterface, LegacyPaymentGatewayInterface
{
    use Responseable;

    /**
     * @var SubscriptionModuleInterface $subscriptionModule
     */
    public $subscriptionModule;

    /**
     * @unreleased
     *
     * @param  SubscriptionModuleInterface|null  $subscriptionModule
     */
    public function __construct(SubscriptionModuleInterface $subscriptionModule = null)
    {
        $this->subscriptionModule = $subscriptionModule;
    }


    /**
     * @inheritDoc
     */
    public function supportsSubscriptions()
    {
        return isset($this->subscriptionModule);
    }

    /**
     * @inheritDoc
     */
    public function handleCreatePayment(GatewayPaymentData $gatewayPaymentData)
    {
        $payment = $this->createPayment($gatewayPaymentData);

        $this->handleReturnTypes($payment);
    }

    /**
     * @inheritDoc
     */
    public function handleCreateSubscription(GatewayPaymentData $paymentData, GatewaySubscriptionData $subscriptionData)
    {
        $subscription = $this->createSubscription($paymentData, $subscriptionData);

        $this->handleReturnTypes($subscription);
    }

    /**
     * If a subscription module isn't wanted this method can be overridden by a child class instead.
     * Just make sure to override the supportsSubscriptions method as well.
     *
     * @inheritDoc
     */
    public function createSubscription(GatewayPaymentData $paymentData, GatewaySubscriptionData $subscriptionData)
    {
        return $this->subscriptionModule->createSubscription($paymentData, $subscriptionData);
    }

    /**
     * Handle return types
     *
     * @param  RedirectResponse|JsonResponse  $type
     * @throws TypeNotSupported
     */
    private function handleReturnTypes($type)
    {
        if ($type instanceof RedirectResponse) {
            wp_redirect($type->getTargetUrl());
            exit;
        }

        if ($type instanceof JsonResponse) {
            wp_send_json(['data' => $type->getData()]);
        }

        throw new TypeNotSupported(
            sprintf(
                "Return type must be an instance of %s or %s",
                RedirectResponse::class,
                JsonResponse::class
            )
        );
    }
}
