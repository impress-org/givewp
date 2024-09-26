<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Rules\HoneyPotRule;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @unreleased
 */
class TestHoneyPotRule extends TestCase
{
    use RefreshDatabase;
    use HasValidationRules;

    /**
     * @unreleased
     * @dataProvider honeyPotProvider
     */
    public function testHoneyPotRule($value, bool $shouldBeValid): void
    {
        if (!$shouldBeValid) {
            $this->expectException(SpamDonationException::class);
        }

        $rule = new HoneyPotRule();

        self::assertValidationRulePassed($rule, $value);
    }

    /**
     * @unreleased
     *
     * @return array<int, array<mixed, bool>>
     */
    public function honeyPotProvider(): array
    {
        return [
            // Valid
            ['', true],
            [null, true],

            // Invalid
            ['123', false],
            ['anything', false],
            [123, false],
            [['123'], false],
            [[123], false],
        ];
    }
}
