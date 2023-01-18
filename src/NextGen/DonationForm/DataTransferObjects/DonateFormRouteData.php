<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\Framework\FieldsAPI\Actions\CreateValidatorFromForm;
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
     * @var string
     */
    public $originUrl;
    /**
     * @var string|null
     */
    public $embedId;
    /**
     * @var bool
     */
    public $isEmbed;

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
        $self->originUrl = $requestData['originUrl'];
        $self->isEmbed = $requestData['isEmbed'];
        $self->embedId = $self->isEmbed ? $requestData['embedId'] : null;
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

        /** @var DonationForm $form */
        $form = DonationForm::find($this->formId);

        $validator = (new CreateValidatorFromForm())($form->schema(), $request);

        if ($validator->fails()) {
            $this->throwDonationFormFieldErrorsException($validator->errors());
        }

        foreach ($validator->validated() as $fieldId => $value) {
            $validData->{$fieldId} = $value;
        }

        $validData->formTitle = $form->title;
        $validData->wpUserId = get_current_user_id();
        $validData->originUrl = $this->originUrl;
        $validData->embedId = $this->embedId;
        $validData->isEmbed = $this->isEmbed;

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
     * @param array<string, string> $errors
     *
     * @throws DonationFormFieldErrorsException
     */
    private function throwDonationFormFieldErrorsException(array $errors)
    {
        $wpError = new WP_Error();

        foreach ($errors as $id => $error) {
            $wpError->add($id, $error);
        }

        throw new DonationFormFieldErrorsException($wpError);
    }
}
