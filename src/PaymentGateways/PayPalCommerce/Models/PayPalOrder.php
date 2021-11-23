<?php

namespace Give\PaymentGateways\PayPalCommerce\Models;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Helpers\ArrayDataSet;
use stdClass;

/**
 * Class PayPalOrder
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.0
 */
class PayPalOrder
{
    /**
     * Order Id.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $id;

    /**
     * Order intent.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $intent;

    /**
     * Order status.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $status;

    /**
     * Order creation time.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $createTime;

    /**
     * Order update time.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $updateTime;

    /**
     * PayPal Order action links.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $links;

    /**
     * Payer information.
     *
     * @since 2.9.0
     *
     * @var stdClass
     */
    public $payer;

    /**
     * Order purchase unit details.
     *
     * @since 2.9.0
     *
     * @var stdClass
     */
    private $purchaseUnit;

    /**
     * Payment details for order.
     *
     * @since 2.9.0
     *
     * @var PayPalPayment
     */
    public $payment;

    /**
     * Create PayPalOrder object from given array.
     *
     * @since 2.9.0
     *
     * @param $array
     *
     * @return PayPalOrder
     */
    public static function fromArray($array)
    {
        /* @var PayPalOrder $order */
        $order = give(__CLASS__);

        $order->validate($array);
        $array = ArrayDataSet::camelCaseKeys($array);

        foreach ($array as $itemName => $value) {
            if ('purchaseUnits' === $itemName) {
                $value = current($value);

                $order->purchaseUnit = $value;
                $order->payment = PayPalPayment::fromArray((array)current($order->purchaseUnit->payments->captures));

                continue;
            }

            $order->{$itemName} = $value;
        }

        return $order;
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
        $required = ['id', 'intent', 'purchase_units', 'create_time', 'update_time', 'links'];

        // PayPal does not send following parameter in Order (completed with advanced card fields payment method) details.
        if ( ! isset($array['payment_source'])) {
            $required = array_merge($required, ['payer', 'status']);
        }

        $array = array_filter($array); // Remove empty values.

        if (array_diff($required, array_keys($array))) {
            throw new InvalidArgumentException(
                sprintf(
                    esc_html__('To create a PayPalOrder object, please provide valid %1$s', 'give'),
                    implode(', ', $required)
                )
            );
        }
    }
}
