<?php

namespace Give\Tests\TestTraits;

use Faker\Generator;

/**
 * @unreleased
 */
trait Faker
{
    /**
     * @unreleased
     *
     * @return Generator
     */
    public function faker(): Generator
    {
        return give()->make(Generator::class);
    }
}
