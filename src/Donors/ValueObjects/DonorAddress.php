<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\Contracts\Arrayable;
use JsonSerializable;

/**
 * @unreleased
 */
final class DonorAddress implements JsonSerializable, Arrayable
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
     * @unreleased
     *
     * @param array $array
     *
     * @return DonorAddress
     */
    public static function fromArray($array)
    {
        $self = new static();

        $self->country = $array['country'] ?? '';
        $self->address1 = $array['address1'] ?? '';
        $self->address2 = $array['address2'] ?? '';
        $self->city = $array['city'] ?? '';
        $self->state = $array['state'] ?? '';
        $self->zip = $array['zip'] ?? '';

        return $self;
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @unreleased
     */
    public function toString(): string
    {
        $parts = array_filter([
            $this->address1,
            $this->address2,
            $this->city,
            $this->state,
            $this->zip,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * @unreleased
     */
    public function isEmpty(): bool
    {
        return empty($this->country) &&
               empty($this->address1) &&
               empty($this->address2) &&
               empty($this->city) &&
               empty($this->state) &&
               empty($this->zip);
    }
}
