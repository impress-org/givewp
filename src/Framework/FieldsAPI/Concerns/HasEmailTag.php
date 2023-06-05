<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasEmailTag
{

    /** @var string|null */
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
     *
     * @return string|null
     */
    public function getEmailTag()
    {
        return $this->emailTag;
    }
}
