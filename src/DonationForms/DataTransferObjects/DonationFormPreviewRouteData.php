<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\DonationForms\Properties\FormSettings;
use Give\Framework\Blocks\BlockCollection;

/**
 * @since 3.0.0
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
     * @var string
     */
    public $locale;

    /**
     * Convert data from request into DTO
     *
     * @unreleased Add locale support
     * @since 3.0.0
     *
     * @param array{form-id: string, form-settings: string, form-blocks: string} $request
     */
    public static function fromRequest(array $request): self
    {
        $self = new self();

        $self->formId = (int)$request['form-id'];
        $self->formSettings = !empty($request['form-settings']) ? FormSettings::fromJson(
            $request['form-settings']
        ) : null;
        $self->formBlocks = !empty($request['form-blocks']) ? BlockCollection::fromJson(
            $request['form-blocks']
        ) : null;

        $self->locale = $request['locale'] ?? '';

        return $self;
    }
}
