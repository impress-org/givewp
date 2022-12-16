<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\Validation;

use Closure;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Validation\Contracts\Sanitizer;
use Give\Framework\Validation\Contracts\ValidationRule;
use Give\Framework\Validation\ValidationRuleSet;
use Give\Framework\Validation\ValidationRulesRegistrar;
use Give\Framework\Validation\Validator;
use Give\Tests\TestCase;

/**
 * @covers \Give\Framework\Validation\Validator
 *
 * @unreleased
 */
class ValidatorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockValidationRulesRegister();
    }

    /**
     * @unreleased
     */
    public function testValidatorPasses()
    {
        $validator = new Validator(
            [
                'name' => give(ValidationRuleSet::class)->rules('required'),
                'email' => give(ValidationRuleSet::class)->rules('required'),
            ],
            [
                'name' => 'Bill Murray',
                'email' => 'bill@example.com',
            ]
        );

        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
    }

    /**
     * @unreleased
     */
    public function testValidatorAcceptsArraysAsRules()
    {
        $validator = new Validator([
            'foo' => ['required'],
            'bar' => ['required'],
        ], [
            'foo' => 'foo',
            'bar' => 'bar',
        ]);

        $this->assertTrue($validator->passes());
    }

    /**
     * @unreleased
     */
    public function testFailingValidations()
    {
        $validator = new Validator([
            'foo' => ['required'],
            'bar' => ['required'],
        ], [
            'foo' => 'foo',
            'bar' => '',
        ]);

        self::assertTrue($validator->fails());
        self::assertFalse($validator->passes());
    }

    /**
     * @unreleased
     */
    public function testReturnsErrorsForFailedValidations()
    {
        $validator = new Validator([
            'foo' => ['required'],
            'bar' => ['required'],
        ], [
            'foo' => 'foo',
            'bar' => '',
        ]);

        self::assertEquals([
            'bar' => 'bar required',
        ], $validator->errors());
    }

    /**
     * @unreleased
     */
    public function testUsesLabelsWhenAvailableInErrorMessage()
    {
        $validator = new Validator([
            'foo' => ['required'],
            'bar' => ['required'],
        ], [
            'foo' => '',
            'bar' => '',
        ], [
            'bar' => 'Bar',
        ]);

        self::assertEquals([
            'foo' => 'foo required',
            'bar' => 'Bar required',
        ], $validator->errors());
    }

    /**
     * @unreleased
     */
    public function testReturnsValidatedValues()
    {
        $validator = new Validator([
            'foo' => ['required'],
            'bar' => ['required'],
        ], [
            'foo' => 'foo',
            'bar' => 'bar',
        ]);

        self::assertEquals([
            'foo' => 'foo',
            'bar' => 'bar',
        ], $validator->validated());
    }

    /**
     * @unreleased
     */
    public function testValuesWithoutRulesAreOmitted()
    {
        $validator = new Validator([
            'foo' => ['required'],
        ], [
            'foo' => 'foo',
            'bar' => 'bar',
        ]);

        self::assertEquals([
            'foo' => 'foo',
        ], $validator->validated());
    }

    /**
     * @unreleased
     */
    public function testRuleArraysWithoutRulesAreConsideredOptional()
    {
        // When no rules are specified, the field is considered optional. This simply means that
        // whatever value is passed in will be returned as validated. Keep in mind that if the rule
        // is entirely omitted then the value will also be omitted.
        $validator = new Validator([
            'foo' => [],
        ], [
            'foo' => 'foo',
            'bar' => 'bar',
        ]);

        self::assertEquals([
            'foo' => 'foo',
        ], $validator->validated());
    }

    /**
     * @unreleased
     */
    public function testRulesWithSanitizationAreApplied()
    {
        $validator = new Validator([
            'name' => ['required'],
            'age' => ['required', 'integer'],
        ], [
            'name' => 'Bill Murray',
            'age' => '72',
        ]);

        self::assertSame([
            'name' => 'Bill Murray',
            'age' => 72,
        ], $validator->validated());
    }

    /**
     * @unreleased
     */
    public function testInvalidRulesThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Validation rules must be an instance of ValidationRuleSet or a compatible array'
        );

        new Validator([
            'foo' => 'wrong',
        ], [
            'foo' => 'foo',
        ]);
    }

    /**
     * @unreleased
     */
    public function testRulesWithoutValuesThrowsAnException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing values for rules');

        new Validator([
            'foo' => ['required'],
        ], []);
    }

    /**
     * Adds the validation register to the container, and adds a mock validation rule
     *
     * @unreleased
     */
    private function mockValidationRulesRegister()
    {
        give()->singleton(ValidationRulesRegistrar::class, function () {
            $register = new ValidationRulesRegistrar();
            $register->register(
                MockRequiredRule::class,
                MockIntegerRule::class
            );

            return $register;
        });
    }
}

class MockRequiredRule implements ValidationRule
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'required';
    }

    /**
     * @inheritDoc
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (empty($value)) {
            $fail('{field} required');
        }
    }
}

class MockIntegerRule implements ValidationRule, Sanitizer
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'integer';
    }

    /**
     * @inheritDoc
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (!is_numeric($value)) {
            $fail('{field} must be an integer');
        }
    }

    /**
     * @inheritDoc
     */
    public function sanitize($value)
    {
        return (int)$value;
    }
}
