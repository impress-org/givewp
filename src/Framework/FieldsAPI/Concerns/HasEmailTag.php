<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasEmailTag
{

    /** @var string|null */
    protected $emailTag;

    /**
     * @since 2.28.0 add types
     */
    public function emailTag(string $emailTag): self
    {
        $this->emailTag = $emailTag;

        return $this;
    }

    /**
     * @since 2.28.0 add types
     *
     * @return string|null
     */
    public function getEmailTag()
    {
        return $this->emailTag;
    }
}
