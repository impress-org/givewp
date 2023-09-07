<?php

namespace Give\PaymentGateways\Gateways\Offline\Actions;

use Give\DonationForms\Models\DonationForm;

class UpdateOfflineMetaFromFormBuilder
{
    public function __invoke(DonationForm $form)
    {
        $paymentGatewaysBlock = $form->blocks->findByName('givewp/payment-gateways');
        if (!$paymentGatewaysBlock) {
            return;
        }

        [
            'offlineEnabled' => $enabled,
            'offlineUseGlobalInstructions' => $useGlobalInstructions,
            'offlineDonationInstructions' => $donationInstructions,
        ] = $paymentGatewaysBlock->getAttributes() + [
            'offlineEnabled' => true,
            'offlineUseGlobalInstructions' => true,
            'offlineDonationInstructions' => '',
        ];

        if (!$enabled) {
            $modeMeta = 'disabled';
        } elseif ($useGlobalInstructions) {
            $modeMeta = 'global';
        } else {
            $modeMeta = 'enabled';
        }

        give()->form_meta->update_meta(
            $form->id,
            '_give_customize_offline_donations',
            $modeMeta
        );

        give()->form_meta->update_meta(
            $form->id,
            '_give_offline_checkout_notes',
            $donationInstructions
        );
    }
}
