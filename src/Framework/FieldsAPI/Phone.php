<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unlreased add phone format attribute
 * @since 2.32.0 added description
 * @since 2.12.0
 * @since 2.14.0 add min/max length validation
 */
class Phone extends Field
{
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasMaxLength;
    use Concerns\HasMinLength;
    use Concerns\HasPlaceholder;

    const TYPE = 'phone';

    /** @var string */
    protected $phoneFormat = '';

    /**
     * Set the phone format for the element.
     *
     * @unreleased
     */
    public function phoneFormat(string $phoneFormat): self
    {
        $this->phoneFormat = $phoneFormat;

        return $this;
    }

    /**
     * Get the phone format for the element.
     *
     * @unreleased
     */
    public function getPhoneFormat(): string
    {
        return $this->phoneFormat;
    }
}
