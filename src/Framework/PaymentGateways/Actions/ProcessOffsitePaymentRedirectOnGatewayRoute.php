<?php

namespace Give\Framework\PaymentGateways\Actions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentAbandonedHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCancelledHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentFailedHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentProcessingHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentRefundedHandler;
use Give\Framework\PaymentGateways\CommandHandlers\ReturnOffsitePaymentReturnHandler;
use Give\Framework\PaymentGateways\Commands\PaymentAbandoned;
use Give\Framework\PaymentGateways\Commands\PaymentCancelled;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentFailed;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsitePaymentFailedReturn;
use Give\Framework\PaymentGateways\Commands\RedirectOffsitePaymentSuccessReturn;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Helpers\Gateway;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Session\SessionDonation\DonationAccessor;

use function Give\Framework\Http\Response\response;

/**
 * This handle responses from payment gateways for legacy donation flow.
 *
 * @unreleased
 */
class ProcessOffsitePaymentRedirectOnGatewayRoute
{
    /**
     * @var PaymentGateway
     */
    private $paymentGateway;

    /**
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Handle gateway route method
     *
     * @param int $donationId
     * @param string $method
     *
     * @unreleased
     *
     * @return void
     * TODO: create and handle failed payment command handler
     */
    public function handleGatewayRouteMethod($donationId, $method)
    {
        try {
            $command = $this->paymentGateway->$method($donationId);

            if ($command instanceof PaymentComplete) {
                PaymentCompleteHandler::make($command)->handle($donationId);
                ReturnOffsitePaymentReturnHandler::make(RedirectOffsitePaymentSuccessReturn::make($donationId))
                                                 ->handle();
            }

            if ($command instanceof PaymentProcessing) {
                PaymentProcessingHandler::make($command)->handle($donationId);
                ReturnOffsitePaymentReturnHandler::make(RedirectOffsitePaymentSuccessReturn::make($donationId))
                                                 ->handle();
            }

            if ($command instanceof PaymentFailed) {
                PaymentFailedHandler::make($command)->handle($donationId);
                ReturnOffsitePaymentReturnHandler::make(RedirectOffsitePaymentFailedReturn::make($donationId))
                                                 ->handle();
            }

            if ($command instanceof PaymentCancelled) {
                PaymentCancelledHandler::make($command)->handle($donationId);
                ReturnOffsitePaymentReturnHandler::make(RedirectOffsitePaymentFailedReturn::make($donationId))
                                                 ->handle();
            }
        } catch (PaymentGatewayException $paymentGatewayException) {
            $this->paymentGateway->handleResponse(response()->json($paymentGatewayException->getMessage()));
            exit;
        } catch (Exception $exception) {
            PaymentGatewayLog::error($exception->getMessage());

            $message = __(
                'An unexpected error occurred while processing your donation.  Please try again or contact us to help resolve.',
                'give'
            );

            $this->paymentGateway->handleResponse(response()->json($message));
            exit;
        }
    }
}
