<?php

namespace Give\Tests\Unit\DonationForms\Rules;

use Give\DonationForms\Rules\CurrencyRule;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @since 4.10.0
 */
class TestCurrencyRule extends TestCase
{
    use RefreshDatabase;
    use HasValidationRules;

    /**
     * @since 4.10.0
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
     * @since 4.10.0
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
            // Empty values should fail
            ['', false],
            [null, false],
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

            // Invalid length currency codes
            ['US', false], // too short
            ['USDD', false], // too long
            ['A', false], // single character
            ['ABCDE', false], // too long

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
     * @since 4.10.0
     */
    public function testCurrencyRuleErrorMessage(): void
    {
        $rule = new CurrencyRule();
        $supportedCurrencies = array_keys(give_get_currencies_list());

        // Test that the error message includes the list of valid currencies
        // Use 'XYZ' which is 3 characters and uppercase, so it won't trigger format validation
        $error = null;
        $fail = function ($message) use (&$error) {
            $error = $message;
        };

        $rule('XYZ', $fail, 'test_field', []);

        $this->assertNotNull($error, 'Validation rule should fail for invalid currency');
        $this->assertIsString($error);

        // Now we know $error is a string, so we can safely use it
        /** @var string $error */
        $this->assertStringContainsString('must be a valid currency', $error);
        $this->assertStringContainsString('Provided: XYZ', $error);
        $this->assertStringNotContainsString('Valid currencies are:', $error);
    }

    /**
     * @since 4.10.0
     */
    public function testCurrencyRuleFormatValidation(): void
    {
        $rule = new CurrencyRule();
        $supportedCurrencies = array_keys(give_get_currencies_list());

        if (empty($supportedCurrencies)) {
            $this->markTestSkipped('No supported currencies available for testing');
        }

        $testCurrency = $supportedCurrencies[0];
        $lowercaseCurrency = strtolower($testCurrency);

        // Test that lowercase currency provides helpful error message
        $error = null;
        $fail = function ($message) use (&$error) {
            $error = $message;
        };

        $rule($lowercaseCurrency, $fail, 'test_field', []);

        $this->assertNotNull($error, 'Validation rule should fail for lowercase currency');
        $this->assertIsString($error);

        // Now we know $error is a string, so we can safely use it
        /** @var string $error */
        $this->assertStringContainsString('must be a valid 3-letter currency code in uppercase format', $error);
        $this->assertStringContainsString('(example: USD)', $error);
        $this->assertStringNotContainsString('Provided:', $error);
        // Should not contain the full list of currencies in this specific error message
        $this->assertStringNotContainsString('Valid currencies are:', $error);
    }

    /**
     * @since 4.10.0
     */
    public function testCurrencyRuleInvalidCodeWithCorrectCase(): void
    {
        $rule = new CurrencyRule();

        // Test that invalid currency code (but with correct case format) shows general error
        $error = null;
        $fail = function ($message) use (&$error) {
            $error = $message;
        };

        $rule('XYZ', $fail, 'test_field', []);

        $this->assertNotNull($error, 'Validation rule should fail for invalid currency');
        $this->assertIsString($error);

        // Now we know $error is a string, so we can safely use it
        /** @var string $error */
        $this->assertStringContainsString('must be a valid currency', $error);
        $this->assertStringContainsString('Provided: XYZ', $error);
        $this->assertStringNotContainsString('Valid currencies are:', $error);
        // Should not contain the format-specific error message
        $this->assertStringNotContainsString('must be a valid 3-letter currency code in uppercase format', $error);
    }

    /**
     * @since 4.10.0
     */
    public function testCurrencyRuleInvalidLength(): void
    {
        $rule = new CurrencyRule();

        // Test that currency codes with wrong length show specific error
        $error = null;
        $fail = function ($message) use (&$error) {
            $error = $message;
        };

        $rule('US', $fail, 'test_field', []);

        $this->assertNotNull($error, 'Validation rule should fail for wrong length currency');
        $this->assertIsString($error);

        // Now we know $error is a string, so we can safely use it
        /** @var string $error */
        $this->assertStringContainsString('must be a valid 3-letter currency code in uppercase format', $error);
        $this->assertStringContainsString('(example: USD)', $error);
        $this->assertStringNotContainsString('Provided:', $error);
        // Should not contain the full list of currencies in this specific error message
        $this->assertStringNotContainsString('Valid currencies are:', $error);
    }

    /**
     * @since 4.10.0
     */
    public function testCurrencyRuleId(): void
    {
        $this->assertEquals('giveCurrency', CurrencyRule::id());
    }

    /**
     * @since 4.10.0
     */
    public function testCurrencyRuleFromString(): void
    {
        $rule = CurrencyRule::fromString();
        $this->assertInstanceOf(CurrencyRule::class, $rule);

        $ruleWithOptions = CurrencyRule::fromString('some-options');
        $this->assertInstanceOf(CurrencyRule::class, $ruleWithOptions);
    }

    /**
     * @since 4.10.0
     * @dataProvider validFormatProvider
     */
    public function testIsValidFormat($value, bool $expected): void
    {
        $rule = new CurrencyRule();

        // Use reflection to access the private method
        $reflection = new \ReflectionClass($rule);
        $method = $reflection->getMethod('isValidFormat');
        $method->setAccessible(true);

        $result = $method->invoke($rule, $value);
        $this->assertEquals($expected, $result);
    }

    /**
     * @since 4.10.0
     *
     * @return array<int, array<mixed, bool>>
     */
    public function validFormatProvider(): array
    {
        return [
            // Valid formats
            ['USD', true],
            ['EUR', true],
            ['GBP', true],
            ['CAD', true],

            // Invalid formats
            ['', false], // empty
            ['usd', false], // lowercase
            ['Usd', false], // mixed case
            ['US', false], // too short
            ['USDD', false], // too long
            ['123', false], // numbers
            ['US1', false], // mixed alphanumeric
            [[], false], // array
            [new \stdClass(), false], // object
            [null, false], // null
            [123, false], // integer
        ];
    }
}
