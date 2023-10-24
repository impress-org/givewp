<?php

namespace Give\PaymentGateways\Gateways\Offline\Views;

/**
 * Generates the markup for the legacy offline payment fields using the admin settings.
 *
 * @since 3.0.0
 */
class LegacyFormFieldMarkup
{
    /**
     * @since 3.0.0
     */
    public function __invoke(int $formId, bool $includeBillingFields): string
    {
        $offlineInstructions = stripslashes(give_get_offline_payment_instruction($formId, true));
        $billingFields = $includeBillingFields ? $this->getBillingFieldsMarkup($formId) : '';

        return trim(
            "
            {$billingFields}
            {$this->getBeforeFieldsHookOutput($formId)}
            <fieldset class='no-fields' id='give_offline_payment_info'>
                {$offlineInstructions}
            </fieldset>
            {$this->getAfterFieldsHookOutput($formId)}
        "
        );
    }

    /**
     * @since 3.0.0
     */
    private function getBeforeFieldsHookOutput(int $formId): string
    {
        ob_start();

        /**
         * Fires before the offline info fields.
         *
         * @since 1.0
         *
         * @param int $form_id Give form id.
         */
        do_action('give_before_offline_info_fields', $formId);

        return ob_get_clean();
    }

    /**
     * @since 3.0.0
     */
    private function getAfterFieldsHookOutput(int $formId): string
    {
        ob_start();

        /**
         * Fires after the offline info fields.
         *
         * @since 1.0
         *
         * @param int $form_id Give form id.
         */
        do_action('give_after_offline_info_fields', $formId);

        return ob_get_clean();
    }

    private function getBillingFieldsMarkup(int $formId): string
    {
        $post_offline_cc_fields = give_get_meta($formId, '_give_offline_donation_enable_billing_fields_single', true);
        $post_offline_customize_option = give_get_meta($formId, '_give_customize_offline_donations', true, 'global');

        $global_offline_cc_fields = give_get_option('give_offline_donation_enable_billing_fields');

        return (give_is_setting_enabled($post_offline_customize_option, 'global') && give_is_setting_enabled(
                $global_offline_cc_fields
            ))
               || (give_is_setting_enabled($post_offline_customize_option, 'enabled') && give_is_setting_enabled(
                $post_offline_cc_fields
            )) ? give_default_cc_address_fields($formId, true) : '';
    }
}
