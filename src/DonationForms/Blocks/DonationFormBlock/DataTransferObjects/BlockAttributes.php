<?php

namespace Give\DonationForms\Blocks\DonationFormBlock\DataTransferObjects;

use Give\Framework\Support\Contracts\Arrayable;

class BlockAttributes implements Arrayable
{
    /**
     * @var int|null
     */
    public $formId;
    /**
     * @var string
     */
    public $blockId;

    /**
     * @var string
     */
    public $displayStyle;

    /**
     * @var string
     */
    public $openFormButton;

    /**
     * @since 3.0.0
     */
    public static function fromArray(array $array): BlockAttributes
    {
        $self = new self();

        $self->formId = !empty($array['formId']) ? (int)$array['formId'] : null;
        $self->blockId = !empty($array['blockId']) ? (string)$array['blockId'] : null;
        $self->displayStyle = !empty($array['displayStyle']) ? (string)$array['displayStyle'] : null;
        $self->openFormButton = !empty($array['openFormButton']) ? (string)$array['openFormButton'] : null;

        return $self;
    }

    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
