<?php

namespace Give\PaymentGateways\Gateways\Stripe\Controllers;

use Give\Helpers\Call;
use Give\PaymentGateways\Gateways\Stripe\Migrations\AddStatementDescriptorToStripeAccounts;
use Give\PaymentGateways\Stripe\Traits\HasStripeStatementDescriptorText;

/**
 * @unreleased
 */
class UpdateStatementDescriptorAjaxRequestController
{
    use HasStripeStatementDescriptorText;

    /**
     * @unreleased
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
        if (!$stripeAccountId || ! $stripeStatementDescriptorText) {
            wp_die('Forbidden', 403);
        }

        $stripeStatementDescriptorText = give_clean($this->filterStatementDescriptor($stripeStatementDescriptorText));

        try {
            Call::invoke(
                AddStatementDescriptorToStripeAccounts::class,
                $stripeAccountId,
                $stripeStatementDescriptorText
            );

            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error(['errorMessage' => $e->getMessage()]);
        }
    }
}
