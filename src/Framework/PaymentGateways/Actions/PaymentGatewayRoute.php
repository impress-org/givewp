<?php

namespace Give\Framework\PaymentGateways\Actions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentAbandonedHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentProcessingHandler;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentRefundedHandler;
use Give\Framework\PaymentGateways\Commands\PaymentAbandoned;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsiteFailedPayment;
use Give\Framework\PaymentGateways\Commands\RedirectOffsiteSuccessPayment;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\Session\SessionDonation\DonationAccessor;

use function Give\Framework\Http\Response\response;

/**
 * This handle responses from payment gateways for legacy donation flow.
 *
 * @unreleased
 */
class PaymentGatewayRoute
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
     * Return whether payment gateway is offsite type.
     *
     * @unreleased
     *
     * @return bool
     */
    private function isOffsitePaymentGateway()
    {
        return $this->paymentGateway instanceof OffSitePaymentGateway;
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
                $this->paymentGateway->handleResponse(
                    response()->redirectTo($this->getSuccessPageUrl($donationId))
                );
            }

            if ($command instanceof PaymentProcessing) {
                PaymentProcessingHandler::make($command)->handle($donationId);
                $this->paymentGateway->handleResponse(
                    response()->redirectTo($this->getSuccessPageUrl($donationId))
                );
            }

            if ($command instanceof PaymentAbandoned) {
                PaymentAbandonedHandler::make($command)->handle($donationId);
            }

            if ($command instanceof PaymentRefunded) {
                PaymentRefundedHandler::make($command)->handle($donationId);
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

    /**
     * @unreleased
     *
     * @param int $donationId
     *
     * @return RedirectResponse|string
     */
    private function getSuccessPageUrl($donationId)
    {
        return $this->isOffsitePaymentGateway() ?
            (new RedirectOffsiteSuccessPayment($donationId))
                ->getUrl((new DonationAccessor())->get()->currentUrl) :
            give_get_success_page_uri();
    }

    /**
     * @unreleased
     *
     * @param int $donationId
     *
     * @return RedirectResponse|mixed
     */
    private function getFailedPageUrl($donationId)
    {
        return $this->isOffsitePaymentGateway() ?
            (new RedirectOffsiteFailedPayment($donationId))
                ->getUrl((new DonationAccessor())->get()->currentUrl) :
            give_get_failed_transaction_uri();
    }

}
