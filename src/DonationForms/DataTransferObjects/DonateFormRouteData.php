<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Exceptions\DonationFormForbidden;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\FieldsAPI\Actions\CreateValidatorFromForm;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Framework\Support\Contracts\Arrayable;
use WP_Error;

/**
 * @since 3.0.0
 */
class DonateFormRouteData implements Arrayable
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
     * @since TBD Validate the client-provided origin URL before it is used in redirects.
     * @since 3.0.0
     */
    public static function fromRequest(array $requestData): self
    {
        $self = new self();
        $self->formId = (int)$requestData['formId'];
        $self->gatewayId = $requestData['gatewayId'];
        $self->originUrl = self::validateOriginUrl($requestData['originUrl'] ?? '');
        $self->isEmbed = filter_var($requestData['isEmbed'], FILTER_VALIDATE_BOOLEAN);
        $self->embedId = $self->isEmbed ? $requestData['embedId'] : null;
        $self->requestData = $requestData;

        return $self;
    }

    /**
     * This method loops over the form schema to
     * compares the request against the individual fields,
     * their types and validation rules.
     *
     * @since 3.22.0 added givewp_donation_form_fields_validated action
     * @since 3.14.0 Added form status validation
     * @since 3.0.0
     *
     * @throws DonationFormFieldErrorsException|NameCollisionException|DonationFormForbidden
     */
    public function validated(): DonateControllerData
    {
        $request = $this->getRequestData();
        $validData = new DonateControllerData();

        /** @var DonationForm $form */
        $form = DonationForm::find($this->formId);

        if (!$form || !$this->isValidForm($form)) {
            throw new DonationFormForbidden();
        }

        $validator = (new CreateValidatorFromForm())($form->schema(), $request);

        if ($validator->fails()) {
            $this->throwDonationFormFieldErrorsException($validator->errors());
        }

        $validatedValues = $validator->validated();

        /**
         * @since 4.16.0 added $isFinalSubmission (true here: the final donation submission)
         * @since 3.22.0
         *
         * @param array $data              validated values in key value pairs
         * @param bool  $isFinalSubmission whether this is the final donation submission
         */
        do_action('givewp_donation_form_fields_validated', $validatedValues, true);

        foreach ($validatedValues as $fieldId => $value) {
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
     * @since 3.0.0
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    /**
     * The origin URL is client-provided and later used as a redirect target,
     * so anything that is not a valid http(s) URL falls back to the site URL.
     * Intentionally not wp_http_validate_url(), which rejects localhost hosts
     * that are valid embed origins during development.
     *
     * @since TBD
     */
    private static function validateOriginUrl(string $originUrl): string
    {
        if (!$originUrl) {
            return '';
        }

        $scheme = wp_parse_url($originUrl, PHP_URL_SCHEME);

        if (in_array($scheme, ['http', 'https'], true) && filter_var($originUrl, FILTER_VALIDATE_URL)) {
            return $originUrl;
        }

        return home_url();
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
    private function throwDonationFormFieldErrorsException(array $errors)
    {
        $wpError = new WP_Error();

        foreach ($errors as $id => $error) {
            $wpError->add($id, $error);
        }

        throw new DonationFormFieldErrorsException($wpError);
    }

    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @since 4.14.0.0 update permission capability to use facade
     * @since 3.14.0
     */
    private function isValidForm(DonationForm $form): bool
    {
        if ($form->status->isTrash()) {
            return false;
        }

        if (!$form->status->isPublished() && !UserPermissions::donationForms()->canEdit()) {
            return false;
        }

        return true;
    }
}
