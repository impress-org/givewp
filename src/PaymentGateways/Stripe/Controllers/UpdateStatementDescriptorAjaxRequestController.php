<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;

/**
 * @unreleased
 */
class UpdateStatementDescriptorAjaxRequestController
{
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
        $stripeStatementDescriptor = $this->getStatementDescriptor();

        if (empty($stripeStatementDescriptor)) {
            wp_send_json_error(['errorCode' => 'INVALID_STRIPE_STATEMENT_DESCRIPTOR']);
        }

        $stripeAccount = $settingRepository->getStripeAccountById($stripeAccountId);

        if ($stripeAccount === null) {
            wp_send_json_error(['errorCode' => 'INVALID_STRIPE_ACCOUNT_ID']);
        }

        $newStripeAccount = AccountDetail::fromArray(
            array_merge(
                $stripeAccount->toArray(),
                ['statement_descriptor' => $stripeStatementDescriptor]
            )
        );

        if ($settingRepository->updateStripeAccount($newStripeAccount)) {
            wp_send_json_success();
        }

        wp_send_json_error();
    }

    /**
     * Check Stripe statement descriptor requirements: https://stripe.com/docs/statement-descriptors#requirements
     *
     * @unreleased
     * @return string
     */
    private function getStatementDescriptor()
    {
        $maxLength = 22;
        $minLength = 5;
        $statementDescriptor = urldecode($_GET['statement-descriptor']);

        $unsupportedCharacters = ['<', '>', '"', '\\', '\'', '*']; // Reserve keywords.
        $statementDescriptor = mb_substr($statementDescriptor, 0, $maxLength);
        $statementDescriptor = str_replace($unsupportedCharacters, '', $statementDescriptor);
        $statementDescriptor = give_clean($statementDescriptor);


        return $minLength > strlen($statementDescriptor) ? '' : give_clean($statementDescriptor);
    }
}
