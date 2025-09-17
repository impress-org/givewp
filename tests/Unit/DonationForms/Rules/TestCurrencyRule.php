<?php

namespace Give\Tests\Unit\DonationForms\Rules;

use Give\DonationForms\Rules\CurrencyRule;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @unreleased
 */
class TestCurrencyRule extends TestCase
{
    use RefreshDatabase;
    use HasValidationRules;

    /**
     * @unreleased
     * @dataProvider currencyProvider
     */
    public function testCurrencyRule($value, bool $shouldBeValid): void
    {
        $rule = new CurrencyRule();

        if ($shouldBeValid) {
            self::assertValidationRulePassed($rule, $value);
        } else {
            self::assertValidationRuleFailed($rule, $value);
        }
    }

    /**
     * @unreleased
     *
     * @return array<int, array<mixed, bool>>
     */
    public function currencyProvider(): array
    {
        // Get some common valid currencies from GiveWP
        $validCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];

        // Filter to only include currencies that are actually supported by GiveWP
        $supportedCurrencies = array_keys(give_get_currencies_list());
        $validCurrencies = array_intersect($validCurrencies, $supportedCurrencies);

        // If no common currencies are supported, use the first few supported ones
        if (empty($validCurrencies)) {
            $validCurrencies = array_slice($supportedCurrencies, 0, 3);
        }

        $testCases = [
            // Valid cases - empty values should pass
            ['', true],
            [null, true],
        ];

        // Add valid currency codes
        foreach ($validCurrencies as $currency) {
            $testCases[] = [$currency, true];
        }

        // Add invalid cases
        $testCases = array_merge($testCases, [
            // Invalid currency codes
            ['INVALID', false],
            ['XYZ', false],
            ['123', false],
            ['usd', false], // lowercase should fail
            ['USD ', false], // with space should fail
            [' USD', false], // with leading space should fail

            // Invalid data types (but note: false is considered empty and passes)
            [123, false],
            [['USD'], false],
            [['123'], false],
            [true, false],
            // false is considered empty() so it passes validation
        ]);

        return $testCases;
    }

    /**
     * @unreleased
     */
    public function testCurrencyRuleErrorMessage(): void
    {
        $rule = new CurrencyRule();
        $supportedCurrencies = array_keys(give_get_currencies_list());

        // Test that the error message includes the list of valid currencies
        $error = null;
        $fail = function ($message) use (&$error) {
            $error = $message;
        };

        $rule('INVALID', $fail, 'test_field', []);

        $this->assertNotNull($error, 'Validation rule should fail for invalid currency');
        $this->assertIsString($error);

        // Now we know $error is a string, so we can safely use it
        /** @var string $error */
        $this->assertStringContainsString('must be a valid currency', $error);
        $this->assertStringContainsString('Valid currencies are:', $error);
        $this->assertStringContainsString(implode(', ', $supportedCurrencies), $error);
    }

    /**
     * @unreleased
     */
    public function testCurrencyRuleId(): void
    {
        $this->assertEquals('giveCurrency', CurrencyRule::id());
    }

    /**
     * @unreleased
     */
    public function testCurrencyRuleFromString(): void
    {
        $rule = CurrencyRule::fromString();
        $this->assertInstanceOf(CurrencyRule::class, $rule);

        $ruleWithOptions = CurrencyRule::fromString('some-options');
        $this->assertInstanceOf(CurrencyRule::class, $ruleWithOptions);
    }
}
