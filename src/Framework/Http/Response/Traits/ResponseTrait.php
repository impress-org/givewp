<?php

namespace Give\Framework\Http\Response\Traits;

use Exception;
use Give\Framework\Http\Exceptions\HttpResponseException;
use Give\Vendors\Symfony\Component\HttpFoundation\Cookie;
use Give\Vendors\Symfony\Component\HttpFoundation\HeaderBag;

/**
 * @since 2.18.0
 */
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
     * @var Exception|null
     */
    public $exception;

    /**
     * Get the status code for the response.
     *
     * @since 2.18.0
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
     * @since 2.18.0
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
     * @since 2.18.0
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        $original = $this->original;

        return $original instanceof self ? $original->{__FUNCTION__}() : $original;
    }

    /**
     * Set a header on the Response.
     *
     * @since 2.18.0
     *
     * @param  string  $key
     * @param  array|string  $values
     * @param  bool  $replace
     * @return $this
     */
    public function header($key, $values, $replace = true)
    {
        $this->headers->set($key, $values, $replace);

        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @since 2.18.0
     *
     * @param  HeaderBag|array  $headers
     * @return $this
     */
    public function withHeaders($headers)
    {
        if ($headers instanceof HeaderBag) {
            $headers = $headers->all();
        }

        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @since 2.18.0
     *
     * @param  Cookie|mixed  $cookie
     * @return $this
     */
    public function cookie($cookie)
    {
        return $this->withCookie(...func_get_args());
    }

    /**
     * Add a cookie to the response.
     *
     * @since 2.18.0
     *
     * @param  Cookie|mixed  $cookie
     * @return $this
     */
    public function withCookie($cookie)
    {
        if (is_string($cookie) && function_exists('cookie')) {
            $cookie = cookie(...func_get_args());
        }

        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * Expire a cookie when sending the response.
     *
     * @since 2.18.0
     *
     * @param  Cookie|mixed  $cookie
     * @param  string|null  $path
     * @param  string|null  $domain
     * @return $this
     */
    public function withoutCookie($cookie, $path = null, $domain = null)
    {
        if (is_string($cookie) && function_exists('cookie')) {
            $cookie = cookie($cookie, null, -2628000, $path, $domain);
        }

        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * Get the callback of the response.
     *
     * @since 2.18.0
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
     * @since 2.18.0
     *
     * @param  Exception  $e
     * @return $this
     */
    public function withException(Exception $e)
    {
        $this->exception = $e;

        return $this;
    }

    /**
     * Throws the response in a HttpResponseException instance.
     *
     * @since 2.18.0
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
