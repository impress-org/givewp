<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasDescription
{

    /** @var string|null */
    protected $description;

    /**
     * @param string $description
     *
     * @return $this
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
