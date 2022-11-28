<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\NextGen\DonationForm\Properties\FormSettings;
use Give\NextGen\Framework\Blocks\BlockCollection;

/**
 * @unreleased
 */
class DonationFormPreviewRouteData
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
     * @var FormSettings|null
     */
    public $formSettings;

    /**
     * Convert data from request into DTO
     *
     * @param  array{form-id: string, form-settings: string, form-blocks: string}  $request
     *
     * @unreleased
     */
    public static function fromRequest(array $request): self
    {
        $self = new static();

        $self->formId = (int)$request['form-id'];
        $self->formSettings = !empty($request['form-settings']) ? FormSettings::fromJson(
            $request['form-settings']
        ) : null;
        $self->formBlocks = !empty($request['form-blocks']) ? BlockCollection::fromJson(
            $request['form-blocks']
        ) : null;

        return $self;
    }
}
