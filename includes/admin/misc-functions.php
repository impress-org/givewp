<?php

/**
 * Gets a number of posts and displays them as options
 *
 * @param  array $query_args Optional. Overrides defaults.
 * @param  bool  $force      Force the pages to be loaded even if not on settings
 *
 * @see: https://github.com/WebDevStudios/CMB2/wiki/Adding-your-own-field-types
 * @return array An array of options that matches the CMB2 options array
 */
function give_cmb2_get_post_options( $query_args, $force = false ) {

	$post_options = array( '' => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'give-settings' != $_GET['page'] ) && ! $force ) {
		return $post_options;
	}

	$args = wp_parse_args(
		$query_args, array(
			'post_type'   => 'page',
			'numberposts' => 10,
		)
	);

	$posts = get_posts( $args );

	if ( $posts ) {
		foreach ( $posts as $post ) {

			$post_options[ $post->ID ] = $post->post_title;

		}
	}

	return $post_options;
}


/**
 * Featured Image Sizes
 *
 * Outputs an array for the "Featured Image Size" option found under Settings > Display Options.
 *
 * @since 1.4
 *
 * @global $_wp_additional_image_sizes
 *
 * @return array $sizes
 */
function give_get_featured_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes            = array();
	$get_sizes        = get_intermediate_image_sizes();
	$core_image_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );

	// This will help us to filter special characters from a string
	$filter_slug_items = array( '_', '-' );

	foreach ( $get_sizes as $_size ) {

		// Converting image size slug to title case
		$sizes[ $_size ] = give_slug_to_title( $_size, $filter_slug_items );

		if ( in_array( $_size, $core_image_sizes ) ) {
			$sizes[ $_size ] .= ' (' . get_option( "{$_size}_size_w" ) . 'x' . get_option( "{$_size}_size_h" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] .= " ({$_wp_additional_image_sizes[ $_size ]['width']} x {$_wp_additional_image_sizes[ $_size ]['height']}";
		}

		// Based on the above image height check, label the respective resolution as responsive
		if ( ( array_key_exists( $_size, $_wp_additional_image_sizes ) && ! $_wp_additional_image_sizes[ $_size ]['crop'] ) || ( in_array( $_size, $core_image_sizes ) && ! get_option( "{$_size}_crop" ) ) ) {
			$sizes[ $_size ] .= ' - responsive';
		}

		$sizes[ $_size ] .= ')';

	}

	return apply_filters( 'give_get_featured_image_sizes', $sizes );
}


/**
 *  Slug to Title
 *
 *  Converts a string with hyphen(-) or underscores(_) or any special character to a string with Title case
 *
 * @since 1.8.8
 *
 * @param string $string
 * @param array  $filters
 *
 * @return string $string
 */
function give_slug_to_title( $string, $filters = array() ) {

	foreach ( $filters as $filter_item ) {
		$string = str_replace( $filter_item, ' ', $string );
	}

	// Return updated string after converting it to title case
	return ucwords( $string );

}


/**
 * Give License Key Callback
 *
 * Registers the license field callback for EDD's Software Licensing.
 *
 * @since       1.0
 * @since       2.0 Removed cmb2 param backward compatibility
 *
 * @param array $field
 * @param mixed $value
 *
 * @return void
 */
