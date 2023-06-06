<?php

namespace Give\DonationForms\V2\Properties;

use Give\Framework\Support\ValueObjects\Money;

final class DonationFormLevel
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var Money
     */
    public $amount;
    /**
     * @var string
     */
    public $label;
    /**
     * @var bool
     */
    public $isDefault;

    /**
     * @since 2.24.0
     *
     * @param array $array
     *
     * @return DonationFormLevel
     */
    public static function fromArray(array $array): DonationFormLevel
    {
        $self = new static();

        $self->id = $array['_give_id']['level_id'];
        $self->amount = Money::fromDecimal($array['_give_amount'], give_get_currency());
        $self->label = $array['_give_text'] ?? '';
        $self->isDefault = isset($array['_give_default']) && $array['_give_default'] === 'default';

        return $self;
    }

    /**
     * @since 2.24.0
     *
     * @param string $price
     *
     * @return DonationFormLevel
     */
    public static function fromPrice(string $price): DonationFormLevel
    {
        $self = new static();

        $self->id = 0;
        $self->amount = Money::fromDecimal($price, give_get_currency());
        $self->label = '';
        $self->isDefault = true;

        return $self;
    }
}
