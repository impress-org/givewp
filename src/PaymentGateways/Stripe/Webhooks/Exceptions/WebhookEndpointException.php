<?php

/**
 * @since 4.20.0
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Stripe\Exception\ApiErrorException;

/**
 * Thrown when a webhook endpoint request cannot be made or Stripe rejects it.
 *
 * @since 4.20.0
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
     * @since 4.20.0
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
     * @since 4.20.0
     */
    public static function missingAccessToken(string $mode): self
    {
        return new self(sprintf('No usable %s access token is stored for this Stripe account.', esc_html($mode)));
    }

    /**
     * @since 4.20.0
     */
    public static function invalidMode(string $mode): self
    {
        return new self(sprintf('Invalid Stripe mode "%s". Expected "live" or "test".', esc_html($mode)));
    }

    /**
     * @since 4.20.0
     */
    public static function invalidEndpointId(string $endpointId): self
    {
        return new self(sprintf('"%s" is not a Stripe webhook endpoint id.', esc_html($endpointId)));
    }

    /**
     * HTTP status of the Stripe response, or null when the request failed before Stripe answered.
     *
     * @since 4.20.0
     */
    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /**
     * Stripe error type, for example "invalid_request_error" or "authentication_error".
     *
     * @since 4.20.0
     */
    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    /**
     * Id of the Stripe request (`req_...`), which finds the call in the Stripe Dashboard logs.
     *
     * @since 4.20.0
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
