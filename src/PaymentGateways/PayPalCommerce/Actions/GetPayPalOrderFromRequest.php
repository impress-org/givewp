<?php

namespace Give\PaymentGateways\PayPalCommerce\Actions;

use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\PaymentGateways\PayPalCommerce\Exceptions\PayPalOrderIdException;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use PayPalHttp\HttpException;
use PayPalHttp\IOException;

/**
 * This class returns paypal order for paypal order id given in request.
 *
 * @since 2.19.0
 */
class GetPayPalOrderFromRequest
{
    /**
     * @return \Give\PaymentGateways\PayPalCommerce\Models\PayPalOrder
     * @throws HttpException|IOException|PayPalOrderIdException
     */
    public function __invoke()
    {
        $paypalOrderId = give_clean($_POST['payPalOrderId']);

        if (!$paypalOrderId) {
            throw new PayPalOrderIdException(
                esc_html__('PayPal order id is missing.', 'give')
            );
        }

        return give(PayPalOrder::class)->getOrder($paypalOrderId);
    }
}
