<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @unreleased
 */
trait Label
{
    /**
     * @var string
     */
    private $text;

    /**
     * Set column text
     *
     * @unreleased
     */
    public function text(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @unreleased
     */
    public function getText(): string
    {
        return $this->text;
    }
}
