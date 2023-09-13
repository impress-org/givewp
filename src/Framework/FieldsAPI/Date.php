<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unlreased add date format attribute
 * @since 2.32.0 added description
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
    protected $dateFormat = 'yyyy/mm/dd';

    /**
     * Set the date format for the element.
     *
     * @since 3.0.0
     */
    public function dateFormat(string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * Get the date format for the element.
     *
     * @since 3.0.0
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }
}
