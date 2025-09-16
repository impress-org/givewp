<?php

use Give\Subscriptions\Models\Subscription;

/**
 * Add subscription options to the import dropdown
 *
 * @unreleased
 *
 * @param array $options Existing donation options
 * @return array Modified options with subscription fields
 */
function give_add_subscription_import_options( $options ) {
	$subscription_options = [
		'subscription_billing_period' => [
			__( 'Subscription Billing Period', 'give' ),
			__( 'Billing Period', 'give' ),
			__( 'Period', 'give' ),
		],
		'subscription_billing_times' => [
			__( 'Subscription Billing Times', 'give' ),
			__( 'Billing Times', 'give' ),
			__( 'Times', 'give' ),
		],
		'subscription_billing_frequency' => [
			__( 'Subscription Billing Frequency', 'give' ),
			__( 'Billing Frequency', 'give' ),
			__( 'Frequency', 'give' ),
		],
		'payment_type' => [
			__( 'Payment Type', 'give' ),
			__( 'Type', 'give' ),
		],
	];

	return array_merge( $options, $subscription_options );
}

/**
 * Add subscription optgroup to import dropdown
 *
 * @unreleased
 *
 * @param int $index Column index
 * @param string $value Column value
 * @param array $mapto Mapped values
 * @param string $current_mapto Current mapped value
 */
function give_add_subscription_import_optgroup( $index, $value, $mapto, $current_mapto ) {
	?>
	<optgroup label="<?php _e( 'Subscriptions', 'give' ); ?>">
		<?php
		$subscription_options = give_add_subscription_import_options( [] );
		$selectedOptions = [];

		// Get the current import class instance to use its method
		$import_class = Give_Import_Donations::get_instance();

		foreach ( $subscription_options as $option => $option_value ) {
			$ignore = array();
			if ( isset( $option_value['ignore'] ) && is_array( $option_value['ignore'] ) ) {
				$ignore = $option_value['ignore'];
				unset( $option_value['ignore'] );
			}

			$option_value_texts = (array) $option_value;
			$option_text = $option_value_texts[0];

			// Initialize selected as false
			$selected = false;

			// If current field matches option and it's not already selected, mark as selected
			if ( $current_mapto === $option && !in_array($option, $selectedOptions) ) {
				$selected = 'selected';
				$selectedOptions[] = $option; // Add to selected options
			} else {
				// Check against option value texts if not already selected
				if ( ! in_array( $value, $ignore ) && !in_array($option, $selectedOptions) ) {
					foreach ( $option_value_texts as $option_value_text ) {
						$selected = $import_class->selected( $option_value_text, $value );
						if ( $selected ) {
							$selectedOptions[] = $option; // Add to selected options
							break;
						}
					}
				}
			}
			?>
			<option value="<?php echo esc_attr($option); ?>" <?php echo esc_html($selected); ?> ><?php echo esc_html($option_text); ?></option>
			<?php
		}
		?>
	</optgroup>
	<?php
}

/**
 * Get subscription data during import
 *
 * @unreleased
 *
 * @param array $data Import data
 * @return array Modified data with subscription processing
 */
function give_get_subscription_import_data( $data ) {
    // Subscription billing period
    if ( ! empty( $data['subscription_billing_period'] ) ) {
        $data['subscription_period'] = give_normalize_subscription_period( $data['subscription_billing_period'] );
    }

    // Subscription billing frequency
    if ( ! empty( $data['subscription_billing_frequency'] ) ) {
        $data['subscription_frequency'] = absint( $data['subscription_billing_frequency'] );
    } else {
        $data['subscription_frequency'] = 1; // Default to 1
    }

    // Subscription billing times (installments)
    if ( ! empty( $data['subscription_billing_times'] ) ) {
        if ( strtolower( $data['subscription_billing_times'] ) === 'ongoing' ) {
            $data['subscription_installments'] = 0; // 0 means indefinite
        } else {
            $data['subscription_installments'] = absint( $data['subscription_billing_times'] );
        }
    } else {
        $data['subscription_installments'] = 0; // Default to indefinite
    }

    return [
        'subscription_period' => $data['subscription_period'] ?? 'month',
        'subscription_frequency' => $data['subscription_frequency'] ?? 1,
        'subscription_installments' => $data['subscription_installments'] ?? 0,
    ];

}

/**
 * Normalize subscription period to valid values
 *
 * @unreleased
 *
 * @param string $period Raw period from CSV
 * @return string Normalized period
 */
function give_normalize_subscription_period( $period ) {
	$period = strtolower( trim( $period ) );

	$period_mapping = [
		'month' => 'month',
		'monthly' => 'month',
		'months' => 'month',
		'week' => 'week',
		'weekly' => 'week',
		'weeks' => 'week',
		'day' => 'day',
		'daily' => 'day',
		'days' => 'day',
		'year' => 'year',
		'yearly' => 'year',
		'years' => 'year',
		'annual' => 'year',
		'annually' => 'year',
	];

	return $period_mapping[$period] ?? 'month';
}

if (defined('GIVE_RECURRING_VERSION')) {
    add_filter( 'give_import_donations_options', 'give_add_subscription_import_options' );
    add_action( 'give_import_dropdown_option', 'give_add_subscription_import_optgroup', 10, 4 );
}
