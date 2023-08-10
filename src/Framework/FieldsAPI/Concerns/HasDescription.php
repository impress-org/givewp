<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 */
trait HasDescription
{

    /** @var string|null */
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
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
