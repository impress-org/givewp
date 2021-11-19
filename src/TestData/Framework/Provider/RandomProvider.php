<?php

namespace Give\TestData\Framework\Provider;

use Faker\Generator;

abstract class RandomProvider implements ProviderContract
{

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    abstract public function __invoke();
}
