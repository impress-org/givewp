<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

/**
 * @since 2.19.0
 * @TODO We should eventually migrate this client-side redirect to a server-side redirect using the url property of the session.
 */
trait CheckoutRedirect
{
    /**
     * @since 2.19.0
     * @param $sessionId
     * @param $formId
     * @return string
     */
    public function getRedirectUrl( $sessionId, $formId )
    {
        return esc_url_raw(add_query_arg(
            [
                'action'  => 'checkout_processing',
                'session' => $sessionId,
                'id'      => $formId,
            ],
            site_url()
        ));
    }

    /**
     * @since  2.5.5
     * @since 2.19.0 Migrated from the legacy Give_Stripe_Checkout::redirect_to_checkout implementation of the Stripe Checkout Gateway.
     * @return void
     */
    public static function maybeHandleRedirect()
    {
        $get_data          = give_clean( $_GET );
        $form_id           = ! empty( $get_data['id'] ) ? absint( $get_data['id'] ) : false;
        $publishable_key   = give_stripe_get_publishable_key( $form_id );
        $session_id        = ! empty( $get_data['session'] ) ? $get_data['session'] : false;
        $action            = ! empty( $get_data['action'] ) ? $get_data['action'] : false;
        $default_account   = give_stripe_get_default_account( $form_id );
        $stripe_account_id = give_stripe_get_connected_account_id( $form_id );

        // Bailout, if action is not checkout processing.
        if ( 'checkout_processing' !== $action ) {
            return;
        }

        // Bailout, if session id doesn't exists.
        if ( ! $session_id ) {
            return;
        }
        ?>
        <div id="give-stripe-checkout-processing"></div>
        <script>
            // Show Processing Donation Overlay.
            const processingHtml = document.querySelector( '#give-stripe-checkout-processing');

            processingHtml.setAttribute( 'class', 'stripe-checkout-process' );
            processingHtml.style.background = '#FFFFFF';
            processingHtml.style.opacity = '0.9';
            processingHtml.style.position = 'fixed';
            processingHtml.style.top = '0';
            processingHtml.style.left = '0';
            processingHtml.style.bottom = '0';
            processingHtml.style.right = '0';
            processingHtml.style.zIndex = '2147483646';
            processingHtml.innerHTML = '<div class="give-stripe-checkout-processing-container" style="position: absolute;top: 50%;left: 50%;width: 300px; margin-left: -150px; text-align:center;"><div style="display:inline-block;"><span class="give-loading-animation" style="color: #333;height:26px;width:26px;font-size:26px; margin:0; "></span><span style="color:#000; font-size: 26px; margin:0 0 0 10px;">' + give_stripe_vars.checkout_processing_text + '</span></div></div>';

            window.addEventListener('load', function() {
                let stripe = {};

                stripe = Stripe( '<?php echo $publishable_key; ?>' );

                <?php if ( ! empty( $stripe_account_id ) ) { ?>
                stripe = Stripe( '<?php echo $publishable_key; ?>', {
                    'stripeAccount': '<?php echo $stripe_account_id; ?>'
                } );
                <?php } ?>

                // Redirect donor to Checkout page.
                stripe.redirectToCheckout({
                    // Make the id field from the Checkout Session creation API response
                    // available to this file, so you can provide it as parameter here
                    // instead of the {{CHECKOUT_SESSION_ID}} placeholder.
                    sessionId: '<?php echo $session_id; ?>'
                }).then( ( result ) => {
                    console.log(result);
                    // If `redirectToCheckout` fails due to a browser or network
                    // error, display the localized error message to your customer
                    // using `result.error.message`.
                });
            })
        </script>
        <?php
    }
}
