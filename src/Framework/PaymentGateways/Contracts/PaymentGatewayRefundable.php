<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;

/**
 * @unreleased
 */
interface PaymentGatewayRefundable
{
    /**
     * @unreleased
     *
     * @return GatewayCommand
     */
    public function refundDonation(Donation $donation);
}
