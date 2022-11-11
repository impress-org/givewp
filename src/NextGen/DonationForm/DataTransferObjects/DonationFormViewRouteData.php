<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\NextGen\Framework\Blocks\BlockCollection;

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
     * @var BlockCollection|null
     */
    public $formBlocks;

    /**
     * @var array
     */
    public $formSettings;

    /**
     * Convert data from request into DTO
     *
     * @unreleased
     */
    public static function fromRequest(array $request): DonationFormViewRouteData
    {
        $self = new static();

        $self->formId = (int)$request['form-id'];
        $self->formSettings = !empty($request['form-settings']) ? $request['form-settings'] : [];
        $self->formBlocks = !empty($request['form-blocks']) ? BlockCollection::fromJson(
            $request['form-blocks']
        ) : null;

        return $self;
    }
}
