<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasEmailTag
{

    /** @var string */
    protected $emailTag;

    /**
     * @unreleased add types
     */
    public function emailTag(string $emailTag): self
    {
        $this->emailTag = $emailTag;

        return $this;
    }

    /**
     * @unreleased add types
     */
    public function getEmailTag(): string
    {
        return $this->emailTag;
    }
}