function give_license_key_callback( $field, $value ) {
	$id                 = $field['id'];
	$field_description  = $field['desc'];
	$license            = $field['options']['license'];
	$license_key        = $value;
	$is_license_key     = apply_filters( 'give_is_license_key', ( is_object( $license ) && ! empty( $license ) ) );
	$is_valid_license   = apply_filters( 'give_is_valid_license', ( $is_license_key && property_exists( $license, 'license' ) && 'valid' === $license->license ) );
	$shortname          = $field['options']['shortname'];
	$field_classes      = 'regular-text give-license-field';
	$type               = empty( $escaped_value ) || ! $is_valid_license ? 'text' : 'password';
	$custom_html        = '';
	$messages           = array();
	$class              = '';
	$account_page_link  = $field['options']['account_url'];
	$checkout_page_link = $field['options']['checkout_url'];
	$addon_name         = $field['options']['item_name'];
	$license_status     = null;
	$is_in_subscription = null;

	// By default query on edd api url will return license object which contain status and message property, this can break below functionality.
	// To combat that check if status is set to error or not, if yes then set $is_license_key to false.
	if ( $is_license_key && property_exists( $license, 'status' ) && 'error' === $license->status ) {
		$is_license_key = false;
	}

	// Check if current license is part of subscription or not.
	$subscriptions = get_option( 'give_subscriptions' );

	if ( $is_license_key && $subscriptions ) {
		foreach ( $subscriptions as $subscription ) {
			if ( in_array( $license_key, $subscription['licenses'] ) ) {
				$is_in_subscription = $subscription['id'];
				break;
			}
		}
	}

	if ( $is_license_key ) {

		if ( empty( $license->success ) && property_exists( $license, 'error' ) ) {

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			switch ( $license->error ) {
				case 'expired':
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank" title="Renew your license key">renew your license key</a>.', 'give' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
						$checkout_page_link . '?edd_license_key=' . $license_key . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
					);
					$license_status = 'license-' . $class;
					break;

				case 'missing':
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'give' ),
						$account_page_link . '?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
					);
					$license_status = 'license-' . $class;
					break;

				case 'invalid':
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'give' ),
						$addon_name,
						$account_page_link . '?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
					);
					$license_status = 'license-' . $class;
					break;

				case 'site_inactive':
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'give' ),
						$addon_name,
						$account_page_link . '?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
					);
					$license_status = 'license-' . $class;
					break;

				case 'item_name_mismatch':
					$class          = $license->error;
					$messages[]     = sprintf( __( 'This license %1$s does not belong to %2$s.', 'give' ), $license_key, $addon_name );
					$license_status = 'license-' . $class;
					break;

				case 'no_activations_left':
					$class          = $license->error;
					$messages[]     = sprintf( __( 'Your license key has reached it\'s activation limit. <a href="%s">View possible upgrades</a> now.', 'give' ), $account_page_link );
					$license_status = 'license-' . $class;
					break;

				default:
					$class          = $license->error;
					$messages[]     = sprintf(
						__( 'Your license is not activated. Please <a href="%3$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs. %2$sError Code: %1$s.', 'give' ),
						$license->error,
						'<br/>',
						"{$account_page_link}?utm_campaign=admin&utm_source=licenses&utm_medium={$license->error}"
					);
					$license_status = 'license-error';
					break;
			}
		} elseif ( $is_in_subscription ) {

			$subscription_expires = strtotime( $subscriptions[ $is_in_subscription ]['expires'] );
			$subscription_status  = __( 'renew', 'give' );

			if ( ( 'active' !== $subscriptions[ $is_in_subscription ]['status'] ) ) {
				$subscription_status = __( 'expire', 'give' );
			}

			if ( $subscription_expires < current_time( 'timestamp', 1 ) ) {
				$messages[]     = sprintf(
					__( 'Your subscription (<a href="%1$s" target="_blank">#%2$d</a>) expired. Please <a href="%3$s" target="_blank" title="Renew your license key">renew your license key</a>', 'give' ),
					urldecode( $subscriptions[ $is_in_subscription ]['invoice_url'] ),
					$subscriptions[ $is_in_subscription ]['payment_id'],
					$checkout_page_link . '?edd_license_key=' . $subscriptions[ $is_in_subscription ]['license_key'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
				);
				$license_status = 'license-expired';
			} elseif ( strtotime( '- 7 days', $subscription_expires ) < current_time( 'timestamp', 1 ) ) {
				$messages[]     = sprintf(
					__( 'Your subscription (<a href="%1$s" target="_blank">#%2$d</a>) will %3$s in %4$s.', 'give' ),
					urldecode( $subscriptions[ $is_in_subscription ]['invoice_url'] ),
					$subscriptions[ $is_in_subscription ]['payment_id'],
					$subscription_status,
					human_time_diff( current_time( 'timestamp', 1 ), strtotime( $subscriptions[ $is_in_subscription ]['expires'] ) )
				);
				$license_status = 'license-expires-soon';
			} else {
				$messages[]     = sprintf(
					__( 'Your subscription (<a href="%1$s" target="_blank">#%2$d</a>) will %3$s on %4$s.', 'give' ),
					urldecode( $subscriptions[ $is_in_subscription ]['invoice_url'] ),
					$subscriptions[ $is_in_subscription ]['payment_id'],
					$subscription_status,
					date_i18n( get_option( 'date_format' ), strtotime( $subscriptions[ $is_in_subscription ]['expires'], current_time( 'timestamp' ) ) )
				);
				$license_status = 'license-expiration-date';
			}
		} elseif ( empty( $license->success ) ) {
			$class          = 'invalid';
			$messages[]     = sprintf(
				__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'give' ),
				$addon_name,
				$account_page_link . '?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
			);
			$license_status = 'license-' . $class;

		} else {
			switch ( $license->license ) {
				case 'valid':
				default:
					$class      = 'valid';
					$now        = current_time( 'timestamp' );
					$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

					if ( 'lifetime' === $license->expires ) {
						$messages[]     = __( 'License key never expires.', 'give' );
						$license_status = 'license-lifetime-notice';
					} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {
						$messages[]     = sprintf(
							__( 'Your license key expires soon! It expires on %1$s. <a href="%2$s" target="_blank" title="Renew license">Renew your license key</a>.', 'give' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							$checkout_page_link . '?edd_license_key=' . $license_key . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
						);
						$license_status = 'license-expires-soon';
					} else {
						$messages[]     = sprintf(
							__( 'Your license key expires on %s.', 'give' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
						);
						$license_status = 'license-expiration-date';
					}
					break;
			}
		}
	} else {
		$messages[]     = sprintf(
			__( 'To receive updates, please enter your valid %s license key.', 'give' ),
			$addon_name
		);
		$license_status = 'inactive';
	}

	// Add class for input field if license is active.
	if ( $is_valid_license ) {
		$field_classes .= ' give-license-active';
	}

	// Get input field html.
	$input_field_html = "<input type=\"{$type}\" name=\"{$id}\" class=\"{$field_classes}\" value=\"{$license_key}\">";

	// If license is active so show deactivate button.
	if ( $is_valid_license ) {
		// Get input field html.
		$input_field_html = "<input type=\"{$type}\" name=\"{$id}\" class=\"{$field_classes}\" value=\"{$license_key}\" readonly=\"readonly\">";

		$custom_html = '<input type="submit" class="button button-small give-license-deactivate" name="' . $id . '_deactivate" value="' . esc_attr__( 'Deactivate License', 'give' ) . '"/>';

	}

	// Field description.
	$custom_html .= '<label for="give_settings[' . $id . ']"> ' . $field_description . '</label>';

	// If no messages found then inform user that to get updated in future register yourself.
	if ( empty( $messages ) ) {
		$messages[] = apply_filters( "{$shortname}_default_addon_notice", __( 'To receive updates, please enter your valid license key.', 'give' ) );
	}

	foreach ( $messages as $message ) {
		$custom_html .= '<div class="give-license-status-notice give-' . $license_status . '">';
		$custom_html .= '<p>' . $message . '</p>';
		$custom_html .= '</div>';
	}

	// Field html.
	$custom_html = apply_filters( 'give_license_key_field_html', $input_field_html . $custom_html, $field );

	// Nonce.
	wp_nonce_field( $id . '-nonce', $id . '-nonce' );

	// Print field html.
	echo "<div class=\"give-license-key\"><label for=\"{$id}\">{$addon_name }</label></div><div class=\"give-license-block\">{$custom_html}</div>";
}


