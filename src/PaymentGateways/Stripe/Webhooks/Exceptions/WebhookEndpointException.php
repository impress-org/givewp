<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Stripe\Exception\ApiErrorException;

/**
 * Thrown when a webhook endpoint request cannot be made or Stripe rejects it.
 *
 * @since TBD
 */
final class WebhookEndpointException extends Exception
{
    /**
     * @var int|null
     */
    private $httpStatus;

    /**
     * @var string|null
     */
    private $errorType;

    /**
     * @var string|null
     */
    private $requestId;

    /**
     * @since TBD
     */
    public static function fromStripe(ApiErrorException $exception): self
    {
        $self = new self($exception->getMessage(), 0, $exception);

        $self->httpStatus = $exception->getHttpStatus();
        $self->errorType = $exception->getError() ? $exception->getError()->type : null;
        $self->requestId = $exception->getRequestId();

        return $self;
    }

    /**
     * @since TBD
     */
    public static function missingAccessToken(string $mode): self
    {
        return new self(sprintf('No usable %s access token is stored for this Stripe account.', esc_html($mode)));
    }

    /**
     * @since TBD
     */
    public static function invalidMode(string $mode): self
    {
        return new self(sprintf('Invalid Stripe mode "%s". Expected "live" or "test".', esc_html($mode)));
    }

    /**
     * @since TBD
     */
    public static function invalidEndpointId(string $endpointId): self
    {
        return new self(sprintf('"%s" is not a Stripe webhook endpoint id.', esc_html($endpointId)));
    }

    /**
     * HTTP status of the Stripe response, or null when the request failed before Stripe answered.
     *
     * @since TBD
     */
    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /**
     * Stripe error type, for example "invalid_request_error" or "authentication_error".
     *
     * @since TBD
     */
    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    /**
     * Id of the Stripe request (`req_...`), which finds the call in the Stripe Dashboard logs.
     *
     * @since TBD
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
