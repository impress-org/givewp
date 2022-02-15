<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use Give\PaymentGateways\Stripe\Traits\HasStripeStatementDescriptorText;

/**
 * @unreleased
 */
class UpdateStatementDescriptorAjaxRequestController
{
    use HasStripeStatementDescriptorText;

    /**
     * @unreleased
     * @return void
     * @throws InvalidPropertyName
     */
    public function __invoke()
    {
        if (!current_user_can('manage_give_settings')) {
            wp_die('Forbidden', 403);
        }

        $settingRepository = give(Settings::class);
        $stripeAccountId = give_clean($_GET['account_slug']);
        $stripeStatementDescriptor = give_clean($this->filterStatementDescriptor(urldecode($_GET['statement-descriptor'])));

        if (empty($stripeStatementDescriptor)) {
            wp_send_json_error(['errorCode' => 'INVALID_STRIPE_STATEMENT_DESCRIPTOR']);
        }

        $stripeAccount = $settingRepository->getStripeAccountById($stripeAccountId);

        if ($stripeAccount === null) {
            wp_send_json_error(['errorCode' => 'INVALID_STRIPE_ACCOUNT_ID']);
        }

        if( $stripeStatementDescriptor === $stripeAccount->statementDescriptor ) {
            wp_send_json_success([
                'newStatementDescriptor' => $stripeStatementDescriptor
            ]);
        }

        $newStripeAccount = AccountDetail::fromArray(
            array_merge(
                $stripeAccount->toArray(),
                ['statement_descriptor' => $stripeStatementDescriptor]
            )
        );

        if ($settingRepository->updateStripeAccount($newStripeAccount)) {
            wp_send_json_success([
                'newStatementDescriptor' => $stripeStatementDescriptor
            ]);
        }

        wp_send_json_error();
    }
}
