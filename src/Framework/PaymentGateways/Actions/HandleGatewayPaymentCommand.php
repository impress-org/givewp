<?php

namespace Give\Framework\PaymentGateways\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentPendingHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentProcessingHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentRefundedHandler;
use Give\Framework\PaymentGateways\CommandHandlers\RedirectOffsiteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\RespondToBrowserHandler;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentPending;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;


/**
 * @since 2.27.0
 */
class HandleGatewayPaymentCommand
{
    /**
     * Handle gateway command
     *
     * @since 3.0.0 Handle PaymentPending command
     * @since 2.29.0 Handle PaymentRefunded command
     * @since 2.27.0 return responses
     * @since 2.18.0
     *
     * @return JsonResponse|RedirectResponse
     * @throws TypeNotSupported|Exception
     */
    public function __invoke(GatewayCommand $command, Donation $donation)
    {
        if ($command instanceof PaymentComplete) {
            $handler = new PaymentCompleteHandler($command);

            $handler->handle($donation);

            return new RedirectResponse(give_get_success_page_uri());
        }

        if ($command instanceof PaymentProcessing) {
            $handler = new PaymentProcessingHandler($command);

            $handler->handle($donation);

            return new RedirectResponse(give_get_success_page_uri());
        }

        if ($command instanceof PaymentPending) {
            $handler = new PaymentPendingHandler($command);

            $handler->handle($donation);

            return new RedirectResponse(give_get_success_page_uri());
        }

        if ($command instanceof PaymentRefunded) {
            $handler = new PaymentRefundedHandler($command);
            $handler->handle($donation);
            $url = isset($_REQUEST['_wp_http_referer']) ? home_url($_REQUEST['_wp_http_referer']) : home_url('/');

            return new RedirectResponse($url);
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
