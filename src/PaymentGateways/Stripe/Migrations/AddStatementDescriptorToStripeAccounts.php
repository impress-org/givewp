<?php

namespace Give\PaymentGateways\Stripe\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\PaymentGateways\Stripe\Repositories\Settings;

/**
 * @unreleased
 */
class AddStatementDescriptorToStripeAccounts extends Migration
{

    /**
     * @inerhitDoc
     * @unreleased
     */
    public function run()
    {
        $stripeSettings = give(Settings::class);
        $allStripeAccount = $stripeSettings->getAllStripeAccounts();

        if ($allStripeAccount) {
            $statementDescriptor = give_get_option('stripe_statement_descriptor');
            foreach ($allStripeAccount as $index => $stripAccount) {
                $allStripeAccount[$index]['statement_descriptor'] = $statementDescriptor;
            }

            give_update_option('_give_stripe_get_all_accounts', $allStripeAccount);
        }
    }

    /**
     * @inerhitDoc
     * @unreleased
     */
    public static function id()
    {
        return 'add-statement-descriptor-to-stripe-accounts';
    }

    /**
     * @inerhitDoc
     * @unreleased
     */
    public static function timestamp()
    {
        return strtotime('10-02-2022');
    }

    /**
     * @inerhitDoc
     * @unreleased
     */
    public static function title()
    {
        return 'Add Statement Descriptor To StripeAccounts';
    }
}
