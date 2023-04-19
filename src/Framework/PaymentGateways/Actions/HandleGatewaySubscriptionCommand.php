<?php

namespace Give\Framework\PaymentGateways\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\CommandHandlers\RedirectOffsiteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\RespondToBrowserHandler;
use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionCompleteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionProcessingHandler;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Subscriptions\Models\Subscription;


/**
 * @unreleased
 */
class HandleGatewaySubscriptionCommand
{
    use HandleHttpResponses;

    /**
     * Handle gateway subscription command
     *
     * @since 2.26.0 add RespondToBrowser command
     * @since 2.21.0 Handle RedirectOffsite response.
     * @since 2.18.0
     *
     * @throws TypeNotSupported
     * @throws Exception
     */
    public function __invoke(
        GatewayCommand $command,
        Donation $donation,
        Subscription $subscription
    ) {
        if ($command instanceof SubscriptionComplete) {
            (new SubscriptionCompleteHandler())($command, $subscription, $donation);

            $response = new RedirectResponse(give_get_success_page_uri());

            $this->handleResponse($response);
        }

        if ($command instanceof SubscriptionProcessing) {
            (new SubscriptionProcessingHandler($command, $subscription, $donation))();

            $response = new RedirectResponse(give_get_success_page_uri());

            $this->handleResponse($response);
        }

        if ($command instanceof RedirectOffsite) {
            $response = (new RedirectOffsiteHandler())($command);

            $this->handleResponse($response);
        }

        if ($command instanceof RespondToBrowser) {
            $response = (new RespondToBrowserHandler())($command);

            $this->handleResponse($response);
        }

        throw new TypeNotSupported(
            sprintf(
                "Return type must be an instance of %s",
                GatewayCommand::class
            )
        );
    }
}