<?php

namespace Give\Framework\PaymentGateways\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Types\JsonResponse;
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
use Give\Subscriptions\Models\Subscription;


/**
 * @since 2.27.0
 */
class HandleGatewaySubscriptionCommand
{
    /**
     *
     * Handle gateway subscription command
     *
     * @since 2.27.0 return responses
     * @since 2.26.0 add RespondToBrowser command
     * @since 2.21.0 Handle RedirectOffsite response.
     * @since 2.18.0
     *
     * @return JsonResponse|RedirectResponse
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

            return new RedirectResponse(give_get_success_page_uri());
        }

        if ($command instanceof SubscriptionProcessing) {
            (new SubscriptionProcessingHandler($command, $subscription, $donation))();

            return new RedirectResponse(give_get_success_page_uri());
        }

        if ($command instanceof RedirectOffsite) {
            return (new RedirectOffsiteHandler())($command);
        }

        if ($command instanceof RespondToBrowser) {
            return (new RespondToBrowserHandler())($command);
        }

        throw new TypeNotSupported(
            sprintf(
                "Return type must be an instance of %s",
                GatewayCommand::class
            )
        );
    }
}
