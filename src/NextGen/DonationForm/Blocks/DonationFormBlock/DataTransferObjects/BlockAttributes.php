<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock\DataTransferObjects;

use Give\Framework\Support\Contracts\Arrayable;

class BlockAttributes implements Arrayable
{
    /**
     * @var int|null
     */
    public $formId;
    /**
     * @var string|null
     */
    public $formTemplateId;

    /**
     * @unreleased
     */
    public static function fromArray(array $array): BlockAttributes
    {
        $self = new self();

        $self->formId = !empty($array['formId']) ? (int)$array['formId'] : null;
        $self->formTemplateId = !empty($array['formTemplateId']) ? $array['formTemplateId'] : null;

        return $self;
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
