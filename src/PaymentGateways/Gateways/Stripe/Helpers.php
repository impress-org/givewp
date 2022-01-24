<?php

namespace Give\PaymentGateways\Gateways\Stripe;

class Helpers
{
    /**
     * Save Stripe Customer ID.
     *
     * @param string $stripe_customer_id Customer ID.
     * @param int    $payment_id         Payment ID.
     *
     * @since 2.5.0
     */
    public static function save_stripe_customer_id( $stripe_customer_id, $payment_id ) {

        // Update customer meta.
        if ( class_exists( 'Give_DB_Donor_Meta' ) ) {

            $donor_id = give_get_payment_donor_id( $payment_id );

            // Get the Give donor.
            $donor = new \Give_Donor( $donor_id );

            // Update donor meta.
            $donor->update_meta( give_stripe_get_customer_key(), $stripe_customer_id );

        } elseif ( is_user_logged_in() ) {

            // Support saving to legacy method of user method.
            update_user_meta( get_current_user_id(), give_stripe_get_customer_key(), $stripe_customer_id );

        }

    }
}
