<?php

declare(strict_types=1);

namespace Give\Framework\Validation;

use ArrayIterator;
use Closure;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Framework\Validation\Contracts\ValidationRule;
use IteratorAggregate;
use JsonSerializable;
use ReflectionException;
use ReflectionFunction;
use Traversable;

class ValidationRulesArray implements IteratorAggregate, JsonSerializable
{
    /**
     * @var ValidationRulesRegister
     */
    private $register;

    /**
     * @var array<int, ValidationRule|Closure>
     */
    private $rules = [];

    /**
     * @unreleased
     */
    public function __construct(ValidationRulesRegister $register)
    {
        $this->register = $register;
    }

    /**
     * Pass a set of validation rules in the form of the rule id, a rule instance, or a closure.
     *
     * @unreleased
     *
     * @param string|ValidationRule|Closure ...$rules
     */
    public function rules(...$rules): self
    {
        foreach ($rules as $rule) {
            if ($rule instanceof Closure) {
                $this->validateClosureRule($rule);
                $this->rules[] = $rule;
            } elseif ($rule instanceof ValidationRule) {
                $this->rules[] = $rule;
            } elseif (is_string($rule)) {
                $this->rules[] = $this->getRuleFromString($rule);
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'Validation rule must be a string, instance of %s, or a closure',
                        ValidationRule::class
                    )
                );
            }
        }

        return $this;
    }

    /**
     * Removes the rules with the given id.
     *
     * @unreleased
     *
     * @return void
     */
    public function removeRuleWithId(string $id): self
    {
        $this->rules = array_filter($this->rules, static function ($rule) use ($id) {
            return $rule instanceof ValidationRule && $rule::id() !== $id;
        });

        return $this;
    }

    /**
     * Returns the validation rules.
     *
     * @unreleased
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Finds and returns the validation rule by id. Does not work for Closure rules.
     *
     * @return ValidationRule|null
     */
    public function getRule(string $rule)
    {
        foreach ($this->rules as $validationRule) {
            if ($validationRule instanceof ValidationRule && $validationRule::id() === $rule) {
                return $validationRule;
            }
        }

        return null;
    }

    /**
     * Returns whether the given rule is present in the validation rules. Does not work with Closure Rules.
     *
     * @unreleased
     */
    public function hasRule(string $rule): bool
    {
        foreach ($this->rules as $validationRule) {
            if ($validationRule instanceof ValidationRule && $validationRule::id() === $rule) {
                return true;
            }
        }

        return false;
    }

    /**
     * Along with the IteratorAggregate interface, we can iterate over the validation rules.
     *
     * @unreleased
     *
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rules);
    }

    /**
     * Runs through the validation rules and compiles a list of rules that can be used by the front end.
     *
     * Resulting data:
     * [
     *   ruleId => ruleOption,
     *   ...
     * ]
     *
     * @inheritDoc
     *
     * @unreleased
     */
    public function jsonSerialize()
    {
        $rules = [];

        foreach ($this->rules as $rule) {
            if ($rule instanceof ValidatesOnFrontEnd) {
                $rules[$rule::id()] = $rule->serializeOption();
            }
        }

        return $rules;
    }

    /**
     * Takes a validation rule string and returns the corresponding rule instance.
     *
     * @unreleased
     */
    private function getRuleFromString(string $rule): ValidationRule
    {
        list($ruleId, $ruleOptions) = array_pad(explode(':', $rule, 2), 2, null);

        /**
         * @var ValidationRule $ruleClass
         */
        $ruleClass = $this->register->getRule($ruleId);

        if (!$ruleClass) {
            throw new InvalidArgumentException(
                sprintf(
                    'Validation rule with id %s has not been registered.',
                    $ruleId
                )
            );
        }

        return $ruleClass::fromString($ruleOptions);
    }

    /**
     * Validates that a closure rule has the proper parameters to be used as a validation rule.
     *
     * @unreleased
     *
     * @return void
     */
    private function validateClosureRule(Closure $closure)
    {
        try {
            $reflection = new ReflectionFunction($closure);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(
                'Unable to validate closure parameters. Please ensure that the closure is valid.'
            );
        }

        $parameters = $reflection->getParameters();
        $parameterCount = count($parameters);

        if ($parameterCount < 2 || $parameterCount > 4) {
            throw new InvalidArgumentException(
                "Validation rule closure must accept between 2 and 4 parameters, $parameterCount given."
            );
        }

        if (!in_array($parameters[1]->getType(), [null, 'Closure'], true)) {
            throw new InvalidArgumentException(
                "Validation rule closure must accept a Closure as the second parameter, {$parameters[1]->getType()} given."
            );
        }

        if ($parameterCount > 2 && !in_array($parameters[2]->getType(), [null, 'string'], true)) {
            throw new InvalidArgumentException(
                "Validation rule closure must accept a string as the third parameter, {$parameters[2]->getType()} given."
            );
        }

        if ($parameterCount > 3 && !in_array($parameters[3]->getType(), [null, 'array'], true)) {
            throw new InvalidArgumentException(
                "Validation rule closure must accept a array as the fourth parameter, {$parameters[3]->getType()} given."
            );
        }
    }
}
