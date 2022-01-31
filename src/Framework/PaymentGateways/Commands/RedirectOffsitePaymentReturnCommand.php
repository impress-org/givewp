<?php

namespace Give\Framework\PaymentGateways\Commands;

/**
 * Extend this class to define commands(class) for handling redirect for onsite/offsite payment.
 *
 * @unreleased
 */
abstract class RedirectOffsitePaymentReturnCommand implements GatewayCommand
{
    protected $donationId;

    /**
     * @param string|null $donationId
     *
     * @return static
     */
    public static function make($donationId = null)
    {
        return new static($donationId);
    }

    /**
     * @unreleased
     *
     * @param string|null $donationId
     */
    public function __construct($donationId = null)
    {
        $this->donationId = $donationId;
    }

    /**
     * Get redirect url.
     *
     * @unreleased
     *
     * @param string
     *
     * @return string
     */
    abstract public function getUrl($donationFormPageUrl = null);
}
