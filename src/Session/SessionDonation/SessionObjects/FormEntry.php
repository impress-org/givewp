<?php

namespace Give\Session\SessionDonation\SessionObjects;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Helpers\ArrayDataSet;
use Give\Session\Objects;

/**
 * Class FormEntry
 *
 * This class use to represent donation form entries as object.
 *
 * @package Give\Session\SessionDonation\SessionObjects
 */
class FormEntry implements Objects
{
    /**
     * Form Id.
     *
     * @var string
     */
    public $formId;

    /**
     * Form Title.
     *
     * @var string
     */
    public $formTitle;

    /**
     * Page url on which donation page exist.
     *
     * @var string
     */
    public $currentUrl;

    /**
     * Donation level id.
     *
     * @var string
     */
    public $priceId;

    /**
     * Donation amount
     *
     * @var string
     */
    public $totalAmount;

    /**
     * First name
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
     * Company name.
     *
     * @var string
     */
    public $companyName;

    /**
     * Donor email
     *
     * @var string
     */
    public $donorEmail;

    /**
     * WP user id.
     *
     * @var string
     */
    public $wpUserId;

    /**
     * Payment gateway.
     *
     * @var string
     */
    public $paymentGateway;

    /**
     * Take array and return object.
     *
     * @param $array
     *
     * @return FormEntry
     */
    public static function fromArray($array)
    {
        $renameTo = [
            'amount' => 'totalAmount',
            'first' => 'firstName',
            'last' => 'lastName',
            'email' => 'donorEmail',
            'userId' => 'wpUserId',
            'gateway' => 'paymentGateway',
        ];

        $array = ArrayDataSet::renameKeys($array, $renameTo);
        $expectedKeys = ['formId', 'totalAmount', 'firstName', 'email', 'gateway'];

        if ( ! ArrayDataSet::hasRequiredKeys($array, $expectedKeys)) {
            throw new InvalidArgumentException(
                'Invalid FormEntries object, must have the exact following keys: ' . implode(', ', $expectedKeys)
            );
        }

        $formEntries = new self();

        foreach ($array as $key => $value) {
            $formEntries->{$key} = $value;
        }

        return $formEntries;
    }
}
