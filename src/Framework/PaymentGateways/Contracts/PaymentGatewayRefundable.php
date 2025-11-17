<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;

/**
 * @since 4.6.0
 */
interface PaymentGatewayRefundable
{
    /**
     * @since 4.6.0
     *
     * @return GatewayCommand
     */
    public function refundDonation(Donation $donation);
}
