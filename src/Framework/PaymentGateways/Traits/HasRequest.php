<?php

namespace Give\Framework\PaymentGateways\Traits;

trait HasRequest {
    /**
     * @var array
     */
    private $request;

    /**
     * @unreleased
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->request[$key];
    }
    /**
     *
     * @unreleased
     * @return $this
     */
    public function request(): self
    {
        $request = file_get_contents('php://input');

        $this->request = json_decode($request, true);

        return $this;
    }
}
