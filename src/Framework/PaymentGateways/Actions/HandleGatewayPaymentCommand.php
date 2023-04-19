<?php

namespace Give\Framework\PaymentGateways\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentProcessingHandler;
use Give\Framework\PaymentGateways\CommandHandlers\RedirectOffsiteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\RespondToBrowserHandler;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;


/**
 * @unreleased
 */
class HandleGatewayPaymentCommand
{
    use HandleHttpResponses;

    /**
     * Handle gateway command
     *
     * @since 2.18.0
     *
     * @throws TypeNotSupported
     * @throws Exception
     */
    public function __invoke(GatewayCommand $command, Donation $donation)
    {
        if ($command instanceof PaymentComplete) {
            $handler = new PaymentCompleteHandler($command);

            $handler->handle($donation);

            $response = new RedirectResponse(give_get_success_page_uri());

            $this->handleResponse($response);
        }

        if ($command instanceof PaymentProcessing) {
            $handler = new PaymentProcessingHandler($command);

            $handler->handle($donation);

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