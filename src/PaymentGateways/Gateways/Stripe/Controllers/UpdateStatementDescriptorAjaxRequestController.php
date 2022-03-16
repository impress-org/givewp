<?php

namespace Give\PaymentGateways\Gateways\Stripe\Controllers;

use Give\Helpers\Call;
use Give\PaymentGateways\Gateways\Stripe\Actions\UpdateStripeAccountStatementDescriptor;
use Give\PaymentGateways\Gateways\Stripe\Migrations\AddStatementDescriptorToStripeAccounts;
use Give\PaymentGateways\Stripe\Traits\HasStripeStatementDescriptorText;

/**
 * @since 2.19.0
 */
class UpdateStatementDescriptorAjaxRequestController
{
    use HasStripeStatementDescriptorText;

    /**
     * @since 2.19.0
     */
    public function __invoke()
    {
        // Authorize action?
        if (!current_user_can('manage_give_settings')) {
            wp_die('Forbidden', 403);
        }

        $stripeAccountId = give_clean($_GET['account-slug']);
        $stripeStatementDescriptorText = urldecode(trim($_GET['statement-descriptor']));

        // Valid data?
        if (!$stripeAccountId || !$stripeStatementDescriptorText) {
            wp_die('Forbidden', 403);
        }

        try {
            $this->validateStatementDescriptor($stripeStatementDescriptorText);
            $stripeStatementDescriptorText = give_clean($stripeStatementDescriptorText);
            Call::invoke(
                UpdateStripeAccountStatementDescriptor::class,
                $stripeAccountId,
                $stripeStatementDescriptorText
            );

            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error(['errorMessage' => $e->getMessage()]);
        }
    }
}
