<?php

namespace Give\Donations\Properties;

use Give\Framework\Support\Contracts\Arrayable;
use JsonSerializable;

/**
 * @since 3.20.0 added JsonSerializable an Arrayable
 * @since 2.19.6
 */
final class BillingAddress implements JsonSerializable, Arrayable
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

    /**
     * @since 3.20.0
     */
    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
        ];
    }

    /**
     * @since 3.20.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
