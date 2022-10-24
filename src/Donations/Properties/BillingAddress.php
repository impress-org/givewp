<?php

namespace Give\Donations\Properties;

final class BillingAddress
{
    /**
     * @var string
     */
    public $country;
    /**
     * @var string
     */
    public $address1;
    /**
     * @var string
     */
    public $address2;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string
     */
    public $state;
    /**
     * @var string
     */
    public $zip;

    /**
     * @since 2.19.6
     *
     * @param array $array
     *
     * @return BillingAddress
     */
    public static function fromArray($array)
    {
        $self = new static();

        $self->country = $array['country'];
        $self->address1 = $array['address1'];
        $self->address2 = $array['address2'];
        $self->city = $array['city'];
        $self->state = $array['state'];
        $self->zip = $array['zip'];

        return $self;
    }
}
