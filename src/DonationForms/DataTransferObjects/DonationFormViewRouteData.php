<?php

namespace Give\DonationForms\DataTransferObjects;

/**
 * @since 3.0.0
 */
class DonationFormViewRouteData
{
    /**
     * @var int
     */
    public $formId;

    /**
     *
     * @since 3.0.0
     */
    public static function fromRequest(array $request): self
    {
        $self = new self();

        $self->formId = (int)$request['form-id'];

        return $self;
    }
}
