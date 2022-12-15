<?php

declare(strict_types=1);

namespace Give\Framework\Validation;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Validation\Contracts\Sanitizer;

/**
 * A tool for taking in a set of values and corresponding validation rules, and then validating the values.
 *
 * @unreleased
 */
class Validator
{
    /**
     * @var array<string, ValidationRuleSet>
     */
    private $ruleSets;

    /**
     * @var array<string, mixed>
     */
    private $values;

    /**
     * @var array<string, string>
     */
    private $labels;

    /**
     * @var array<string, string>
     */
    private $errors = [];

    /**
     * @var array<string, mixed>
     */
    private $validatedValues = [];

    /**
     * @var bool
     */
    private $ranValidationRules = false;

    /**
     * @unreleased
     *
     * @param array<string, ValidationRuleSet|array> $ruleSets
     * @param array<string, mixed> $values
     */
    public function __construct(array $ruleSets, array $values, array $labels = [])
    {
        $this->validateRulesAndValues($ruleSets, $values);

        $validatedRules = [];
        foreach ($ruleSets as $key => $rule) {
            if (is_array($rule)) {
                $validatedRules[$key] = give(ValidationRuleSet::class)->rules(...$rule);
            } elseif ($rule instanceof ValidationRuleSet) {
                $validatedRules[$key] = $rule;
            } else {
                throw new InvalidArgumentException(
                    'Validation rules must be an instance of ValidationRuleSet or a compatible array'
                );
            }
        }

        $this->ruleSets = $validatedRules;
        $this->values = $values;
        $this->labels = $labels;
    }

    /**
     * Returns whether the values passed validation or not.
     *
     * @unreleased
     */
    public function passes(): bool
    {
        $this->runValidationRules();

        return empty($this->errors);
    }

    /**
     * Returns whether the values failed validation or not.
     *
     * @unreleased
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Returns the errors that were found during validation.
     *
     * @unreleased
     *
     * @return array<string, string>
     */
    public function errors(): array
    {
        $this->runValidationRules();

        return $this->errors;
    }

    /**
     * Returns the validated values, with any sanitization rules applied.
     *
     * @unreleased
     */
    public function validated(): array
    {
        $this->runValidationRules();

        return $this->validatedValues;
    }

    /**
     * Runs the validation rules on the values, and stores any resulting errors.
     * Will run only once, and then store the results for subsequent calls.
     *
     * @unreleased
     *
     * @return void
     */
    private function runValidationRules()
    {
        if ($this->ranValidationRules) {
            return;
        }

        foreach ($this->ruleSets as $key => $ruleSet) {
            $label = $this->labels[$key] ?? $key;
            $value = $this->values[$key] ?? null;

            $fail = function (string $message) use ($key, $label) {
                $this->errors[$key] = str_ireplace('{field}', $label, $message);
            };

            foreach ($ruleSet as $rule) {
                $rule($value, $fail, $key, $this->values);

                if ($rule instanceof Sanitizer) {
                    $value = $rule->sanitize($value);
                }
            }

            $this->validatedValues[$key] = $value;
        }

        $this->ranValidationRules = true;
    }

    /**
     * Validates that all rules have a corresponding value with the same key.
     *
     * @unreleased
     *
     * @return void
     */
    private function validateRulesAndValues(array $rules, array $values)
    {
        $missingKeys = array_diff_key($rules, $values);

        if (!empty($missingKeys)) {
            throw new InvalidArgumentException(
                "Missing values for rules: " . implode(', ', array_keys($missingKeys))
            );
        }
    }
}
