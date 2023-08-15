<?php

namespace Give\DonationForms\DataTransferObjects;

/**
 * @since 0.1.0
 */
class DonationFormViewRouteData
{
    /**
     * @var int
     */
    public $formId;

    /**
     *
     * @since 0.1.0
     */
    public static function fromRequest(array $request): DonationFormViewRouteData
    {
        $self = new static();

        $self->formId = (int)$request['form-id'];

        return $self;
    }
}
