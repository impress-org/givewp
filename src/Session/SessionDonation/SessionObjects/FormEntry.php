<?php

namespace Give\Session\SessionDonation\SessionObjects;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Helpers\ArrayDataSet;
use Give\Session\Objects;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;

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
     * Donation-related session objects.
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
     * Honeypot value to detect spam submissions.
     *
     * @var string|null
     */
    public $honeypot;

    /**
     * Form ID prefix.
     *
     * @var string|null
     */
    public $formIdPrefix;

    /**
     * Form URL.
     *
     * @var string|null
     */
    public $formUrl;

    /**
     * Minimum donation amount.
     *
     * @var float|null
     */
    public $formMinimum;

    /**
     * Maximum donation amount.
     *
     * @var float|null
     */
    public $formMaximum;

    /**
     * Form hash.
     *
     * @var string|null
     */
    public $formHash;

    /**
     * Payment mode.
     *
     * @var string|null
     */
    public $paymentMode;

    /**
     * Stripe Payment Method.
     *
     * @var string|null
     */
    public $stripePaymentMethod;
    /*

    /**
     * Constant Contact signup status.
     *
     * @var bool|null
     */
    public $constantContactSignup;

    /**
     * Action property.
     *
     * @var string|null
     */
    public $action;

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
