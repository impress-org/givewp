<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

class ThrowingTestGateway extends TestGateway
{
    public static function id(): string
    {
        return 'throwing-test-gateway';
    }

    public function getId(): string
    {
        return self::id();
    }

    /**
     * @throws \Exception
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        throw new \Exception('Simulated refund error', 42);
    }
}
