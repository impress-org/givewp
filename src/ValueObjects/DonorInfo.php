<?php

namespace Give\ValueObjects;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

class DonorInfo implements ValueObjects
{
    /**
     * WP user id.
     * Donor can be connect to WP user. if connect and donating after login as WP user then it set to logged in user id.
     *
     * @var string
     */
    public $wpUserId;

    /**
     * Primary email.
     *
     * @var string
     */
    public $email;

    /**
     * Donor address.
     *
     * @var Address
     */
    public $address;

    /**
     * First name.
     *
     * @var string
     */
    public $firstName;

    /**
     * Last name.
     *
     * @var string
     */
    public $lastName;

    /**
     * Donor honorific.
     *
     * @var string
     */
    public $honorific;

    /**
     * Take array and return object.
     *
     * @since 2.19.6 - fix update key validation with new variable to prevent removing not required keys like lastName
     *
     * @param $array
     *
     * @return DonorInfo
     */
    public static function fromArray($array)
    {
        $expectedKeys = ['wpUserId', 'firstName', 'email', 'honorific', 'address'];

        $requiredKeys = array_intersect_key($array, array_flip($expectedKeys));

        if (empty($requiredKeys)) {
            throw new InvalidArgumentException(
                'Invalid DonorInfo object, must have the exact following keys: ' . implode(', ', $expectedKeys)
            );
        }

        $donorInfo = new self();
        foreach ($array as $key => $value) {
            $donorInfo->{$key} = $value;
        }

        // Cast address to Give\ValueObjects\Address object.
        if ( ! empty($array['address'])) {
            $donorInfo->address = Address::fromArray($array['address']);
        }

        return $donorInfo;
    }
}
