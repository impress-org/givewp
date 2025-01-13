<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Rules\HoneyPotRule;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @since 3.16.2
 */
class TestHoneyPotRule extends TestCase
{
    use RefreshDatabase;
    use HasValidationRules;

    /**
     * @since 3.16.2
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
     * @since 3.16.2
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
