<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.32.0 added description
 * @since 2.12.0
 * @since 2.14.0 Add support for min/max length
 */
class Textarea extends Field
{
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasPlaceholder;
    use Concerns\HasMaxLength;
    use Concerns\HasMinLength;
    use Concerns\HasDescription;

    const TYPE = 'textarea';

    /** @var int */
    protected $rows = 5;

    /**
     * Set the number of rows for the element.
     *
     * @since 3.0.0
     */
    public function rows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get the number of rows for the element.
     *
     * @since 3.0.0
     */
    public function getRows(): string
    {
        return $this->rows;
    }
}
