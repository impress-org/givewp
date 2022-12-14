<?php

declare(strict_types=1);

namespace Unit\Framework\Validation;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Validation\Rules\Required;
use Give\Framework\Validation\Rules\Size;
use Give\Framework\Validation\ValidationRuleSet;
use Give\Framework\Validation\ValidationRulesRegistrar;
use Give\Tests\TestCase;

/**
 * @covers ValidationRuleSet
 *
 * @unreleased
 */
class ValidationRuleSetTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testRulesCanBePassedAsStrings()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules('required', 'size:5');

        self::assertCount(2, $rules);
    }

    /**
     * @unreleased
     */
    public function testRulesCanBePassedAsInstances()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules(new Required(), new Size(5));

        self::assertCount(2, $rules);
    }

    /**
     * @unreleased
     */
    public function testRulesCanBePassedAsClosures()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules(static function ($value, $fail) {
        });

        self::assertCount(1, $rules);
    }

    /**
     * @unreleased
     */
    public function testCheckingHasRule()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules('required', 'size:5');

        self::assertTrue($rules->hasRule('required'));
        self::assertTrue($rules->hasRule('size'));
        self::assertFalse($rules->hasRule('email'));
    }

    /**
     * @unreleased
     */
    public function testGettingARule()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules('required', 'size:5');

        self::assertInstanceOf(Required::class, $rules->getRule('required'));
        self::assertInstanceOf(Size::class, $rules->getRule('size'));
        self::assertNull($rules->getRule('email'));
    }

    /**
     * @unreleased
     */
    public function testGettingAllRules()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules('required', 'size:5');

        self::assertCount(2, $rules->getRules());
    }

    /**
     * @unreleased
     */
    public function testForgettingARule()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules('required', 'size:5');
        $rules->removeRuleWithId('required');

        self::assertCount(1, $rules);
        self::assertFalse($rules->hasRule('required'));
    }

    /**
     * @unreleased
     */
    public function testRulesCanBeSerializedToJson()
    {
        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules('required', 'size:5');

        self::assertJsonStringEqualsJsonString(
            json_encode([
                'required' => true,
                'size' => 5,
            ]),
            json_encode($rules)
        );
    }

    /**
     * @unreleased
     */
    public function testRulesAreIterable()
    {
        self::assertIsIterable(new ValidationRuleSet($this->getMockRulesRegister()));
    }

    /**
     * @unreleased
     */
    public function testClosuresMustHaveAtLeastTwoParameters()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Validation rule closure must accept between 2 and 4 parameters, 1 given.');

        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules(static function ($value) {
        });
    }

    /**
     * @unreleased
     */
    public function testClosureMustHaveAtMostFourParameters()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Validation rule closure must accept between 2 and 4 parameters, 5 given.');

        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules(static function ($value, $fail, $message, $attribute, $extra) {
        });
    }

    /**
     * @unreleased
     */
    public function testClosureSecondParameterMustBeClosure()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            'Validation rule closure must accept a Closure as the second parameter, int given.'
        );

        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules(static function ($value, int $fail) {
        });
    }

    /**
     * @unreleased
     */
    public function testClosureThirdParameterMustBeString()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Validation rule closure must accept a string as the third parameter, int given.');

        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules(static function ($value, $fail, int $message) {
        });
    }

    /**
     * @unreleased
     */
    public function testClosureFourthParameterMustBeArray()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Validation rule closure must accept a array as the fourth parameter, int given.');

        $rules = new ValidationRuleSet($this->getMockRulesRegister());
        $rules->rules(static function ($value, $fail, $message, int $attribute) {
        });
    }

    /**
     * @unreleased
     */
    private function getMockRulesRegister(): ValidationRulesRegistrar
    {
        $register = new ValidationRulesRegistrar();
        $register->register(Required::class);
        $register->register(Size::class);

        return $register;
    }
}

