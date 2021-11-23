<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\EventListener;
use Give\Repositories\PaymentsRepository;

/**
 * Class PaymentEventListener
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @since 2.9.0
 */
abstract class PaymentEventListener implements EventListener
{
    /**
     * @since 2.9.0
     *
     * @var PaymentsRepository
     */
    protected $paymentsRepository;

    /**
     * @var MerchantDetails
     */
    protected $merchantDetails;

    /**
     * PaymentEventListener constructor.
     *
     * @since 2.9.0
     *
     * @param PaymentsRepository $paymentsRepository
     * @param MerchantDetails    $merchantDetails
     */
    public function __construct(PaymentsRepository $paymentsRepository, MerchantDetails $merchantDetails)
    {
        $this->paymentsRepository = $paymentsRepository;
        $this->merchantDetails = $merchantDetails;
    }
}
