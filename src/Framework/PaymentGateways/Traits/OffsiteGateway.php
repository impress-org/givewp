<?php

namespace Give\Framework\PaymentGateways\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Actions\GenerateReturnUrlFromRedirectOffsite;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;

use function Give\Framework\Http\Response\response;

trait OffsiteGateway
{
    /**
     * Return from offsite redirect
     *
     * @unreleased
     *
     * @return GatewayCommand
     * @throws PaymentGatewayException|Exception
     */
    abstract public function returnFromOffsiteRedirect();

    /**
     * Generate return url from redirect offsite
     *
     * @param  array|null  $args  - associative array of query args
     * @return string
     */
    public function generateReturnUrlFromRedirectOffsite($paymentId, $args = null)
    {
        /** @var GenerateReturnUrlFromRedirectOffsite $action */
        $action = give(GenerateReturnUrlFromRedirectOffsite::class);

        return $action($this->getId(), 'handleReturnFromOffsiteRedirect', $paymentId, $args);
    }

    /**
     * Handle returning from offsite redirect
     *
     * @param  int  $paymentId
     *
     * @unreleased
     *
     * @return void
     */
    public function handleReturnFromOffsiteRedirect($paymentId)
    {
        try {
            $command = $this->returnFromOffsiteRedirect();
            if ($command instanceof PaymentComplete) {
                give(PaymentCompleteHandler::class)->__invoke(
                    $command,
                    $paymentId
                );

                $response = response()->redirectTo(give_get_success_page_uri());

                $this->handleResponse($response);
            }
        } catch (PaymentGatewayException $paymentGatewayException) {
            $this->handleResponse(response()->json($paymentGatewayException->getMessage()));
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
}