<?php

namespace Give\PaymentGateways\PayPalCommerce\Actions;

use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use PayPalHttp\HttpException;
use PayPalHttp\IOException;

/**
 * This class returns paypal order for paypal order id given in request.
 *
 * @unreleased
 */
class GetPayPalOrderFromRequest
{
    /**
     * @return \Give\PaymentGateways\PayPalCommerce\Models\PayPalOrder
     * @throws HttpException | PaymentGatewayException | IOException
     */
    public function __invoke()
    {
        $paypalOrderId = give_clean($_POST['payPalOrderId']);

        if ( ! $paypalOrderId) {
            throw new PaymentGatewayException(
                esc_html__('PayPal order id is missing.', 'give')
            );
        }

        return give(PayPalOrder::class)->getOrder($paypalOrderId);
    }
}
