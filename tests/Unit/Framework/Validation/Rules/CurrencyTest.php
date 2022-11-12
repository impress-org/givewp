<?php

declare(strict_types=1);

namespace GiveTests\Unit\Framework\Validation\Rules;

use Give\Framework\Validation\Rules\Currency;
use GiveTests\TestCase;
class CurrencyTest extends TestCase
{
    /**
     * @unreleased
     * @dataProvider currencyProvider
     */
    public function testCurrencyValidations($currency, $shouldPass)
    {
        $rule = new Currency();

        if ( $shouldPass ) {
            self::assertValidationRulePassed($rule, $currency);
        } else {
            self::assertValidationRuleFailed($rule, $currency);
        }
    }

    /**
     * @unreleased
     */
    public function currencyProvider(): array
    {
        return [
            // normal
            ['USD', true],
            ['CAD', true],

            // should not be case-sensitive
            ['jpy', true],
            ['EuR', true],

            // should fail
            ['US', false],
            ['USDD', false],
            ['US D', false],
            ['US-D', false],
            ['ABC', false],
            ['123', false],
        ];
    }
}
