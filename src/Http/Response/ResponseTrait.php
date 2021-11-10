<?php

namespace Give\Framework\Http\Response;

use Give\PaymentGateways\Exceptions\HttpResponseException;
use Give\PaymentGateways\Exceptions\Throwable;

trait ResponseTrait
{
    /**
     * The original content of the response.
     *
     * @var mixed
     */
    public $original;

    /**
     * The exception that triggered the error response (if applicable).
     *
     * @var Throwable|null
     */
    public $exception;

    /**
     * Get the status code for the response.
     *
     * @return int
     */
    public function status()
    {
        return $this->getStatusCode();
    }

    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * Get the original response content.
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        $original = $this->original;

        return $original instanceof self ? $original->{__FUNCTION__}() : $original;
    }

    /**
     * Get the callback of the response.
     *
     * @return string|null
     */
    public function getCallback()
    {
        return isset($this->callback) ? $this->callback : null;
    }

    /**
     * Set the exception to attach to the response.
     *
     * @param  Throwable  $e
     * @return $this
     */
    public function withException(Throwable $e)
    {
        $this->exception = $e;

        return $this;
    }

    /**
     * Throws the response in a HttpResponseException instance.
     *
     * @return void
     *
     * @throws HttpResponseException
     */
    public function throwResponse()
    {
        throw new HttpResponseException($this);
    }
}
