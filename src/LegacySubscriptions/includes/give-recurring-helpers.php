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
 * @since  1.0
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
 * Set up the time period IDs and labels
 *
 * @param  int  $number
 * @param  string  $period
 *
 * @return array
 * @since  1.6.0 Update Periods label.
 * @static
 *
 * @since  1.0
 */
function give_recurring_periods($number = 1, $period = '')
{
    $periods = apply_filters(
        'give_recurring_periods',
        [
            // translators: placeholder is number of days. (e.g. "Bill this every day / 4 days")
            'day' => sprintf(
                _nx(
                    'day',
                    '%s days',
                    $number,
                    'Recurring billing period.',
                    'give-recurring'
                ),
                $number
            ),
            // translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks")
            'week' => sprintf(
                _nx(
                    'week',
                    '%s weeks',
                    $number,
                    'Recurring billing period.',
                    'give-recurring'
                ),
                $number
            ),
            // translators: placeholder is number of months. (e.g. "Bill this every month / 4 months")
            'month' => sprintf(
                _nx(
                    'month',
                    '%s months',
                    $number,
                    'Recurring billing period.',
                    'give-recurring'
                ),
                $number
            ),
            // translators: placeholder is number of quarters. (e.g. "Bill this every quarter / 4 times in a year")
            'quarter' => sprintf(
                _nx(
                    'quarter',
                    '%s quarters',
                    $number,
                    'Recurring billing period.',
                    'give-recurring'
                ),
                $number
            ),
            // translators: placeholder is number of years. (e.g. "Bill this every year / 4 years")
            'year' => sprintf(
                _nx(
                    'year',
                    '%s years',
                    $number,
                    'Recurring billing period.',
                    'give-recurring'
                ),
                $number
            ),
        ],
        $number
    );

    return !empty($periods[$period]) ? $periods[$period] : $periods;
}

/**
 * Get billing times.
 *
 * @param  string  $billing_period
 *
 * @return array
 * @since 1.6.0
 *
 */
function give_recurring_times($billing_period = '')
{
    $periods = give_recurring_ranges();

    $periods = apply_filters('give_recurring_times', $periods);

    if (!empty($billing_period)) {
        return $periods[$billing_period];
    }

    return $periods;
}

/**
 * Returns an array of Recurring lengths.
 *
 * PayPal Standard Allowable Ranges
 * D – for days; allowable range is 1 to 90
 * W – for weeks; allowable range is 1 to 52
 * M – for months; allowable range is 1 to 24
 * Y – for years; allowable range is 1 to 5
 *
 * @since 1.6.0
 */
function give_recurring_ranges()
{
    $periods = array_keys(give_recurring_periods());

    foreach ($periods as $period) {
        $subscription_lengths = [
            _x('Ongoing', 'Subscription length', 'give-recurring'),
        ];

        switch ($period) {
            case 'day':
                $subscription_lengths[] = _x(
                    '1 day',
                    'Subscription lengths. e.g. "For 1 day..."',
                    'give-recurring'
                );
                $subscription_range = range(2, 90);
                break;
            case 'week':
                $subscription_lengths[] = _x(
                    '1 week',
                    'Subscription lengths. e.g. "For 1 week..."',
                    'give-recurring'
                );
                $subscription_range = range(2, 52);
                break;
            case 'month':
                $subscription_lengths[] = _x(
                    '1 month',
                    'Subscription lengths. e.g. "For 1 month..."',
                    'give-recurring'
                );
                $subscription_range = range(2, 24);
                break;
            case 'quarter':
                $subscription_lengths[] = _x(
                    '1 quarter',
                    'Subscription lengths. e.g. "For 1 quarter..."',
                    'give-recurring'
                );
                $subscription_range = range(2, 12);
                break;
            case 'year':
                $subscription_lengths[] = _x(
                    '1 year',
                    'Subscription lengths. e.g. "For 1 year..."',
                    'give-recurring'
                );
                $subscription_range = range(2, 5);
                break;
        }

        foreach ($subscription_range as $number) {
            $subscription_range[$number] = give_recurring_periods($number, $period);
        }

        // Add the possible range to all time range
        $subscription_lengths += $subscription_range;

        $subscription_ranges[$period] = $subscription_lengths;
    }

    return $subscription_ranges;
}

/**
 * Set up the interval label.
 *
 * @param  string (optional) An interval in the range 1-6
 *
 * @return mixed
 * @since 1.6.0
 * Return an i18n'ified associative array of all possible subscription periods.
 *
 */
function give_recurring_interval($interval = '')
{
    $intervals = [1 => _x('every', 'period interval (eg "$10 _every_ 2 weeks")', 'give-recurring')];

    foreach (range(2, 6) as $i) {
        // translators: period interval, placeholder is ordinal (eg "$10 every _2nd/3rd/4th_", etc)
        $intervals[$i] = sprintf(
            _x(
                'every %s',
                'period interval with ordinal number (e.g. "every 2nd"',
                'give-recurring'
            ),
            give_recurring_append_numeral_suffix($i)
        );
    }

    $intervals = apply_filters('give_recurring_interval', $intervals);

    if (empty($interval)) {
        return $intervals;
    }

    return $intervals[$interval];
}

