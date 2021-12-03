<?php

namespace Give\Receipt;

/**
 * Class LineItem
 *
 * This class represent receipt line item as object.
 *
 * @package Give\Receipt
 * @since 2.7.0
 */
class LineItem
{
    /**
     * ID.
     *
     * @since 2.7.0
     * @var int $donationId
     */
    public $id;

    /**
     * Label.
     *
     * @since 2.7.0
     * @var int $donationId
     */
    public $label;

    /**
     * Value.
     *
     * @since 2.7.0
     * @var int $donationId
     */
    public $value;

    /**
     * Icon.
     *
     * @since 2.7.0
     * @var int $donationId
     */
    public $icon;

    /**
     * LineItem constructor.
     *
     * @param string $id
     * @param string $label
     * @param string $value
     * @param string $icon
     */
    public function __construct($id, $label, $value, $icon = '')
    {
        $this->id = $id;
        $this->label = $label;
        $this->value = $value;
        $this->icon = $icon;
    }
}
