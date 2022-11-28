<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

/**
 * @unreleased
 */
class DonationFormViewRouteData
{
    /**
     * @var int
     */
    public $formId;

    /**
     *
     * @unreleased
     */
    public static function fromRequest(array $request): DonationFormViewRouteData
    {
        $self = new static();

        $self->formId = (int)$request['form-id'];

        return $self;
    }
}
