<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

/**
 * @unreleased
 */
class DonationFormViewRouteData
{
    /**
     * @var string
     */
    public $formId;
    /**
     * @var string|null
     */
    public $formTemplateId;

    /**
     * Convert data from request into DTO
     *
     * @unreleased
     */
    public static function fromRequest(array $request): DonationFormViewRouteData
    {
        $self = new static();

        $self->formId = $request['form-id'];
        $self->formTemplateId = !empty($request['form-template-id']) ? $request['form-template-id'] : null;

        return $self;
    }
}
