<?php

namespace Give\Session\SessionDonation\SessionObjects;

use DateTime;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Helpers\ArrayDataSet;
use Give\Session\Objects;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;
use Give\ValueObjects\ValueObjects;

/**
 * Class Donation
 *
 * This class is use to represent donation session data as object.
 * You can add custom data but that data only store momentarily because of donation session time limit.
 * This does not represent actual Donation model instead of that it has few donation related information which required for donation processing.
 *
 * @package Give\Session\SessionDonation\SessionObjects
 *
 * @property CardInfo $cardInfo
 * @property FormEntry $formEntry
 * @property DonorInfo $donorInfo
 */
class Donation implements Objects
{
    /**
     * Donation id.
     *
     * @since 2.7.0
     * @var array
     */
    public $id;

    /**
     * Sanitized donation total amount.
     *
     * @since 2.7.0
     * @var string
     */
    public $totalAmount;

    /**
     * Donation purchase key.
     *
     * @since 2.7.0
     * @var string
     */
    public $purchaseKey;

    /**
     * Donor email.
     *
     * @since 2.7.0
     * @var string
     */
    public $donorEmail;

    /**
     * Donor email.
     *
     * @since 2.7.0
     * @var DateTime
     */
    public $createdAt;

    /**
     * Payment gateway id.
     *
     * @since 2.7.0
     * @var array
     */
    public $paymentGateway;

    /**
     * Donation-related objects.
     *
     * @since 3.18.0
     * @var FormEntry
     */
    public $formEntry;

    /**
     * Donor information.
     *
     * @since 3.18.0
     * @var DonorInfo
     */
    public $donorInfo;

    /**
     * Card information.
     *
     * @since 3.18.0
     * @var CardInfo
     */
    public $cardInfo;

    /**
     * Array of properties and their cast type.
     *
     * @var ValueObjects[]
     */
    private $caseTo = [
        'formEntry' => FormEntry::class,
        'donorInfo' => DonorInfo::class,
        'cardInfo' => CardInfo::class,
    ];

    /**
     * Take array and return object.
     *
     * @param $array
     *
     * @return Donation
     */
    public static function fromArray($array)
    {
        $expectedKeys = [
            'id',
            'totalAmount',
            'purchaseKey',
            'donorEmail',
            'createdAt',
            'paymentGateway',
            'formEntry',
            'cardInfo',
            'donorInfo',
        ];

        if ( ! ArrayDataSet::hasRequiredKeys($array, $expectedKeys)) {
            throw new InvalidArgumentException(
                'Invalid Donation object, must have the exact following keys: ' . implode(', ', $expectedKeys)
            );
        }

        $donation = new self();

        $array['donorInfo'] = $donation->renameKeyInDonorInfo($array['donorInfo']);
        $array['cardInfo'] = $donation->filterCardInfoKeys($array['cardInfo']);

        foreach ($array as $key => $value) {
            if (array_key_exists($key, $donation->caseTo)) {
                $class = $donation->caseTo[$key];
                $donation->{$key} = $class::fromArray($value);
                continue;
            }

            $donation->{$key} = is_array($value) ?
                json_decode(json_encode($value)) // Convert unlisted array type session data to stdClass object
                : $value;
        }

        return $donation;
    }

    /**
     * Rename array key in donor info
     *
     * @since 2.7.0
     *
     * @param array $array
     *
     * @return array
     */
    private function renameKeyInDonorInfo($array)
    {
        return ArrayDataSet::renameKeys(
            $array,
            [
                'id' => 'wpUserId',
                'title' => 'honorific',
            ]
        );
    }

    /**
     * Filter array keys in card info
     *
     * @since 2.7.0
     *
     * @param $array
     *
     * @return array
     */
    private function filterCardInfoKeys($array)
    {
        $array = $this->removePrefixFromArrayKeys($array, ['card']);
        $array = ArrayDataSet::renameKeys(
            $array,
            [
                'address' => 'line1',
                'address2' => 'line2',
            ]
        );
        $array = ArrayDataSet::moveArrayItemsUnderArrayKey(
            $array,
            ['line1', 'line2', 'city', 'state', 'country', 'zip'],
            'address'
        );

        // Rename zip to postal code.
        $array['address']['postalCode'] = $array['address']['zip'];
        unset($array['address']['zip']);

        return $array;
    }

    /**
     * Remove prefix from array key.
     *
     * @param array $array
     * @param array $prefixes
     *
     * @return array
     */
    private function removePrefixFromArrayKeys($array, $prefixes)
    {
        foreach ($array as $key => $value) {
            $newKey = lcfirst(str_replace((array)$prefixes, '', $key));

            if ($key !== $newKey) {
                unset($array[$key]);
            }

            if (is_array($value)) {
                $array[$newKey] = $this->removePrefixFromArrayKeys($value, $prefixes);
                continue;
            }

            $array[$newKey] = $value;
        }

        return $array;
    }
}
