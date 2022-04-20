<?php

namespace Give\Framework\PaymentGateways\Contracts\Donation;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;

/**
 * @unreleased
 */
interface CanRefundDonation
{
    /**
     * @unreleased
     *
     * @param Donation $donation
     *
     * @return void
     * @throws PaymentGatewayException | Exception
     */
    public function refundDonation(Donation $donation);
}