/**
 * Takes a number and returns the number with its relevant suffix appended, eg. for 2, the function returns 2nd
 *
 * @param  int  $number
 *
 * @return string
 * @since 1.6.0
 *
 */
function give_recurring_append_numeral_suffix($number)
{
    // Handle teens: if the tens digit of a number is 1, then write "th" after the number. For example: 11th, 13th, 19th, 112th, 9311th. http://en.wikipedia.org/wiki/English_numerals
    if (strlen($number) > 1 && 1 == substr($number, -2, 1)) {
        // translators: placeholder is a number, this is for the teens
        $number_string = sprintf(__('%sth', 'give-recurring'), $number);
    } else { // Append relevant suffix
        switch (substr($number, -1)) {
            case 1:
                // translators: placeholder is a number, numbers ending in 1
                $number_string = sprintf(__('%sst', 'give-recurring'), $number);
                break;
            case 2:
                // translators: placeholder is a number, numbers ending in 2
                $number_string = sprintf(__('%snd', 'give-recurring'), $number);
                break;
            case 3:
                // translators: placeholder is a number, numbers ending in 3
                $number_string = sprintf(__('%srd', 'give-recurring'), $number);
                break;
            default:
                // translators: placeholder is a number, numbers ending in 4-9, 0
                $number_string = sprintf(__('%sth', 'give-recurring'), $number);
                break;
        }
    }

    return apply_filters('give_recurring_numeral_suffix', $number_string, $number);
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
 * @since  1.0
 * @access public
 * @static
 *
 */
function give_recurring_get_period($form_id, $price_id = 0)
{
    $recurring_option = give_get_meta($form_id, '_give_recurring', true);

    // Is this a variable price form & admin's choice?
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

/**
 * Get Interval.
 *
 * Get the period interval for a variable priced donation.
 *
 * @param  $form_id
 * @param  $price_id
 *
 * @return bool|string
 * @since  1.6.0
 * @access public
 * @static
 *
 */
function give_recurring_get_interval($form_id, $price_id = 0)
{
    $recurring_option = give_get_meta($form_id, '_give_recurring', true);

    // Is this a variable price form & admin's choice?
    if (give_has_variable_prices($form_id) && 'yes_admin' === $recurring_option) {
        if ('custom' === $price_id) {
            return give_get_meta($form_id, '_give_recurring_custom_amount_interval', true, '1');
        } else {
            $levels = give_get_meta($form_id, '_give_donation_levels', true);

            foreach ($levels as $price) {
                // Check that this indeed the recurring price.
                if ($price_id == $price['_give_id']['level_id']
                    && isset($price['_give_recurring'])
                    && 'yes' === $price['_give_recurring']
                    && isset($price['_give_period'])
                ) {
                    return isset($price['_give_period_interval']) ? $price['_give_period_interval'] : 1;
                }
            }
        }
    } else {
        // This is either a Donor's Choice multi-level or set donation form.
        $period = give_get_meta($form_id, '_give_period_interval', true, 1);

        if ($period) {
            return $period;
        }
    }

    return false;
}

/**
 * Get Times.
 *
 * Get the number of times a price ID recurs.
 *
 * @param  $form_id
 * @param  $price_id
 *
 * @return int
 * @since  1.0
 * @access public
 * @static
 *
 */
function give_recurring_get_times($form_id, $price_id = 0)
{
    $recurring_option = give_get_meta($form_id, '_give_recurring', true);

    // is this a single or multi-level form?
    if (give_has_variable_prices($form_id) && 'yes_admin' === $recurring_option) {
        if ('custom' === $price_id) {
            return give_get_meta($form_id, '_give_recurring_custom_amount_times', true, 0);
        } else {
            $levels = maybe_unserialize(give_get_meta($form_id, '_give_donation_levels', true));

            foreach ($levels as $price) {
                // Check that this indeed the recurring price.
                if (
                    $price_id == $price['_give_id']['level_id'] &&
                    isset($price['_give_recurring']) &&
                    'yes' === $price['_give_recurring'] &&
                    isset($price['_give_times'])
                ) {
                    return isset($price['_give_times']) ? intval($price['_give_times']) : 0;
                }
            }
        }
    } else {
        $times = give_get_meta($form_id, '_give_times', true, 0);

        if ($times) {
            return $times;
        }
    }

    return 0;
}

/**
 * Get the number of times a single-price donation form recurs.
 *
 * @param  $form_id
 *
 * @return int|mixed
 * @since  1.0
 * @static
 *
 */
function give_recurring_get_times_single($form_id)
{
    $times = give_get_meta($form_id, '_give_times', true);

    if ($times) {
        return $times;
    }

    return 0;
}
