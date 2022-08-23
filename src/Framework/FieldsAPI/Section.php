<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Concerns\HasLabel;

/**
 * @since 2.22.0
 */
class Section extends Group
{
    use HasLabel;

    /**
     * @since 2.22.0
     */
    const TYPE = 'section';

    /**
     * @var string
     */
    protected $description;

    /**
     * @since 2.22.0
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @since 2.22.0
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
