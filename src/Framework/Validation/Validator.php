<?php

declare(strict_types=1);

namespace Give\Framework\Validation;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\Validation\Contracts\Sanitizer;

/**
 * A tool for taking in a set of values and corresponding validation rules, and then validating the values.
 *
 * @unreleased
 */
class Validator
{
    /**
     * @var array<string, ValidationRulesArray>
     */
    private $rules;

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
     * Takes a Form from the Field API, a corresponding set of values, and converts it to a Validator
     *
     * @unreleased
     */
    public static function fromForm(Form $form, array $values): self
    {
        $labels = [];
        $rules = [];

        foreach ($form->getFields() as $field) {
            $rules[$field->getName()] = $field->getValidationRules();

            if (method_exists($field, 'getLabel')) {
                $labels[$field->getName()] = $field->getLabel();
            }
        }

        return new self($rules, $values, $labels);
    }

    /**
     * @unreleased
     *
     * @param array<string, ValidationRulesArray|array> $rules
     * @param array<string, mixed> $values
     */
    public function __construct(array $rules, array $values, array $labels = [])
    {
        $validatedRules = [];
        foreach ($rules as $key => $rule) {
            if (is_array($rule)) {
                $validatedRules[$key] = give(ValidationRulesArray::class)->rules(...$rule);
            } elseif ($rule instanceof ValidationRulesArray) {
                $validatedRules[$key] = $rule;
            } else {
                throw new InvalidArgumentException(
                    "Validation rules must be an instance of ValidationRulesArray or a compatible array"
                );
            }
        }

        $this->rules = $validatedRules;
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

        foreach ($this->rules as $key => $fieldRule) {
            $label = $this->labels[$key] ?? $key;
            $value = $this->values[$key] ?? null;

            $fail = function (string $message) use ($label) {
                $this->errors[] = str_ireplace('{field}', $label, $message);
            };

            foreach ($fieldRule as $rule) {
                $rule($value, $fail, $key, $this->values);

                if ($rule instanceof Sanitizer) {
                    $value = $rule->sanitize($value);
                }

                $this->validatedValues[$key] = $value;
            }
        }

        $this->ranValidationRules = true;
    }
}
