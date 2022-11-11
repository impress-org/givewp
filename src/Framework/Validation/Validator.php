<?php

declare(strict_types=1);

namespace Give\Framework\Validation;

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
        $rules = [];

        foreach ($form->getFields() as $field) {
            $rules[$field->getName()] = $field->getValidationRules();
        }

        return new self($rules, $values);
    }

    /**
     * @unreleased
     *
     * @param array<string, ValidationRulesArray> $rules
     * @param array<string, mixed> $values
     */
    public function __construct(array $rules, array $values)
    {
        $this->rules = $rules;
        $this->values = $values;
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

        foreach($this->rules as $key => $fieldRule) {
            $value = $this->values[$key] ?? null;

            $fail = function (string $message) use ($key) {
                $this->errors[] = str_replace('{field}', $key, $message);
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
