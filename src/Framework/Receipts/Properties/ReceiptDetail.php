<?php

namespace Give\Framework\Receipts\Properties;

use Give\Framework\Support\Contracts\Arrayable;

class ReceiptDetail implements Arrayable
{
    /**
     * @var string
     */
    public $label;
    /**
     * @var mixed
     */
    public $value;

    /**
     * @since 0.1.0
     *
     * @param  string  $label
     * @param  mixed  $value
     */
    public function __construct(string $label, $value) {
        $this->label = $label;
        $this->value = $value;
    }

    /**
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
        ];
    }
}
