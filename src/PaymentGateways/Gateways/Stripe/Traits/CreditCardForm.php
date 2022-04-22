<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

use Give_Notices;

trait CreditCardForm
{
    use FormFieldMarkup;

    /**
     * @note Stripe uses its own credit card form to tokenize card details.
     * @note Input fields for credit cards should NOT be posted with the donation data.
     *
     * @param int   $form_id Donation Form ID.
     * @param array $args    Donation Form Arguments.
     *
     * @since 2.19.0 Migrated from the legacy Give_Stripe_Card::addCreditCardForm implementation of the Stripe Gateway.
     *
     * @return string
     */
    public function getCreditCardFormHTML( $form_id, $args ) {

        ob_start();
        $idPrefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';

        do_action( 'give_before_cc_fields', $form_id ); ?>

        <fieldset id="give_cc_fields" class="give-do-validate">
            <legend>
                <?php esc_attr_e( 'Credit Card Info', 'give' ); ?>
            </legend>

            <?php
            if ( is_ssl() ) {
                ?>
                <div id="give_secure_site_wrapper">
                    <span class="give-icon padlock"></span>
                    <span>
					<?php esc_attr_e( 'This is a secure SSL encrypted payment.', 'give' ); ?>
				</span>
                </div>
                <?php
            }

            if ( $this->canShowFields() ) {
                // Show Credit Card Fields.
                echo \Give\Helpers\Gateways\Stripe::showCreditCardFields( $idPrefix );

                /**
                 * This action hook is used to display content after the Credit Card expiration field.
                 *
                 * Note: Kept this hook as it is.
                 *
                 * @since 2.5.0
                 *
                 * @param int   $form_id Donation Form ID.
                 * @param array $args    List of additional arguments.
                 */
                do_action( 'give_after_cc_expiration', $form_id, $args );

                /**
                 * This action hook is used to display content after the Credit Card expiration field.
                 *
                 * @since 2.5.0
                 *
                 * @param int   $form_id Donation Form ID.
                 * @param array $args    List of additional arguments.
                 */
                do_action( 'give_stripe_after_cc_expiration', $form_id, $args );
            }
            ?>
        </fieldset>
        <?php
        // Remove Address Fields if user has option enabled.
        $billing_fields_enabled = give_get_option( 'stripe_collect_billing' );
        if ( ! $billing_fields_enabled ) {
            remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
        }

        do_action( 'give_after_cc_fields', $form_id, $args );

        return ob_get_clean();
    }

    /**
     * This function is used to determine whether to show the payment fields or not.
     *
     * @since  2.7.0
     * @access public
     *
     * @return bool
     */
    public function canShowFields() {

        $status       = true;
        $isConfigured = \Give\Helpers\Gateways\Stripe::isAccountConfigured();
        $isTestMode   = give_is_test_mode();
        $isSslActive  = is_ssl();

        if ( ! $isConfigured && ! $isSslActive && ! $isTestMode ) {
            // Account not configured, No SSL scenario.
            Give_Notices::print_frontend_notice(
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__( 'Notice:', 'give' ),
                    $this->errorMessages['accountNotConfiguredNoSsl']
                )
            );
            $status = false;

        } elseif ( ! $isConfigured ) {
            // Account not configured scenario.
            Give_Notices::print_frontend_notice(
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__( 'Notice:', 'give' ),
                    $this->errorMessages['accountNotConfigured']
                )
            );
            $status = false;

        } elseif ( ! $isTestMode && ! $isSslActive ) {
            // Account configured, No SSL scenario.
            Give_Notices::print_frontend_notice(
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
