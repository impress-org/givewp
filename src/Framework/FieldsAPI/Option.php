<?php

namespace Give\Framework\FieldsAPI;

use JsonSerializable;

/**
 * Class Option
 *
 * @since 2.12.0
 */
class Option implements JsonSerializable
{

    use Concerns\HasLabel;

    /** @var string */
    protected $value;

    /**
     * @since 2.23.1 Make constructor final to avoid unsafe usage of `new static()`.
     *
     * @param string  $value
     * @param ?string $label
     */
    final public function __construct($value, $label = null)
    {
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * Create a new option.
     *
     * @since 2.12.0
     *
     * @return static
     */
    public static function make(...$args)
    {
        return new static(...$args);
    }

    /**
     * Access the value
     *
     * @since 2.12.0
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'value' => $this->getValue(),
            'label' => $this->getLabel(),
        ];
    }
}
