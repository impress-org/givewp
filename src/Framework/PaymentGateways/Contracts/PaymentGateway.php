<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\LegacyPaymentGateways\Contracts\LegacyPaymentGatewayInterface;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionCompleteHandler;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

use function Give\Framework\Http\Response\response;

/**
 * @unreleased
 */
abstract class PaymentGateway implements PaymentGatewayInterface, LegacyPaymentGatewayInterface
{
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
        try {
            $command = $this->createPayment($gatewayPaymentData);
            $this->handleGatewayPaymentCommand($command, $gatewayPaymentData);
        } catch (PaymentGatewayException $paymentGatewayException) {
            $message = $paymentGatewayException->getMessage() ?: __(
                'An unexpected error occurred from the payment gateway.  Please try again or contact us to help resolve.',
                'give'
            );

            $this->handleResponse(response()->json($message));
            exit;
        } catch (Exception $exception) {
            PaymentGatewayLog::error($exception->getMessage());

            $message = __(
                'An unexpected error occurred while processing your donation.  Please try again or contact us to help resolve.',
                'give'
            );

            $this->handleResponse(response()->json($message));
            exit;
        }
    }

    /**
     * @inheritDoc
     */
    public function handleCreateSubscription(GatewayPaymentData $paymentData, GatewaySubscriptionData $subscriptionData)
    {
        try {
            $command = $this->createSubscription($paymentData, $subscriptionData);
            $this->handleGatewaySubscriptionCommand($command, $paymentData, $subscriptionData);
        } catch (PaymentGatewayException $paymentGatewayException) {
            $message = $paymentGatewayException->getMessage() ?: __(
                'An unexpected error occurred from the payment gateway.  Please try again or contact us to help resolve.',
                'give'
            );

            $this->handleResponse(response()->json($message));
            exit;
        } catch (Exception $exception) {
            PaymentGatewayLog::error($exception->getMessage());

            $message = __(
                'An unexpected error occurred while processing your donation.  Please try again or contact us to help resolve.',
                'give'
            );

            $this->handleResponse(response()->json($message));
            exit;
        }
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
     * Handle gateway command
     *
     * @unreleased
     *
     * @param  GatewayCommand  $command
     * @param  GatewayPaymentData  $gatewayPaymentData
     * @throws TypeNotSupported
     */
    public function handleGatewayPaymentCommand(GatewayCommand $command, GatewayPaymentData $gatewayPaymentData)
    {
        if ($command instanceof PaymentComplete) {
            give(PaymentCompleteHandler::class)->__invoke(
                $command,
                $gatewayPaymentData->paymentId
            );

            $response = response()->redirectTo($gatewayPaymentData->redirectUrl);

            $this->handleResponse($response);
        }

        throw new TypeNotSupported(
            sprintf(
                "Return type must be an instance of %s",
                GatewayCommand::class
            )
        );
    }

    /**
     * Handle gateway subscription command
     *
     * @unreleased
     *
     * @param  GatewayCommand  $command
     * @param  GatewayPaymentData  $gatewayPaymentData
     * @param  GatewaySubscriptionData  $gatewaySubscriptionData
     * @throws TypeNotSupported
     */
    public function handleGatewaySubscriptionCommand(
        GatewayCommand $command,
        GatewayPaymentData $gatewayPaymentData,
        GatewaySubscriptionData $gatewaySubscriptionData
    ) {
        if ($command instanceof SubscriptionComplete) {
            give(SubscriptionCompleteHandler::class)->__invoke(
                $command,
                $gatewaySubscriptionData->subscriptionId,
                $gatewayPaymentData->paymentId
            );

            $response = response()->redirectTo($gatewayPaymentData->redirectUrl);

            $this->handleResponse($response);
        }

        throw new TypeNotSupported(
            sprintf(
                "Return type must be an instance of %s",
                GatewayCommand::class
            )
        );
    }


    /**
     * Handle Response
     *
     * @unreleased
     *
     * @param  RedirectResponse|JsonResponse  $type
     */
    private function handleResponse($type)
    {
        if ($type instanceof RedirectResponse) {
            wp_redirect($type->getTargetUrl());
            exit;
        }

        if ($type instanceof JsonResponse) {
            wp_send_json(['data' => $type->getData()]);
        }
    }
}
