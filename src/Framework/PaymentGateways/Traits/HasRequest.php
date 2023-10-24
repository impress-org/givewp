<?php

namespace Give\Framework\PaymentGateways\Traits;

trait HasRequest {
    /**
     * @var array
     */
    private $request;

    /**
     * @since 3.0.0
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->request[$key];
    }

    /**
     *
     * @since 3.0.0
     * @return $this
     */
    public function request(): self
    {
        $request = file_get_contents('php://input');

        $this->request = json_decode($request, true);

        return $this;
    }
}
