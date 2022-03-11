<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

trait FormFieldMarkup
{
    public function canShowFields() {
        $status       = true;
        $isConfigured = \Give\Helpers\Gateways\Stripe::isAccountConfigured();
        $isTestMode   = give_is_test_mode();
        $isSslActive  = is_ssl();

        if ( ! $isConfigured && ! $isSslActive && ! $isTestMode ) {
            // Account not configured, No SSL scenario.
            \Give_Notices::print_frontend_notice(
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__( 'Notice:', 'give' ),
                    $this->errorMessages['accountNotConfiguredNoSsl']
                )
            );
            $status = false;

        } elseif ( ! $isConfigured ) {
            // Account not configured scenario.
            \Give_Notices::print_frontend_notice(
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__( 'Notice:', 'give' ),
                    $this->errorMessages['accountNotConfigured']
                )
            );
            $status = false;

        } elseif ( ! $isTestMode && ! $isSslActive ) {
            // Account configured, No SSL scenario.
            \Give_Notices::print_frontend_notice(
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__( 'Notice:', 'give' ),
                    $this->errorMessages['accountConfiguredNoSsl']
                )
            );
            $status = false;
        }

        return $status;
    }
}
