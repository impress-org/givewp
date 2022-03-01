<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Gateways\Stripe;

/**
 * @since 2.19.0
 */
trait CheckoutModal
{
    /**
     * @param int   $formId Donation Form ID.
     * @param array $args   Donation Form Arguments.
     *
     * @since 2.19.0 Migrated from the legacy Give_Stripe_Checkout::showCheckoutModal implementation of the Stripe Checkout Gateway.
     *
     * @return string
     */
    public function getCheckoutModalHTML( $formId, $args )
    {
        $idPrefix           = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : "{$formId}-1";
        $backgroundImageUrl = give_get_option( 'stripe_checkout_background_image', '' );
        $backgroundItem     = 'background-color: #000000;';

        // Load Background Image, if exists.
        if ( ! empty( $backgroundImageUrl ) ) {
            $backgroundImageUrl = esc_url( $backgroundImageUrl );
            $backgroundItem     = "background-image: url('{$backgroundImageUrl}'); background-size: cover;";
        }

        ob_start();
        ?>
        <div id="give-stripe-checkout-modal-<?php echo $idPrefix; ?>" class="give-stripe-checkout-modal">
            <div class="give-stripe-checkout-modal-content">
                <div class="give-stripe-checkout-modal-container">
                    <div class="give-stripe-checkout-modal-header" style="<?php echo $backgroundItem; ?>">
                        <button class="give-stripe-checkout-modal-close">
                            <svg
                                width="20px"
                                height="20px"
                                viewBox="0 0 20 20"
                                version="1.1"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink"
                            >
                                <defs>
                                    <path
                                        d="M10,8.8766862 L13.6440403,5.2326459 C13.9542348,4.92245137 14.4571596,4.92245137 14.7673541,5.2326459 C15.0775486,5.54284044 15.0775486,6.04576516 14.7673541,6.3559597 L11.1238333,9.99948051 L14.7673541,13.6430016 C15.0775486,13.9531961 15.0775486,14.4561209 14.7673541,14.7663154 C14.4571596,15.0765099 13.9542348,15.0765099 13.6440403,14.7663154 L10,11.1222751 L6.3559597,14.7663154 C6.04576516,15.0765099 5.54284044,15.0765099 5.2326459,14.7663154 C4.92245137,14.4561209 4.92245137,13.9531961 5.2326459,13.6430016 L8.87616671,9.99948051 L5.2326459,6.3559597 C4.92245137,6.04576516 4.92245137,5.54284044 5.2326459,5.2326459 C5.54284044,4.92245137 6.04576516,4.92245137 6.3559597,5.2326459 L10,8.8766862 Z"
                                        id="path-1"
                                    ></path>
                                </defs>
                                <g
                                    id="Payment-recipes"
                                    stroke="none"
                                    stroke-width="1"
                                    fill="none"
                                    fill-rule="evenodd"
                                >
                                    <g
                                        id="Elements-Popup"
                                        transform="translate(-816.000000, -97.000000)"
                                    >
                                        <g id="close-btn" transform="translate(816.000000, 97.000000)">
                                            <circle
                                                id="Oval"
                                                fill-opacity="0.3"
                                                fill="#AEAEAE"
                                                cx="10"
                                                cy="10"
                                                r="10"
                                            ></circle>
                                            <mask id="mask-2" fill="white">
                                                <use xlink:href="#path-1"></use>
                                            </mask>
                                            <use
                                                id="Mask"
                                                fill-opacity="0.5"
                                                fill="#FFFFFF"
                                                opacity="0.5"
                                                xlink:href="#path-1"
                                            ></use>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <h3><?php echo give_get_option( 'stripe_checkout_name' ); ?></h3>
                        <div class="give-stripe-checkout-donation-amount">
                            <?php echo give_get_form_price( $formId ); ?>
                        </div>
                        <div class="give-stripe-checkout-donor-email"></div>
                        <div class="give-stripe-checkout-form-title">
                            <?php echo get_the_title( $formId ); ?>
                        </div>
                    </div>
                    <div class="give-stripe-checkout-modal-body">
                        <?php
                        /**
                         * This action hook will be trigger in Stripe Checkout Modal before CC fields.
                         *
                         * @since 2.7.3
                         */
                        do_action( 'give_stripe_checkout_modal_before_cc_fields', $formId, $args );

                        // Load Credit Card Fields for Stripe Checkout.
                        echo Stripe::showCreditCardFields( $idPrefix );

                        /**
                         * This action hook will be trigger in Stripe Checkout Modal after CC fields.
                         *
                         * @since 2.7.3
                         */
                        do_action( 'give_stripe_checkout_modal_after_cc_fields', $formId, $args );
                        ?>
                        <input type="hidden" name="give_validate_stripe_payment_fields" value="0"/>
                    </div>
                    <div class="give-stripe-checkout-modal-footer">
                        <div class="card-errors"></div>
                        <?php
                        $display_label_field = give_get_meta( $formId, '_give_checkout_label', true );
                        $display_label_field = apply_filters( 'give_donation_form_submit_button_text', $display_label_field, $formId, $args );
                        $display_label       = ( ! empty( $display_label_field ) ? $display_label_field : esc_html__( 'Donate Now', 'give' ) );
                        ?>
                        <div class="give-submit-button-wrap give-stripe-checkout-modal-btn-wrap give-clearfix">
                            <?php
                            echo sprintf(
                                '<input type="submit" class="%1$s" id="%2$s" value="%3$s" data-before-validation-label="%3$s" name="%4$s" data-is_legacy_form="%5$s" disabled/>',
                                FormUtils::isLegacyForm() ? 'give-btn give-stripe-checkout-modal-donate-button' : 'give-btn give-stripe-checkout-modal-sequoia-donate-button',
                                "give-stripe-checkout-modal-donate-button-{$idPrefix}",
                                $display_label,
                                'give_stripe_modal_donate',
                                FormUtils::isLegacyForm()
                            );
                            ?>
                            <span class="give-loading-animation"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
