<?php

namespace Give\PaymentGateways\PayPalCommerce\Models;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Helpers\ArrayDataSet;

/**
 * Class PayPalPayment
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.0
 */
class PayPalPayment
{
    /**
     * Payment Id.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $id;

    /**
     * Payment Amount.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $amount;

    /**
     * Payment status.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $status;

    /**
     * Payment creation time.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $createTime;

    /**
     * Payment update time.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $updateTime;

    /**
     * PayPal Payment action links.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $links;

    /**
     *
     */
    /**
     * Create PayPalPayment object from given array.
     *
     * @since 2.9.0
     *
     * @param $array
     *
     * @return PayPalPayment
     */
    public static function fromArray($array)
    {
        /* @var PayPalPayment $payment */
        $payment = give(__CLASS__);

        $payment->validate($array);

        $array = ArrayDataSet::camelCaseKeys($array);

        foreach ($array as $itemName => $value) {
            $payment->{$itemName} = $value;
        }

        return $payment;
    }

    /**
     * Validate order given in array format.
     *
     * @since 2.9.0
     *
     * @param array $array
     *
     * @throws InvalidArgumentException
     */
    private function validate($array)
    {
        $required = ['id', 'amount', 'status', 'create_time', 'update_time', 'links'];
        $array = array_filter($array); // Remove empty values.

        if (array_diff($required, array_keys($array))) {
            throw new InvalidArgumentException(
                __(
                    'To create a PayPalPayment object, please provide valid id, amount, status, create_time, update_time and links',
                    'give'
                )
            );
        }
    }
}
