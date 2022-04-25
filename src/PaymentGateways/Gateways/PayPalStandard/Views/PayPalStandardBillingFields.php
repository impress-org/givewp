<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Views;

use Give\Helpers\Form\Utils;

/**
 * This class handle donation form billing field markup for PayPal Standard.
 *
 * @unlreased
 */
class PayPalStandardBillingFields
{
    /**
     * @unlreased
     *
     * @param int $formId
     *
     * @return string
     */
    public function __invoke($formId)
    {
        ob_start();

        if (give_is_setting_enabled(give_get_option('paypal_standard_billing_details'))) {
            give_default_cc_address_fields($formId);
        } elseif (Utils::isLegacyForm($formId)) {
            echo '';
        } else {
            printf(
                '
                <fieldset class="no-fields">
                    <div style="display: flex; justify-content: center; margin-top: 20px;">
                       %4$s
                    </div>
                    <p style="text-align: center;"><b>%1$s</b></p>
                    <p style="text-align: center;"><b>%2$s</b> %3$s</p>
                </fieldset>
                ',
                esc_html__('Make your donation quickly and securely with PayPal', 'give'),
                esc_html__('How it works:', 'give'),
                esc_html__(
                    'You will be redirected to PayPal to pay using your PayPal account, or with a credit or debit card. You will then be brought back to this page to view your receipt.',
                    'give'
                ),
                $this->getLogo()
            );
        }

        return ob_get_clean();
    }

    /**
     * Return paypal logo.
     *
     * @since 2.19.0
     * @since 2.19.4 Use correct logo path.
     *
     * @return string
     */
    private function getLogo()
    {
        return file_get_contents(
            GIVE_PLUGIN_DIR . 'src/PaymentGateways/Gateways/PayPalStandard/resources/templates/paypal-standard-logo.svg'
        );
    }
}