/**
 * Display the API Keys
 *
 * @since       1.0
 * @return      void
 */
function give_api_callback() {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return;
	}

	/**
	 * Fires before displaying API keys.
	 *
	 * @since 1.0
	 */
	do_action( 'give_tools_api_keys_before' );

	require_once GIVE_PLUGIN_DIR . 'includes/admin/class-api-keys-table.php';

	$api_keys_table = new Give_API_Keys_Table();
	$api_keys_table->prepare_items();
	$api_keys_table->display();
	?>
	<span class="cmb2-metabox-description api-description">
		<?php
		echo sprintf(
		/* translators: 1: http://docs.givewp.com/api 2: http://docs.givewp.com/addon-zapier */
			__( 'You can create API keys for individual users within their profile edit screen. API keys allow users to use the <a href="%1$s" target="_blank">Give REST API</a> to retrieve donation data in JSON or XML for external applications or devices, such as <a href="%2$s" target="_blank">Zapier</a>.', 'give' ),
			esc_url( 'http://docs.givewp.com/api' ),
			esc_url( 'http://docs.givewp.com/addon-zapier' )
		);
		?>
	</span>
	<?php

	/**
	 * Fires after displaying API keys.
	 *
	 * @since 1.0
	 */
	do_action( 'give_tools_api_keys_after' );
}
