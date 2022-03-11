<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

/**
 * @since 2.19.0
 */
trait BECSMandateForm
{
    use FormFieldMarkup;

    public function getMandateFormHTML( $form_id, $args ) {
        ob_start();

        $id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';

        do_action( 'give_before_cc_fields', $form_id ); ?>

        <fieldset id="give_cc_fields" class="give-do-validate">
            <legend>
                <?php esc_attr_e( 'Bank Account Info', 'give' ); ?>
            </legend>

            <?php
            if ( is_ssl() ) {
                ?>
                <div id="give_secure_site_wrapper">
                    <span class="give-icon padlock"></span>
                    <span><?php esc_attr_e( 'This is a secure SSL encrypted payment.', 'give' ); ?></span>
                </div>
                <?php
            }

            if ( $this->canShowFields() ) {
                ?>
                <div id="give-bank-account-number-wrap" class="form-row form-row-responsive give-stripe-cc-field-wrap">
                    <label for="give-bank-account-number-field-<?php echo $id_prefix; ?>" class="give-label">
                        <?php esc_html_e( 'Bank Account', 'give' ); ?>
                        <span class="give-required-indicator">*</span>
                        <span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php esc_html_e( 'BSB Number and Account Number of your bank account.', 'give' ); ?>"></span>
                    </label>
                    <div
                        id="give-stripe-becs-fields-<?php echo $id_prefix; ?>"
                        class="give-stripe-becs-bank-account-field give-stripe-cc-field"
                        data-hide_icon="<?php echo give_stripe_becs_hide_icon( $form_id ); ?>"
                        data-icon_style="<?php echo give_stripe_get_becs_icon_style( $form_id ); ?>"
                    ></div>
                </div>
                <div class="form-row form-row-responsive give-stripe-becs-mandate-acceptance-text">
                    <?php
                    if ( give_is_setting_enabled( give_get_option( 'stripe_becs_mandate_acceptance_option', 'enabled' ) ) ) {
                        echo give_stripe_get_mandate_acceptance_text( 'becs' );
                    }
                    ?>
                </div>
                <?php
                /**
                 * This action hook is used to display content after the Stripe BECS field.
                 *
                 * @param int   $form_id Donation Form ID.
                 * @param array $args    List of additional arguments.
                 *
                 * @since 2.6.3
                 */
                do_action( 'give_stripe_after_becs_fields', $form_id, $args );
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

        $form = ob_get_clean();

        return $form;
    }
}
