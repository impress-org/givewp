<?php

namespace Give\TestData\Framework\Provider;

class RandomGateway extends RandomProvider
{

    /** @var array [ gatewaySlug, ... ] */
    protected $gateways = [
        'paypal-commerce',
        'stripe',
        'manual',
        'manual_donation',
    ];

    public function __invoke()
    {
        $count = count($this->gateways);
        $index = $this->faker->biasedNumberBetween(0, $count - 1, $function = 'sqrt');

        return $this->gateways[$index];
    }
}
