<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions;

use Give\Donations\Models\Donation;
use Stripe\Invoice;

/**
 * @since 4.3.0
 */
class UpdateStripeInvoiceMetaData
{
    /**
     * @since 4.3.0
     */
    public function __invoke(Invoice $invoice, Donation $donation)
    {
        $invoice->updateAttributes(['metadata' => give_stripe_prepare_metadata($donation->id)]);
        $invoice->save();
    }
}
