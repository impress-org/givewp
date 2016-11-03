<?php
/**
 * Admin View: System Info
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display system info in a table format.
 *
 * Copyright (c) 2015 WooThemes
 * Copyright (c) 2016 WordImpress, LLC
 *
 * The following code is a derivative work of the code from the WooCommerce
 * plugin, which is licensed GPLv3. This code therefore is also licensed under
 * the terms of the GNU Public License, version 3.
 */

global $wpdb;
?>

<table class="give-status-table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WordPress Environment"><h2><?php _e( 'WordPress Environment', 'give' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
	<tr>
		<td data-export-label="Home URL"><?php _e( 'Home URL', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The URL of your site\'s homepage.', 'give' ) ); ?></td>
		<td><?php form_option( 'home' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Site URL"><?php _e( 'Site URL', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The root URL of your site.', 'give' ) ); ?></td>
		<td><?php form_option( 'siteurl' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="WC Version"><?php _e( 'Give Version', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The version of Give installed on your site.', 'give' ) ); ?></td>
		<td><?php echo esc_html( GIVE_VERSION ); ?></td>
	</tr>
	<tr>
		<td data-export-label="WP Version"><?php _e( 'WP Version', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The version of WordPress installed on your site.', 'give' ) ); ?></td>
		<td><?php bloginfo('version'); ?></td>
	</tr>
	<tr>
		<td data-export-label="WP Multisite"><?php _e( 'WP Multisite', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'Whether or not you have WordPress Multisite enabled.', 'give' ) ); ?></td>
		<td><?php if ( is_multisite() ) echo '<span class="dashicons dashicons-yes"></span>'; else echo '&ndash;'; ?></td>
	</tr>
	<tr>
		<td data-export-label="WP Memory Limit"><?php _e( 'WP Memory Limit', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The maximum amount of memory (RAM) that your site can use at one time.', 'give' ) ); ?></td>
		<td><?php
			$memory = wc_let_to_num( WP_MEMORY_LIMIT );

			if ( function_exists( 'memory_get_usage' ) ) {
				$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) );
				$memory        = max( $memory, $system_memory );
			}

			if ( $memory < 67108864 ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend setting memory to at least 64MB. See: %s', 'give' ), size_format( $memory ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'give' ) . '</a>' ) . '</mark>';
			} else {
				echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
			}
			?></td>
	</tr>
	<tr>
		<td data-export-label="WP Debug Mode"><?php _e( 'WP Debug Mode', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'Displays whether or not WordPress is in Debug Mode.', 'give' ) ); ?></td>
		<td>
			<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
				<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
			<?php else : ?>
				<mark class="no">&ndash;</mark>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td data-export-label="WP Cron"><?php _e( 'WP Cron', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'Displays whether or not WP Cron Jobs are enabled.', 'give' ) ); ?></td>
		<td>
			<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
				<mark class="no">&ndash;</mark>
			<?php else : ?>
				<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Language"><?php _e( 'Language', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The current language used by WordPress. Default = English', 'give' ) ); ?></td>
		<td><?php echo get_locale(); ?></td>
	</tr>
	<tr>
		<td data-export-label="Permalink Structure"><?php _e( 'Permalink Structure', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The permalink structure as defined in Settings.', 'give' ) ); ?></td>
		<td><?php get_option( 'permalink_structure' ) ? form_option( 'permalink_structure' ) : _e( 'Default', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Show on Front"><?php _e( 'Show on Front', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'Whether your front page is set to show posts or a static page.', 'give' ) ); ?></td>
		<td><?php form_option( 'show_on_front' ) ?></td>
	</tr>
	<?php if ( 'page' === get_option( 'show_on_front' ) ) : ?>
		<?php
		$front_page_id = absint( get_option( 'page_on_front' ) );
		$blog_page_id  = absint( get_option( 'page_for_posts' ) );
		?>
		<tr>
			<td data-export-label="Page on Front"><?php _e( 'Page on Front', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The page set to display as your front page.', 'give' ) ); ?></td>
			<td><?php echo 0 !== $front_page_id ? esc_html( get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' ) : __( 'Unset', 'give' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Page for Posts"><?php _e( 'Page for Posts', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The page set to display your posts.', 'give' ) ); ?></td>
			<td><?php echo 0 !== $blog_page_id ? esc_html( get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' ) : __( 'Unset', 'give' ); ?></td>
		</tr>
	<?php endif;?>
</table>

<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Server Environment"><h2><?php _e( 'Server Environment', 'give' ); ?></h2></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td data-export-label="Server Info"><?php _e( 'Server Info', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'Information about the web server that is currently hosting your site.', 'give' ) ); ?></td>
		<td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
	</tr>
	<tr>
		<td data-export-label="PHP Version"><?php _e( 'PHP Version', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The version of PHP installed on your hosting server.', 'give' ) ); ?></td>
		<td><?php
			// Check if phpversion function exists.
			if ( function_exists( 'phpversion' ) ) {
				$php_version = phpversion();

				if ( version_compare( $php_version, '5.6', '<' ) ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum PHP version of 5.6. See: %s', 'give' ), esc_html( $php_version ), '<a href="https://givewp.com/documentation/core/settings/system-info/" target="_blank">' . __( 'PHP Requirements in Give', 'give' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . esc_html( $php_version ) . '</mark>';
				}
			} else {
				_e( "Couldn't determine PHP version because phpversion() doesn't exist.", 'give' );
			}
			?></td>
	</tr>
	<?php if ( function_exists( 'ini_get' ) ) : ?>
		<tr>
			<td data-export-label="PHP Post Max Size"><?php _e( 'PHP Post Max Size', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The largest filesize that can be contained in one post.', 'give' ) ); ?></td>
			<td><?php echo size_format( wc_let_to_num( ini_get( 'post_max_size' ) ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Time Limit"><?php _e( 'PHP Time Limit', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'give' ) ); ?></td>
			<td><?php echo ini_get( 'max_execution_time' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Max Input Vars"><?php _e( 'PHP Max Input Vars', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'give' ) ); ?></td>
			<td><?php echo ini_get( 'max_input_vars' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="cURL Version"><?php _e( 'cURL Version', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The version of cURL installed on your server.', 'give' ) ); ?></td>
			<td><?php
				if ( function_exists( 'curl_version' ) ) {
					$curl_version = curl_version();
					echo $curl_version['version'] . ', ' . $curl_version['ssl_version'];
				} else {
					_e( 'N/A', 'give' );
				}
				?></td>
		</tr>
		<tr>
			<td data-export-label="SUHOSIN Installed"><?php _e( 'SUHOSIN Installed', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'give' ) ); ?></td>
			<td><?php echo extension_loaded( 'suhosin' ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
		</tr>
	<?php endif;

	if ( $wpdb->use_mysqli ) {
		$ver = mysqli_get_server_info( $wpdb->dbh );
	} else {
		$ver = mysql_get_server_info();
	}

	if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) ) : ?>
		<tr>
			<td data-export-label="MySQL Version"><?php _e( 'MySQL Version', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The version of MySQL installed on your hosting server.', 'give' ) ); ?></td>
			<td>
				<?php
				$mysql_version = $wpdb->db_version();

				if ( version_compare( $mysql_version, '5.6', '<' ) ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum MySQL version of 5.6. See: %s', 'give' ), esc_html( $mysql_version ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress Requirements', 'give' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . esc_html( $mysql_version ) . '</mark>';
				}
				?>
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<td data-export-label="Max Upload Size"><?php _e( 'Max Upload Size', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The largest filesize that can be uploaded to your WordPress installation.', 'give' ) ); ?></td>
		<td><?php echo size_format( wp_max_upload_size() ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Default Timezone is UTC"><?php _e( 'Default Timezone is UTC', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The default timezone for your server.', 'give' ) ); ?></td>
		<td><?php
			$default_timezone = date_default_timezone_get();
			if ( 'UTC' !== $default_timezone ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'give' ), $default_timezone ) . '</mark>';
			} else {
				echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
			} ?>
		</td>
	</tr>
	<?php
	$posting = array();

	// fsockopen/cURL.
	$posting['fsockopen_curl']['name'] = 'fsockopen/cURL';
	$posting['fsockopen_curl']['help'] = wc_help_tip( __( 'Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'give' ) );

	if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) {
		$posting['fsockopen_curl']['success'] = true;
	} else {
		$posting['fsockopen_curl']['success'] = false;
		$posting['fsockopen_curl']['note']    = __( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'give' );
	}

	// SOAP.
	$posting['soap_client']['name'] = 'SoapClient';
	$posting['soap_client']['help'] = wc_help_tip( __( 'Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'give' ) );

	if ( class_exists( 'SoapClient' ) ) {
		$posting['soap_client']['success'] = true;
	} else {
		$posting['soap_client']['success'] = false;
		$posting['soap_client']['note']    = sprintf( __( 'Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'give' ), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>' );
	}

	// DOMDocument.
	$posting['dom_document']['name'] = 'DOMDocument';
	$posting['dom_document']['help'] = wc_help_tip( __( 'HTML/Multipart emails use DOMDocument to generate inline CSS in templates.', 'give' ) );

	if ( class_exists( 'DOMDocument' ) ) {
		$posting['dom_document']['success'] = true;
	} else {
		$posting['dom_document']['success'] = false;
		$posting['dom_document']['note']    = sprintf( __( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'give' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' );
	}

	// Multibyte String.
	$posting['mbstring']['name'] = 'Multibyte String';
	$posting['mbstring']['help'] = wc_help_tip( __( 'Multibyte String (mbstring) is used to convert character encoding, like for emails or converting characters to lowercase.', 'give' ) );

	if ( extension_loaded( 'mbstring' ) ) {
		$posting['mbstring']['success'] = true;
	} else {
		$posting['mbstring']['success'] = false;
		$posting['mbstring']['note']    = sprintf( __( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'give' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' );
	}

	// WP Remote Post Check.
	$posting['wp_remote_post']['name'] = __( 'Remote Post', 'give');
	$posting['wp_remote_post']['help'] = wc_help_tip( __( 'PayPal uses this method of communicating when sending back transaction information.', 'give' ) );

	$response = wp_safe_remote_post( 'https://www.paypal.com/cgi-bin/webscr', array(
		'timeout'     => 60,
		'user-agent'  => 'Give/' . GIVE_VERSION,
		'httpversion' => '1.1',
		'body'        => array(
			'cmd'     => '_notify-validate'
		)
	) );

	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$posting['wp_remote_post']['success'] = true;
	} else {
		$posting['wp_remote_post']['note']    = __( 'wp_remote_post() failed. PayPal IPN won\'t work with your server. Contact your hosting provider.', 'give' );
		if ( is_wp_error( $response ) ) {
			$posting['wp_remote_post']['note'] .= ' ' . sprintf( __( 'Error: %s', 'give' ), sanitize_text_field( $response->get_error_message() ) );
		} else {
			$posting['wp_remote_post']['note'] .= ' ' . sprintf( __( 'Status code: %s', 'give' ), sanitize_text_field( $response['response']['code'] ) );
		}
		$posting['wp_remote_post']['success'] = false;
	}

	// WP Remote Get Check.
	$posting['wp_remote_get']['name'] = __( 'Remote Get', 'give');
	$posting['wp_remote_get']['help'] = wc_help_tip( __( 'Give plugins may use this method of communication when checking for plugin updates.', 'give' ) );

	$response = wp_safe_remote_get( 'https://woocommerce.com/wc-api/product-key-api?request=ping&network=' . ( is_multisite() ? '1' : '0' ) );

	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$posting['wp_remote_get']['success'] = true;
	} else {
		$posting['wp_remote_get']['note']    = __( 'wp_remote_get() failed. The WooCommerce plugin updater won\'t work with your server. Contact your hosting provider.', 'give' );
		if ( is_wp_error( $response ) ) {
			$posting['wp_remote_get']['note'] .= ' ' . sprintf( __( 'Error: %s', 'give' ), wc_clean( $response->get_error_message() ) );
		} else {
			$posting['wp_remote_get']['note'] .= ' ' . sprintf( __( 'Status code: %s', 'give' ), wc_clean( $response['response']['code'] ) );
		}
		$posting['wp_remote_get']['success'] = false;
	}

	$posting = apply_filters( 'woocommerce_debug_posting', $posting );

	foreach ( $posting as $post ) {
		$mark = ! empty( $post['success'] ) ? 'yes' : 'error';
		?>
		<tr>
			<td data-export-label="<?php echo esc_html( $post['name'] ); ?>"><?php echo esc_html( $post['name'] ); ?>:</td>
			<td class="help"><?php echo isset( $post['help'] ) ? $post['help'] : ''; ?></td>
			<td>
				<mark class="<?php echo $mark; ?>">
					<?php echo ! empty( $post['success'] ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no-alt"></span>'; ?> <?php echo ! empty( $post['note'] ) ? wp_kses_data( $post['note'] ) : ''; ?>
				</mark>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Active Plugins (<?php echo count( (array) get_option( 'active_plugins' ) ); ?>)"><h2><?php _e( 'Active Plugins', 'give' ); ?> (<?php echo count( (array) get_option( 'active_plugins' ) ); ?>)</h2></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
		$active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
	}

	foreach ( $active_plugins as $plugin ) {

		$plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		$dirname        = dirname( $plugin );
		$version_string = '';
		$network_string = '';

		if ( ! empty( $plugin_data['Name'] ) ) {

			// Link the plugin name to the plugin url if available.
			$plugin_name = esc_html( $plugin_data['Name'] );

			if ( ! empty( $plugin_data['PluginURI'] ) ) {
				$plugin_name = '<a href="' . esc_url( $plugin_data['PluginURI'] ) . '" title="' . esc_attr__( 'Visit plugin homepage' , 'give' ) . '" target="_blank">' . $plugin_name . '</a>';
			}

			if ( strstr( $dirname, 'woocommerce-' ) && strstr( $plugin_data['PluginURI'], 'woothemes.com' ) ) {

				if ( false === ( $version_data = get_transient( md5( $plugin ) . '_version_data' ) ) ) {
					$changelog = wp_safe_remote_get( 'http://dzv365zjfbd8v.cloudfront.net/changelogs/' . $dirname . '/changelog.txt' );
					$cl_lines  = explode( "\n", wp_remote_retrieve_body( $changelog ) );
					if ( ! empty( $cl_lines ) ) {
						foreach ( $cl_lines as $line_num => $cl_line ) {
							if ( preg_match( '/^[0-9]/', $cl_line ) ) {

								$date         = str_replace( '.' , '-' , trim( substr( $cl_line , 0 , strpos( $cl_line , '-' ) ) ) );
								$version      = preg_replace( '~[^0-9,.]~' , '' ,stristr( $cl_line , "version" ) );
								$update       = trim( str_replace( "*" , "" , $cl_lines[ $line_num + 1 ] ) );
								$version_data = array( 'date' => $date , 'version' => $version , 'update' => $update , 'changelog' => $changelog );
								set_transient( md5( $plugin ) . '_version_data', $version_data, DAY_IN_SECONDS );
								break;
							}
						}
					}
				}

				if ( ! empty( $version_data['version'] ) && version_compare( $version_data['version'], $plugin_data['Version'], '>' ) ) {
					$version_string = ' &ndash; <strong style="color:red;">' . esc_html( sprintf( _x( '%s is available', 'Version info', 'give' ), $version_data['version'] ) ) . '</strong>';
				}

				if ( $plugin_data['Network'] != false ) {
					$network_string = ' &ndash; <strong style="color:black;">' . __( 'Network enabled', 'give' ) . '</strong>';
				}
			}

			?>
			<tr>
				<td><?php echo $plugin_name; ?></td>
				<td class="help">&nbsp;</td>
				<td><?php echo sprintf( _x( 'by %s', 'by author', 'give' ), $plugin_data['Author'] ) . ' &ndash; ' . esc_html( $plugin_data['Version'] ) . $version_string . $network_string; ?></td>
			</tr>
			<?php
		}
	}
	?>
	</tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Theme"><h2><?php _e( 'Theme', 'give' ); ?></h2></th>
	</tr>
	</thead>
	<?php
	include_once( ABSPATH . 'wp-admin/includes/theme-install.php' );

	$active_theme         = wp_get_theme();
	$theme_version        = $active_theme->Version;
	$update_theme_version = WC_Admin_Status::get_latest_theme_version( $active_theme );
	?>
	<tbody>
	<tr>
		<td data-export-label="Name"><?php _e( 'Name', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The name of the current active theme.', 'give' ) ); ?></td>
		<td><?php echo esc_html( $active_theme->Name ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Version"><?php _e( 'Version', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The installed version of the current active theme.', 'give' ) ); ?></td>
		<td><?php
			echo esc_html( $theme_version );

			if ( version_compare( $theme_version, $update_theme_version, '<' ) ) {
				echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'give' ), esc_html( $update_theme_version ) ) . '</strong>';
			}
			?></td>
	</tr>
	<tr>
		<td data-export-label="Author URL"><?php _e( 'Author URL', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'The theme developers URL.', 'give' ) ); ?></td>
		<td><?php echo $active_theme->{'Author URI'}; ?></td>
	</tr>
	<tr>
		<td data-export-label="Child Theme"><?php _e( 'Child Theme', 'give' ); ?>:</td>
		<td class="help"><?php echo wc_help_tip( __( 'Displays whether or not the current theme is a child theme.', 'give' ) ); ?></td>
		<td><?php
			echo is_child_theme() ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<span class="dashicons dashicons-no-alt"></span> &ndash; ' . sprintf( __( 'If you\'re modifying Give on a parent theme you didn\'t build personally, then we recommend using a child theme. See: <a href="%s" target="_blank">How to Create a Child Theme</a>', 'give' ), 'https://codex.wordpress.org/Child_Themes' );
			?></td>
	</tr>
	<?php
	if( is_child_theme() ) :
		$parent_theme         = wp_get_theme( $active_theme->Template );
		$update_theme_version = WC_Admin_Status::get_latest_theme_version( $parent_theme );
		?>
		<tr>
			<td data-export-label="Parent Theme Name"><?php _e( 'Parent Theme Name', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The name of the parent theme.', 'give' ) ); ?></td>
			<td><?php echo esc_html( $parent_theme->Name ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Version"><?php _e( 'Parent Theme Version', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The installed version of the parent theme.', 'give' ) ); ?></td>
			<td><?php
				echo esc_html( $parent_theme->Version );

				if ( version_compare( $parent_theme->Version, $update_theme_version, '<' ) ) {
					echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'give' ), esc_html( $update_theme_version ) ) . '</strong>';
				}
				?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Author URL"><?php _e( 'Parent Theme Author URL', 'give' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( __( 'The parent theme developers URL.', 'give' ) ); ?></td>
			<td><?php echo $parent_theme->{'Author URI'}; ?></td>
		</tr>
	<?php endif ?>
	</tbody>
</table>