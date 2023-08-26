<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * Update Stripe settings on backwards compatible form meta.
 *
 * @since 3.0.0
 */
class UpdateStripeFormBuilderSettingsMeta
{
    /**
     * @since 3.0.0
     * @param  DonationForm  $form
     */
    public function __invoke(DonationForm $form)
    {
        $paymentGatewaysBlock = $form->blocks->findByName('givewp/payment-gateways');
        if (!$paymentGatewaysBlock) {
            give()->form_meta->delete_meta($form->id, 'give_stripe_per_form_accounts');
            give()->form_meta->delete_meta($form->id, '_give_stripe_default_account');

            return;
        }

        [
            'stripeUseGlobalDefault' => $stripeUseGlobalDefault,
            'stripeAccountId' => $stripeAccountId
        ] = $paymentGatewaysBlock->getAttributes() + ['stripeUseGlobalDefault' => true, 'stripeAccountId' => ''];

        if (is_bool($stripeUseGlobalDefault)) {
            give()->form_meta->update_meta($form->id, "give_stripe_per_form_accounts", $stripeUseGlobalDefault ? 'disabled' : 'enabled');
        }

        if (is_string($stripeAccountId)) {
            give()->form_meta->update_meta($form->id, "_give_stripe_default_account", $stripeAccountId);
        }
    }
}
