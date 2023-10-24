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

	$post_options = [ '' => '' ]; // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'give-settings' != $_GET['page'] ) && ! $force ) {
		return $post_options;
	}

	$args = wp_parse_args(
		$query_args,
		[
			'post_type'   => 'page',
			'numberposts' => 10,
		]
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

	$sizes            = [];
	$get_sizes        = get_intermediate_image_sizes();
	$core_image_sizes = [ 'thumbnail', 'medium', 'medium_large', 'large' ];

	// This will help us to filter special characters from a string
	$filter_slug_items = [ '_', '-' ];

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
function give_slug_to_title( $string, $filters = [] ) {

	foreach ( $filters as $filter_item ) {
		$string = str_replace( $filter_item, ' ', $string );
	}

	// Return updated string after converting it to title case
	return ucwords( $string );

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
	<span class="give-metabox-description api-description">
		<?php
		echo sprintf(
			/* translators: 1: http://docs.givewp.com/api 2: http://docs.givewp.com/addon-zapier */
			__( 'You can create API keys for individual users within their profile edit screen. API keys allow users to use the <a href="%1$s" target="_blank">GiveWP REST API</a> to retrieve donation data in JSON or XML for external applications or devices, such as <a href="%2$s" target="_blank">Zapier</a>.', 'give' ),
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


/**
 * Hide char in string
 *
 * @param string $str
 * @param int    $show_char_count
 * @param string $replace
 *
 * @return string
 * @since 2.5.0
 */
function give_hide_char( $str, $show_char_count, $replace = '*' ) {
	return str_repeat(
		$replace,
		strlen( $str ) - $show_char_count
	) . substr(
		$str,
		- $show_char_count,
		$show_char_count
	);
}


/**
 *  Format marKdown formatted string.
 *
 * @param string $readme Markdown format string
 *
 * @return string
 * @since 2.5.0
 */
function give_get_format_md( $readme ) {
	$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
	$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
	$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
	$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
	$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );

	return $readme;
}

/**
 * Add-ons Render Feed
 *
 * Renders the add-ons page feed.
 *
 * @param string $feed_type
 * @param bool   $echo
 *
 * @return string
 * @since 1.0
 */
function give_add_ons_feed( $feed_type = '', $echo = true ) {

	$addons_debug = false; // set to true to debug. NEVER LEAVE TRUE IN PRODUCTION.
	$cache_key    = $feed_type ? "give_add_ons_feed_{$feed_type}" : 'give_add_ons_feed';
	$cache        = Give_Cache::get( $cache_key, true );
	$feed_url     = Give_License::get_website_url() . 'downloads/feed/';

	if ( false === $cache || ( true === $addons_debug && true === WP_DEBUG ) ) {
		switch ( $feed_type ) {
			case 'price-bundle':
				$feed_url = Give_License::get_website_url() . 'downloads/feed/addons-price-bundles.php';
				break;
			case 'addons-directory':
				$feed_url = Give_License::get_website_url() . 'downloads/feed/index.php';
				break;
		}

		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$feed = vip_safe_wp_remote_get( $feed_url, false, 3, 1, 20, [ 'sslverify' => false ] );
		} else {
			$feed = wp_remote_get( $feed_url, [ 'sslverify' => false ] );
		}

		if ( ! is_wp_error( $feed ) ) {
			if ( ! empty( $feed['body'] ) ) {
				$cache = wp_remote_retrieve_body( $feed );
				Give_Cache::set( $cache_key, $cache, DAY_IN_SECONDS, true );
			}
		} else {
			$cache = sprintf(
				'<div class="error inline"><p>%s</p></div>',
				esc_html__( 'There was an error retrieving the GiveWP add-ons list from the server. Please try again.', 'give' )
			);
		}
	}

	$cache = wp_kses_post( $cache );

	if ( $echo ) {
		echo $cache;
	}

	return $cache;
}

/**
 * Handle installation and connection for SendWP via ajax
 *
 * @since 2.33.4 added nonce check
 * @since 2.9.15
 */
function give_sendwp_remote_install_handler () {

  check_ajax_referer( 'give_sendwp_remote_install');

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'give' )
		) );
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if( ! array_key_exists( 'sendwp/sendwp.php', $plugins ) ) {

		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api( 'plugin_information', array(
			'slug' => 'sendwp',
		) );

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( array(
				'error' => $api->get_error_message(),
				'debug' => $api
			) );
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install = $upgrader->install( $api->download_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error( array(
				'error' => $install->get_error_message(),
				'debug' => $api
			) );
		}

		$activated = activate_plugin( $upgrader->plugin_info() );

	} else {

		$activated = activate_plugin( 'sendwp/sendwp.php' );

	}

	/*
	* Final check to see if SendWP is available.
	*/
	if( ! function_exists('sendwp_get_server_url') ) {
		wp_send_json_error( array(
			'error' => __( 'Something went wrong. SendWP was not installed correctly.', 'give' )
		) );
	}

	wp_send_json_success( array(
		'partner_id'      => 51154,
		'register_url'    => sendwp_get_server_url() . '_/signup',
		'client_name'     => sendwp_get_client_name(),
        'client_url'      => sendwp_get_client_url(),
		'client_secret'   => sendwp_get_client_secret(),
		'client_redirect' => admin_url( '/edit.php?post_type=give_forms&page=give-settings&tab=emails&section=email-settings' ),
	) );
}
add_action( 'wp_ajax_give_sendwp_remote_install', 'give_sendwp_remote_install_handler' );

/**
 * Handle deactivation of SendWP via ajax
 *
 * @since 2.33.4 add nonce check
 * @since 2.9.15
 */
function give_sendwp_disconnect () {

  check_ajax_referer( 'give_sendwp_disconnect');

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'give' )
		) );
	}

	sendwp_disconnect_client();

	deactivate_plugins( 'sendwp/sendwp.php' );

	wp_send_json_success();
}
add_action( 'wp_ajax_give_sendwp_disconnect', 'give_sendwp_disconnect' );
