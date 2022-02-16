<?php

namespace Give\PaymentGateways\Stripe\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use Give\PaymentGateways\Stripe\Traits\HasStripeStatementDescriptorText;

/**
 * @unreleased
 */
class AddStatementDescriptorToStripeAccounts extends Migration
{
    use HasStripeStatementDescriptorText;

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
                if( ! isset( $stripAccount['statement_descriptor'] ) ) {
                    $allStripeAccount[$index]['statement_descriptor'] = $this->filterStatementDescriptor($statementDescriptor);
                }
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
