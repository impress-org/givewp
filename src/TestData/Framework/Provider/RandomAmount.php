<?php

namespace Give\TestData\Framework\Provider;

class RandomAmount extends RandomProvider
{

    /** @var array [ int, ... ] */
    protected $amounts = [
        10,
        25,
        50,
        100,
        250,
    ];

    public function __invoke()
    {
        return $this->faker->randomElement($this->amounts);
    }
}
