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
     * @var string|null
     */
    public $formTemplateId;
    /**
     * @var BlockCollection|null
     */
    public $formBlocks;

    /**
     * Convert data from request into DTO
     *
     * @unreleased
     */
    public static function fromRequest(array $request): DonationFormViewRouteData
    {
        $self = new static();

        $self->formId = (int)$request['form-id'];
        $self->formTemplateId = !empty($request['form-template-id']) ? $request['form-template-id'] : '';
        $self->formBlocks = !empty($request['form-blocks']) ? BlockCollection::fromJson(
            $request['form-blocks']
        ) : null;

        return $self;
    }
}
