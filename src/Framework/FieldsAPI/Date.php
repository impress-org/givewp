<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 */
class Date extends Field
{
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasPlaceholder;
    use Concerns\HasDescription;

    const TYPE = 'date';

    /** @var string */
    protected $dateFormat = '';

    /**
     * Set the date format for the element.
     *
     * @unreleased
     */
    public function dateFormat(string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * Get the date format for the element.
     *
     * @unreleased
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }
}
