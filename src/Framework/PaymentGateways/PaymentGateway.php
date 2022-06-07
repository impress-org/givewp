<?php

namespace Give\Framework\PaymentGateways;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\LegacyPaymentGateways\Contracts\LegacyPaymentGatewayInterface;
use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentProcessingHandler;
use Give\Framework\PaymentGateways\CommandHandlers\RedirectOffsiteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\RespondToBrowserHandler;
use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionCompleteHandler;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionAmountEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionPaymentMethodEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionTransactionsSynchronizable;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\Routes\RouteSignature;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Framework\PaymentGateways\Traits\HasRouteMethods;
use Give\Helpers\Call;
use Give\Subscriptions\Models\Subscription;

use function Give\Framework\Http\Response\response;

/**
 * @since 2.18.0
 */
abstract class PaymentGateway implements PaymentGatewayInterface, LegacyPaymentGatewayInterface
{
    use HandleHttpResponses;
    use HasRouteMethods {
        supportsMethodRoute as protected SupportsOwnMethodRoute;
        callRouteMethod as protected CallOwnRouteMethod;
    }

    /**
     * @since 2.20.0 Change variable type to SubscriptionModule.
     * @var SubscriptionModule $subscriptionModule
     */
    public $subscriptionModule;

    /**
     * @since 2.20.0 Change first argument type to SubscriptionModule abstract class.
     * @since 2.18.0
     *
     * @param SubscriptionModule|null $subscriptionModule
     */
    public function __construct(SubscriptionModule $subscriptionModule = null)
    {
        if ($subscriptionModule !== null) {
            $subscriptionModule->setGateway($this);
        }

        $this->subscriptionModule = $subscriptionModule;
    }

    /**
     * @inheritDoc
     */
    public function supportsSubscriptions(): bool
    {
        return isset($this->subscriptionModule);
    }

