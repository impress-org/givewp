<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.32.0
 */
trait HasDescription
{

    /** @var string|null */
    protected $description;

    /**
     * @since 2.32.0
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @since 2.32.0
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
