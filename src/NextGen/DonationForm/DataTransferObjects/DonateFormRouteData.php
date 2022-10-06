<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\Framework\FieldsAPI\Field;
use Give\NextGen\DonationForm\Exceptions\DonationFormFieldErrorsException;
use Give\NextGen\DonationForm\Models\DonationForm;
use WP_Error;

/**
 * @unreleased
 */
class DonateFormRouteData
{
    /**
     * @var string
     */
    public $gatewayId;
    /**
     * @var array
     */
    private $requestData;
    /**
     * @var int
     */
    public $formId;

    /**
     * Convert data from request into DTO
     *
     * @unreleased
     */
    public static function fromRequest(array $requestData): DonateFormRouteData
    {
        $self = new static();
        $self->formId = (int)$requestData['formId'];
        $self->gatewayId = $requestData['gatewayId'];
        $self->requestData = $requestData;

        return $self;
    }

    /**
     * This method loops over the form schema to
     * compares the request against the individual fields,
     * their types and validation rules.
     *
     * @unreleased
     *
     * @throws DonationFormFieldErrorsException
     */
    public function validated(): DonateControllerData
    {
        $request = $this->getRequestData();
        $validData = new DonateControllerData();

        $errors = [];

        /** @var DonationForm $form */
        $form = DonationForm::find($this->formId);

        $form->schema()->walkFields(function (Field $field) use (
            $request,
            $validData,
            &$errors
        ) {
            $fieldValue = $request[$field->getName()];

            // validate required fields
            if (empty($fieldValue) && $field->isRequired()) {
                $errors[] = $request[$field->getName()] = $field->getRequiredError();
            }

            if (empty($errors[$field->getName()])) {
                $validData->{$field->getName()} = $this->castFieldValue($field, $fieldValue ?? '');
            }
        });

        if ($errors) {
            $this->throwDonationFormFieldErrorsException($errors);
        }

        $validData->wpUserId = get_current_user_id();

        return $validData;
    }

    /**
     * @unreleased
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    /**
     * This loops over an array of errors in the specific FieldAPI format,
     * and converts them into a WP_Error object that is attached to the
     * exception and delivered back to the client via JSON.
     *
     * @unreleased
     *
     * @param  array{error_id: string, error_message: string}  $errors
     *
     * @throws DonationFormFieldErrorsException
     */
    private function throwDonationFormFieldErrorsException(array $errors)
    {
        $wpError = new WP_Error();

        foreach ($errors as $error) {
            $wpError->add($error['error_id'], $error['error_message']);
        }

        $exception = new DonationFormFieldErrorsException();
        $exception->setError($wpError);

        throw $exception;
    }

    /**
     * Some properties need to be cast to specific types.
     *
     * TODO: figure out a less static way of doing this
     *
     * @unreleased
     */
    private function castFieldValue(Field $field, string $fieldValue)
    {
        if ($field->getName() === 'formId') {
            return (int)$fieldValue;
        }

        return $fieldValue;
    }
}