    /**
     * @unreleased Handle PHP exception.
     * @since 2.19.0
     */
    public function handleCreatePayment(Donation $donation)
    {
        try {
            $command = $this->createPayment($donation);
            $this->handleGatewayPaymentCommand($command, $donation);
        } catch (\Exception $exception) {
            PaymentGatewayLog::error(
                $exception->getMessage(),
                [
                    'Payment Gateway' => $this->getId(),
                    'Donation' => $donation,
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
     * @unreleased Handle PHP exception.
     * @since 2.19.0
     *
     */
    public function handleCreateSubscription(Donation $donation, Subscription $subscription)
    {
        try {
            $command = $this->createSubscription($donation, $subscription);
            $this->handleGatewaySubscriptionCommand($command, $donation, $subscription);
        } catch (\Exception $exception) {
            PaymentGatewayLog::error(
                $exception->getMessage(),
                [
                    'Payment Gateway' => $this->getId(),
                    'Donation' => $donation,
                    'Subscription' => $subscription,
                ]
            );

            $message = __(
                'An unexpected error occurred while processing the subscription.  Please try again or contact the site administrator.',
                'give'
            );

            $this->handleExceptionResponse($exception, $message);
        }
    }

    /**
     * If a subscription module isn't wanted this method can be overridden by a child class instead.
     * Just make sure to override the supportsSubscriptions method as well.
     *
     * @inheritDoc
     */
    public function createSubscription(Donation $donation, Subscription $subscription): GatewayCommand
    {
        return $this->subscriptionModule->createSubscription($donation, $subscription);
    }

    /**
     * @inheritDoc
     */
    public function cancelSubscription(Subscription $subscription)
    {
        $this->subscriptionModule->cancelSubscription($subscription);
    }

    /**
     * @inheritDoc
     */
    public function canSyncSubscriptionWithPaymentGateway(): bool
    {
        return $this instanceof SubscriptionTransactionsSynchronizable
            || $this->subscriptionModule->canSyncSubscriptionWithPaymentGateway();
    }

    /**
     * @inheritDoc
     */
    public function canUpdateSubscriptionAmount(): bool
    {
        return $this instanceof SubscriptionAmountEditable
            || $this->subscriptionModule->canUpdateSubscriptionAmount();
    }

    /**
     * @inheritDoc
     */
    public function canUpdateSubscriptionPaymentMethod(): bool
    {
        return $this instanceof SubscriptionPaymentMethodEditable
            || $this->subscriptionModule->canUpdateSubscriptionPaymentMethod();
    }

    /**
     * Handle gateway command
     *
     * @since 2.18.0
     *
     * @throws TypeNotSupported
     * @throws Exception
     */
    public function handleGatewayPaymentCommand(GatewayCommand $command, Donation $donation)
    {
        if ($command instanceof PaymentComplete) {
            $handler = new PaymentCompleteHandler($command);

            $handler->handle($donation);

            $response = response()->redirectTo(give_get_success_page_uri());

            $this->handleResponse($response);
        }

        if ($command instanceof PaymentProcessing) {
            $handler = new PaymentProcessingHandler($command);

            $handler->handle($donation);

            $response = response()->redirectTo(give_get_success_page_uri());

            $this->handleResponse($response);
        }

        if ($command instanceof RedirectOffsite) {
            $response = Call::invoke(RedirectOffsiteHandler::class, $command);

            $this->handleResponse($response);
        }

        if ($command instanceof RespondToBrowser) {
            $response = Call::invoke(RespondToBrowserHandler::class, $command);

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
     * @since 2.18.0
     *
     * @throws TypeNotSupported
     */
    public function handleGatewaySubscriptionCommand(
        GatewayCommand $command,
        Donation $donation,
        Subscription $subscription
    ) {
        if ($command instanceof SubscriptionComplete) {
            Call::invoke(
                SubscriptionCompleteHandler::class,
                $command,
                $subscription,
                $donation
            );

            $response = response()->redirectTo(give_get_success_page_uri());

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
     * Generate gateway route url
     *
     * @since 2.18.0
     * @since 2.19.0 remove $donationId param in favor of args
     */
    public function generateGatewayRouteUrl(string $gatewayMethod, array $args = []): string
    {
        return Call::invoke(GenerateGatewayRouteUrl::class, $this->getId(), $gatewayMethod, $args);
    }

    /**
     * Generate secure gateway route url
     *
     * @since 2.19.5 replace nonce with hash and expiration
     * @since 2.19.4 replace RouteSignature args with unique donationId
     * @since 2.19.0
     */
    public function generateSecureGatewayRouteUrl(string $gatewayMethod, int $donationId, array $args = []): string
    {
        $signature = new RouteSignature($this->getId(), $gatewayMethod, $donationId);

        return Call::invoke(
            GenerateGatewayRouteUrl::class,
            $this->getId(),
            $gatewayMethod,
            array_merge($args, [
                'give-route-signature' => $signature->toHash(),
                'give-route-signature-id' => $donationId,
                'give-route-signature-expiration' => $signature->expiration,
            ])
        );
    }

    /**
     * Handle response on basis of request mode when exception occurs:
     * 1. Redirect to donation form if donation form submit.
     * 2. Return json response if processing payment on ajax.
     *
     * @unreleased Handle PHP exception.
     * @since 2.19.0
     */
    private function handleExceptionResponse(\Exception $exception, string $message)
    {
        if ($exception instanceof PaymentGatewayException) {
            $message = $exception->getMessage();
        }

        if (wp_doing_ajax()) {
            $this->handleResponse(response()->json($message));
        }

        give_set_error('PaymentGatewayException', $message);
        give_send_back_to_checkout();
    }

    /**
     * @since 2.20.0
     */
    public function supportsMethodRoute(string $method): bool
    {
        if ($this->subscriptionModule && $this->subscriptionModule->supportsMethodRoute($method)) {
            return true;
        }

        return $this->supportsOwnMethodRoute($method);
    }

    /**
     * @since 2.20.0
     *
     * @param string $method
     *
     * @throws Exception
     */
    public function callRouteMethod($method, $queryParams)
    {
        if ($this->subscriptionModule && $this->subscriptionModule->supportsMethodRoute($method)) {
            return $this->subscriptionModule->callRouteMethod($method, $queryParams);
        }

        return $this->callOwnRouteMethod($method, $queryParams);
    }
}
