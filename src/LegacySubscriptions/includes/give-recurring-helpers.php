<?php
/**
 * Give Recurring Helper Functions
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

/**
 * Is the donation recurring.
 *
 * Determines if a donation is a recurring donation; should be used only at time of making the donation.
 * Use Give_Recurring_Subscriber->has_subscription() to determine after subscription is made if it is in fact
 * recurring.
 *
 * @param  array  $payment_meta
 *
 * @return bool
 * @since 2.19.0 - this is copied over from give_recurring()->is_donation_recurring
 * @access public
 *
 */
function give_recurring_is_donation_recurring($payment_meta)
{
    // Ensure we have proper vars set
    if (isset($payment_meta['post_data'])) {
        $form_id = isset($payment_meta['post_data']['give-form-id']) ? $payment_meta['post_data']['give-form-id'] : 0;
        $price_id = isset($payment_meta['post_data']['give-price-id']) ? $payment_meta['post_data']['give-price-id'] : 0;
    } else {
        // fallback
        $form_id = isset($payment_meta['form_id']) ? $payment_meta['form_id'] : 0;
        $price_id = isset($payment_meta['price_id']) ? $payment_meta['price_id'] : 0;
    }

    // Check for donor's choice option
    $user_choice = isset($payment_meta['post_data']['give-recurring-period']) ? $payment_meta['post_data']['give-recurring-period'] : '';
    $user_custom_amount = isset($payment_meta['post_data']['give-price-id']) ? $payment_meta['post_data']['give-price-id'] : '';
    $recurring_enabled = give_get_meta($form_id, '_give_recurring', true);
    $custom_amount = give_get_meta($form_id, '_give_custom_amount', true);
    $custom_amount_recurring = give_get_meta($form_id, '_give_recurring_custom_amount_period', true, 'month');

    // If not empty this is a recurring donation (checkbox is checked)
    if (!empty($user_choice)) {
        return true;
    }

    if ((empty($user_choice) && 'yes_donor' === $recurring_enabled) ||
        (
            empty($user_choice) &&
            'yes_admin' === $recurring_enabled &&
            'once' === $custom_amount_recurring &&
            'custom' === $user_custom_amount
        )) {
        // User only wants to give once
        return false;
    }

    // Admin choice: check fields
    if (give_has_variable_prices($form_id) || ('yes_admin' === $recurring_enabled && give_is_setting_enabled(
                $custom_amount
            ))) {
        // get default selected price ID
        return give_recurring_is_recurring($form_id, $price_id);
    }

    // Set level
    return give_recurring_is_recurring($form_id);
}

/**
 * Is Donation Form Recurring?
 *
 * Check if a donation form is recurring.
 *
 * @param  int  $form_id  The donation form ID.
 * @param  int  $level_id  The multi-level ID.
 *
 * @return bool
 * @since 2.19.0 - this is copied over from give_recurring()->is_recurring
 * @since  1.0
 * @access public
 * @static
 *
 */
function give_recurring_is_recurring($form_id, $level_id = 0)
{
    $is_recurring = false;
    $levels = maybe_unserialize(give_get_meta($form_id, '_give_donation_levels', true));
    $recurring_option = give_get_meta($form_id, '_give_recurring', true);
    $period = give_recurring_get_period($form_id, $level_id);

    // If it's multi level with admin choice with admin does not choice recurring for that level.
    if (empty($period)) {
        return false;
    }

    /**
     * Check multi-level forms whether any level is recurring
     *
     * Conditions:
     * a. Form has variable price
     * b. The form has a recurring option enabled.
     */
    if (
        give_has_variable_prices($form_id)
        && (empty($recurring_option) || 'no' !== $recurring_option)
    ) {
        switch ($recurring_option) {
            // Is this a multi-level donor's choice?
            case 'yes_donor':
                return true;
                break;

            case 'yes_admin':
                if ('custom' === $level_id) {
                    return true;
                } else {
                    // Loop through levels and see if a level is recurring.
                    foreach ($levels as $level) {
                        // Is price recurring?
                        $level_recurring = (isset($level['_give_recurring']) && $level['_give_recurring'] == 'yes');

                        // check that this price is indeed recurring:
                        if ($level_id == $level['_give_id']['level_id'] && $level_recurring && false !== $period) {
                            $is_recurring = true;
                        } elseif (empty($level_id) && $level_recurring) {
                            // Checking for ANY recurring level - empty $level_id param.
                            $is_recurring = true;
                        }
                    }
                }
                break;
        }
    } elseif (!empty($recurring_option) && 'no' !== $recurring_option) {
        // Single level donation form.
        $is_recurring = true;
    }

    return $is_recurring;
}

/**
 * Get Period.
 *
 * Get the time period for a variable priced donation.
 *
 * @param  $form_id
 * @param  $price_id
 *
 * @return bool|string
 * @since 2.19.0 - this is copied over from give_recurring()->get_period
 * @access public
 * @static
 *
 */
function give_recurring_get_period($form_id, $price_id = 0)
{
    $recurring_option = give_get_meta($form_id, '_give_recurring', true);

    // Is this a variable price form & admins choice?
    if (give_has_variable_prices($form_id) && 'yes_admin' === $recurring_option) {
        if ('custom' === $price_id) {
            return give_get_meta($form_id, '_give_recurring_custom_amount_period', true, 'month');
        } else {
            $levels = give_get_meta($form_id, '_give_donation_levels', true);

            foreach ($levels as $price) {
                // Check that this indeed the recurring price.
                if ($price_id == $price['_give_id']['level_id']
                    && isset($price['_give_recurring'])
                    && 'yes' === $price['_give_recurring']
                    && isset($price['_give_period'])
                ) {
                    return isset($price['_give_period']) ? $price['_give_period'] : 'month';
                }
            }
        }
    } else {
        $recurring_period = give_get_meta($form_id, '_give_period_functionality', true, 'admin_choice');

        // This is either a Donor's Choice multi-level or set donation form.
        $period = give_get_meta($form_id, '_give_period', true);

        if ('donors_choice' === $recurring_period) {
            $period = give_get_meta($form_id, '_give_period_default_donor_choice', true, 'month');
        }

        if ($period) {
            return $period;
        }
    }

    return false;
}
