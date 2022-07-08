<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Concerns\HasLabel;

/**
 * @unreleased
 */
class Section extends Group
{
    use HasLabel;

    /**
     * @unreleased
     */
    const TYPE = 'section';

    /**
     * @var string
     */
    protected $description;

    /**
     * @unreleased
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @unreleased
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
