<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Exceptions\DonationFormForbidden;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\FieldsAPI\Actions\CreateValidatorFromFormFields;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Support\Contracts\Arrayable;
use WP_Error;

/**
 * @since 3.0.0
 */
class ValidationRouteData implements Arrayable
{
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
     * @since 3.0.0
     */
    public static function fromRequest(array $requestData): self
    {
        $self = new self();
        $self->formId = (int)$requestData['formId'];
        $self->requestData = $requestData;

        return $self;
    }

    /**
     * This method loops over the form schema to
     * compares the request against the individual fields,
     * their types and validation rules.
     *
     * @since 3.0.0
     *
     * @throws DonationFormFieldErrorsException
     * @throws NameCollisionException
     * @throws DonationFormForbidden
     */
    public function validate(): JsonResponse
    {
        $request = $this->getRequestData();

        /** @var DonationForm $form */
        $form = DonationForm::find($this->formId);

        if (!$form || !$this->isValidForm($form)) {
            throw new DonationFormForbidden();
        }

        $formFields = array_filter($form->schema()->getFields(), static function ($field) use ($request) {
            return array_key_exists($field->getName(), $request);
        });

        $validator = (new CreateValidatorFromFormFields())($formFields, $request);

        if ($validator->fails()) {
            $this->throwDonationFormFieldErrorsException($validator->errors());
        }

        $data = $validator->validated();

       /**
         * Allow for additional validation of the preflight validation form data.
         * The donation flow can be interrupted by throwing an Exception.
         *
         * @unreleased
         *
         * @param array $data Returns the validated values
         */
        do_action('givewp_donate_form_preflight_data_validated', $data);

        return new JsonResponse(['valid' => true]);
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     *
     * @param  array<string, string>  $errors
     *
     * @throws DonationFormFieldErrorsException
     */
    private function throwDonationFormFieldErrorsException(array $errors): void
    {
        $wpError = new WP_Error();

        foreach ($errors as $id => $error) {
            $wpError->add($id, $error);
        }

        throw new DonationFormFieldErrorsException($wpError);
    }

    /**
     * @unreleased
     */
    private function isValidForm(DonationForm $form): bool
    {
        if ($form->status->isTrash()) {
            return false;
        }

        if (!$form->status->isPublished() && !current_user_can('edit_give_forms')) {
            return false;
        }

        return true;
    }

    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
