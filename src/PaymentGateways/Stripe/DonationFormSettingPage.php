<?php

namespace Give\PaymentGateways\Stripe;

use Give\PaymentGateways\Stripe\Admin\AccountManagerSettingField;
use Give\PaymentGateways\Stripe\Admin\CustomizeAccountField;

/**
 * Class DonationFormSettingPage
 *
 * @package Give\PaymentGateways\Stripe
 *
 * @since 2.13.0
 */
class DonationFormSettingPage
{
    /**
     * @since 2.13.0
     *
     * @param array $settings Settings List.
     * @param       $formId
     *
     * @return array
     */
    function __invoke($settings, $formId)
    {
        if ( ! $this->canRegisterTab()) {
            return $settings;
        }

        $settings['stripe_form_account_options'] = [
            'id' => 'stripe_form_account_options',
            'title' => esc_html__('Stripe Account', 'give'),
            'icon-html' => '<i class="fab fa-stripe-s"></i>',
            'fields' => $this->getMainSettingFields($formId),
        ];

        return $settings;
    }

    /**
     * @since 2.13.0
     *
     * @param int $formId
     *
     * @return array[]
     */
    private function getMainSettingFields($formId)
    {
        $formAccount = give_is_setting_enabled(
            give_get_meta(
                $formId,
                'give_stripe_per_form_accounts',
                true
            )
        );

        return [
            [
                'id' => 'give_stripe_per_form_accounts',
                'type' => 'give_stripe_per_form_accounts',
                'callback' => [give(CustomizeAccountField::class), 'handle'],
                'default' => CustomizeAccountField::DEFAULT_VALUE,
            ],
            [
                'id' => 'give_manage_accounts',
                'type' => 'give_manage_accounts',
                'callback' => [give(AccountManagerSettingField::class), 'handle'],
                'wrapper_class' => $formAccount ? 'give-stripe-manage-account-options' : 'give-stripe-manage-account-options give-hidden',
            ],
            [
                'name' => 'donation_stripe_per_form_docs',
                'type' => 'docs_link',
                'url' => 'http://docs.givewp.com/stripe-free',
                'title' => esc_html__('Stripe Documentation', 'give'),
            ],
        ];
    }

    /**
     * @since 2.13.0
     * @return bool
     */
    private function canRegisterTab()
    {
        return give_stripe_is_any_payment_method_active();
    }
}
