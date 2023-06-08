<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.2
 */
class Html extends Element
{
    const TYPE = 'html';

    /** @var string */
    protected $html = '';

    /**
     * Set the HTML for the element.
     *
     * @unreleased added types
     * @since 2.12.2
     */
    public function html(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get the HTML for the element.
     *
     * @unreleased added types
     * @since 2.12.2
     */
    public function getHtml(): string
    {
        return $this->html;
    }
}
