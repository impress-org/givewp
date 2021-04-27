<?php
/**
 * Admin View: System Info
 */

use Give\Framework\Migrations\MigrationsRunner;
use Give\Helpers\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The following code is a derivative work of the code from the WooCommerce
 * plugin, which is licensed GPLv3. This code therefore is also licensed under
 * the terms of the GNU Public License, version 3.
 *
 * Copyright (c) 2015 WooThemes
 * Copyright (c) 2016 GiveWP
 */

global $wpdb;
$give_options = give_get_settings();
$plugins      = give_get_plugins();
$give_add_ons = give_get_plugins( [ 'only_add_on' => true ] );

$give_plugin_authors = [ 'WordImpress', 'GiveWP' ];

/* @var  Give_Updates $give_updates */
$give_updates = Give_Updates::get_instance();
?>

<div class="give-debug-report-wrapper">
	<p class="give-debug-report-text"><?php echo sprintf( __( 'Please copy and paste this information in your ticket when contacting support:', 'give' ) ); ?> </p>
	<div class="give-debug-report-actions">
		<a class="button-primary js-give-debug-report-button" href="#"><?php _e( 'Get System Report', 'give' ); ?></a>
		<a class="button-secondary docs" href="http://docs.givewp.com/settings-system-info"
		   target="_blank"><?php _e( 'Understanding the System Report', 'give' ); ?> <span
				class="dashicons dashicons-external"></span></a>
	</div>
	<div class="give-debug-report js-give-debug-report">
		<textarea readonly="readonly"></textarea>
	</div>
</div>

<table class="give-status-table widefat" cellspacing="0" id="status">
	<thead>
	<tr>
		<th colspan="3" data-export-label="WordPress Environment">
			<h2><?php _e( 'WordPress Environment', 'give' ); ?></h2></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td data-export-label="Home URL"><?php _e( 'Home URL', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The URL of your site\'s homepage.', 'give' ) ); ?></td>
		<td><?php form_option( 'home' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Site URL"><?php _e( 'Site URL', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The root URL of your site.', 'give' ) ); ?></td>
		<td><?php form_option( 'siteurl' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="WP Version"><?php _e( 'WP Version', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The version of WordPress installed on your site.', 'give' ) ); ?></td>
		<td><?php bloginfo( 'version' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="WP Multisite"><?php _e( 'WP Multisite', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether or not you have WordPress Multisite enabled.', 'give' ) ); ?></td>
		<td>
			<?php
			if ( is_multisite() ) {
				echo '<span class="dashicons dashicons-yes"></span>';
			} else {
				echo '&ndash;';
			}
			?>
		</td>

	</tr>
	<tr>
		<td data-export-label="WP Memory Limit"><?php _e( 'WP Memory Limit', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The maximum amount of memory (RAM) that your site can use at one time.', 'give' ) ); ?></td>
		<td>
			<?php
			$memory = give_let_to_num( WP_MEMORY_LIMIT );

			if ( function_exists( 'memory_get_usage' ) ) {
				$system_memory = give_let_to_num( @ini_get( 'memory_limit' ) );
				$memory        = max( $memory, $system_memory );
			}

			if ( $memory < 67108864 ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend setting memory to at least 64 MB. See: %2$s', 'give' ), size_format( $memory ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'give' ) . '</a>' ) . '</mark>';
			} else {
				echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
			}
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="WP Debug Mode"><?php _e( 'WP Debug Mode', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Displays whether or not WordPress is in Debug Mode.', 'give' ) ); ?></td>
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
		<td class="help"><?php echo Give()->tooltips->render( __( 'Displays whether or not WP Cron Jobs are enabled.', 'give' ) ); ?></td>
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
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The current language used by WordPress. Default = English', 'give' ) ); ?></td>
		<td><?php echo get_locale(); ?></td>
	</tr>
	<tr>
		<td data-export-label="Permalink Structure"><?php _e( 'Permalink Structure', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The permalink structure as defined in Settings.', 'give' ) ); ?></td>
		<td><?php echo esc_html( get_option( 'permalink_structure', __( 'Default', 'give' ) ) ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Show on Front"><?php _e( 'Show on Front', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether your front page is set to show posts or a static page.', 'give' ) ); ?></td>
		<td><?php echo esc_html( get_option( 'show_on_front', '&ndash;' ) ); ?></td>
	</tr>
	<?php if ( 'page' === get_option( 'show_on_front' ) ) : ?>
		<?php
		$front_page_id = absint( get_option( 'page_on_front' ) );
		$blog_page_id  = absint( get_option( 'page_for_posts' ) );
		?>
		<tr>
			<td data-export-label="Page on Front"><?php _e( 'Page on Front', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The page set to display as your front page.', 'give' ) ); ?></td>
			<td><?php echo 0 !== $front_page_id ? esc_html( get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' ) : __( 'Unset', 'give' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Page for Posts"><?php _e( 'Page for Posts', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The page set to display your posts.', 'give' ) ); ?></td>
			<td><?php echo 0 !== $blog_page_id ? esc_html( get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' ) : __( 'Unset', 'give' ); ?></td>
		</tr>
	<?php endif; ?>
	<tr>
		<td data-export-label="Table Prefix Length"><?php _e( 'Table Prefix', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The table prefix used in your WordPress database.', 'give' ) ); ?></td>
		<td><?php echo esc_html( $wpdb->prefix ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Table Prefix Length"><?php _e( 'Table Prefix Length', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The length of the table prefix used in your WordPress database.', 'give' ) ); ?></td>
		<td><?php echo esc_html( strlen( $wpdb->prefix ) ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Table Prefix Status"><?php _e( 'Table Prefix Status', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The status of the table prefix used in your WordPress database.', 'give' ) ); ?></td>
		<td><?php echo strlen( $wpdb->prefix ) > 16 ? esc_html( 'Error: Too long', 'give' ) : esc_html( 'Acceptable', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Admin AJAX"><?php _e( 'Admin AJAX', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether Admin AJAX is accessible.', 'give' ) ); ?></td>
		<td><?php echo give_test_ajax_works( true ) ? __( 'Accessible', 'give' ) : __( 'Inaccessible', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Registered Post Statuses"><?php _e( 'Registered Post Statuses', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'A list of all registered post statuses.', 'give' ) ); ?></td>
		<td><?php echo esc_html( implode( ', ', get_post_stati() ) ); ?></td>
	</tr>
	</tbody>
</table>

<table class="give-status-table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Server Environment"><h2><?php _e( 'Server Environment', 'give' ); ?></h2>
		</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td data-export-label="Hosting Provider"><?php _e( 'Hosting Provider', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The hosting provider for this WordPress installation.', 'give' ) ); ?></td>
		<td><?php echo give_get_host() ? esc_html( give_get_host() ) : __( 'Unknown', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="TLS Connection"><?php _e( 'TLS Connection', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Most payment gateway APIs only support connections using the TLS 1.2 security protocol.', 'give' ) ); ?></td>
		<td>
			<?php
			$tls_check = false;

			// Get the SSL status.
			if ( ini_get( 'allow_url_fopen' ) ) {
				$tls_check = wp_remote_get( 'https://www.howsmyssl.com/a/check' );
			}

			if ( ! is_wp_error( $tls_check ) ) {
				$tls_check = json_decode( wp_remote_retrieve_body( $tls_check ), false );

				/* translators: %s: SSL connection response */
				printf( __( 'Connection uses %s', 'give' ), esc_html( $tls_check->tls_version ) );
			}
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="TLS Connection"><?php _e( 'TLS Rating', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The server\'s connection as rated by https://www.howsmyssl.com/', 'give' ) ); ?></td>
		<td>
			<?php
			if ( ! is_wp_error( $tls_check ) ) {
				esc_html_e( property_exists( $tls_check, 'rating' ) ? $tls_check->rating : $tls_check->tls_version, 'give' );
			}
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Server Info"><?php _e( 'Server Info', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Information about the web server that is currently hosting your site.', 'give' ) ); ?></td>
		<td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
	</tr>
	<tr>
		<td data-export-label="PHP Version"><?php _e( 'PHP Version', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The version of PHP installed on your hosting server.', 'give' ) ); ?></td>
		<td>
			<?php
			// Check if phpversion function exists.
			if ( function_exists( 'phpversion' ) ) {
				$php_version = phpversion();

				if ( version_compare( $php_version, '5.6', '<' ) ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum PHP version of 5.6. See: %2$s', 'give' ), esc_html( $php_version ), '<a href="http://docs.givewp.com/settings-system-info" target="_blank">' . __( 'PHP Requirements in Give', 'give' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . esc_html( $php_version ) . '</mark>';
				}
			} else {
				_e( "Couldn't determine PHP version because phpversion() doesn't exist.", 'give' );
			}
			?>
		</td>
	</tr>
	<?php if ( function_exists( 'ini_get' ) ) : ?>
		<tr>
			<td data-export-label="PHP Post Max Size"><?php _e( 'PHP Post Max Size', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The largest filesize that can be contained in one post.', 'give' ) ); ?></td>
			<td><?php echo size_format( give_let_to_num( ini_get( 'post_max_size' ) ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Time Limit"><?php _e( 'PHP Time Limit', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups).', 'give' ) ); ?></td>
			<td><?php echo ini_get( 'max_execution_time' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Max Input Vars"><?php _e( 'PHP Max Input Vars', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'give' ) ); ?></td>
			<td><?php echo ini_get( 'max_input_vars' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Max Upload Size"><?php _e( 'PHP Max Upload Size', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The largest filesize that can be uploaded to your WordPress installation.', 'give' ) ); ?></td>
			<td><?php echo size_format( wp_max_upload_size() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="cURL Version"><?php _e( 'cURL Version', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The version of cURL installed on your server.', 'give' ) ); ?></td>
			<td>
				<?php
				if ( function_exists( 'curl_version' ) ) {
					$curl_version = curl_version();

					if ( version_compare( $curl_version['version'], '7.40', '<' ) ) {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum cURL version of 7.40.', 'give' ), esc_html( $curl_version['version'] . ', ' . $curl_version['ssl_version'] ) ) . '</mark>';
					} else {
						echo '<mark class="yes">' . esc_html( $curl_version['version'] . ', ' . $curl_version['ssl_version'] ) . '</mark>';
					}
				} else {
					echo '&ndash';
				}
				?>
			</td>
		</tr>
		<tr>
			<td data-export-label="SUHOSIN Installed"><?php _e( 'SUHOSIN Installed', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'give' ) ); ?></td>
			<td><?php echo extension_loaded( 'suhosin' ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
		</tr>
		<?php
	endif;

	if ( $wpdb->use_mysqli ) {
		$ver = mysqli_get_server_info( $wpdb->dbh );
	} else {
		$ver = mysql_get_server_info();
	}

	if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) ) :
		?>
		<tr>
			<td data-export-label="MySQL Version"><?php _e( 'MySQL Version', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The version of MySQL installed on your hosting server.', 'give' ) ); ?></td>
			<td>
				<?php
				$mysql_version = $wpdb->db_version();

				if ( version_compare( $mysql_version, '5.6', '<' ) ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'give' ), esc_html( $mysql_version ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress Requirements', 'give' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . esc_html( $mysql_version ) . '</mark>';
				}
				?>
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<td data-export-label="Default Timezone is UTC"><?php _e( 'Default Timezone is UTC', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The default timezone for your server.', 'give' ) ); ?></td>
		<td>
			<?php
			$default_timezone = date_default_timezone_get();
			if ( 'UTC' !== $default_timezone ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'give' ), $default_timezone ) . '</mark>';
			} else {
				echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
			}
			?>
		</td>
	</tr>
	<?php
	$posting = [];

	// fsockopen/cURL.
	$posting['fsockopen_curl']['name'] = 'fsockopen/cURL';
	$posting['fsockopen_curl']['help'] = __( 'Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'give' );

	if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) {
		$posting['fsockopen_curl']['success'] = true;
	} else {
		$posting['fsockopen_curl']['success'] = false;
		$posting['fsockopen_curl']['note']    = __( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'give' );
	}

	// SOAP.
	$posting['soap_client']['name'] = 'SoapClient';
	$posting['soap_client']['help'] = __( 'Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'give' );

	if ( class_exists( 'SoapClient' ) ) {
		$posting['soap_client']['success'] = true;
	} else {
		$posting['soap_client']['success'] = false;
		$posting['soap_client']['note']    = sprintf( __( 'Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'give' ), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>' );
	}

	// DOMDocument.
	$posting['dom_document']['name'] = 'DOMDocument';
	$posting['dom_document']['help'] = __( 'HTML/Multipart emails use DOMDocument to generate inline CSS in templates.', 'give' );

	if ( class_exists( 'DOMDocument' ) ) {
		$posting['dom_document']['success'] = true;
	} else {
		$posting['dom_document']['success'] = false;
		$posting['dom_document']['note']    = sprintf( __( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'give' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' );
	}

	// gzip.
	$posting['gzip']['name'] = 'gzip';
	$posting['gzip']['help'] = __( 'gzip is used for file compression and decompression.', 'give' );

	if ( is_callable( 'gzopen' ) ) {
		$posting['gzip']['success'] = true;
	} else {
		$posting['gzip']['success'] = false;
		$posting['gzip']['note']    = sprintf( __( 'Your server does not support the %s function - this is used for file compression and decompression.', 'give' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' );
	}


	// GD Graphics Library.
	$posting['gd']['name']    = 'GD Graphics Library';
	$posting['gd']['help']    = __( 'GD Graphics Library is used for dynamically manipulating images.', 'give' );
	$posting['gd']['success'] = extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ? true : false;

	// Multibyte String.
	$posting['mbstring']['name'] = 'Multibyte String';
	$posting['mbstring']['help'] = __( 'Multibyte String (mbstring) is used to convert character encoding, like for emails or converting characters to lowercase.', 'give' );

	if ( extension_loaded( 'mbstring' ) ) {
		$posting['mbstring']['success'] = true;
	} else {
		$posting['mbstring']['success'] = false;
		$posting['mbstring']['note']    = sprintf( __( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'give' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' );
	}

	// WP Remote Post Check.
	$posting['wp_remote_post']['name'] = __( 'Remote Post', 'give' );
	$posting['wp_remote_post']['help'] = __( 'PayPal uses this method of communicating when sending back transaction information.', 'give' );

	$response = wp_safe_remote_post(
		'https://www.paypal.com/cgi-bin/webscr',
		[
			'timeout'     => 60,
			'user-agent'  => 'Give/' . GIVE_VERSION,
			'httpversion' => '1.1',
			'body'        => [
				'cmd' => '_notify-validate',
			],
		]
	);

	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$posting['wp_remote_post']['success'] = true;
	} else {
		$posting['wp_remote_post']['note'] = __( 'wp_remote_post() failed. PayPal IPN won\'t work with your server. Contact your hosting provider.', 'give' );
		if ( is_wp_error( $response ) ) {
			$posting['wp_remote_post']['note'] .= ' ' . sprintf( __( 'Error: %s', 'give' ), sanitize_text_field( $response->get_error_message() ) );
		} else {
			$posting['wp_remote_post']['note'] .= ' ' . sprintf( __( 'Status code: %s', 'give' ), sanitize_text_field( $response['response']['code'] ) );
		}
		$posting['wp_remote_post']['success'] = false;
	}

	// WP Remote Get Check.
	$posting['wp_remote_get']['name'] = __( 'Remote Get', 'give' );
	$posting['wp_remote_get']['help'] = __( 'GiveWP plugins may use this method of communication when checking for plugin updates.', 'give' );

	$response = wp_safe_remote_get( 'https://woocommerce.com/wc-api/product-key-api?request=ping&network=' . ( is_multisite() ? '1' : '0' ) );

	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$posting['wp_remote_get']['success'] = true;
	} else {
		$posting['wp_remote_get']['note'] = __( 'wp_remote_get() failed. The GiveWP plugin updater won\'t work with your server. Contact your hosting provider.', 'give' );
		if ( is_wp_error( $response ) ) {
			$posting['wp_remote_get']['note'] .= ' ' . sprintf( __( 'Error: %s', 'give' ), give_clean( $response->get_error_message() ) );
		} else {
			$posting['wp_remote_get']['note'] .= ' ' . sprintf( __( 'Status code: %s', 'give' ), give_clean( $response['response']['code'] ) );
		}
		$posting['wp_remote_get']['success'] = false;
	}

	$posting = apply_filters( 'give_debug_posting', $posting );

	foreach ( $posting as $post ) {
		$mark = ! empty( $post['success'] ) ? 'yes' : 'error';
		?>
		<tr>
			<td data-export-label="<?php echo esc_html( $post['name'] ); ?>"><?php echo esc_html( $post['name'] ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( esc_attr( isset( $post['help'] ) ? $post['help'] : '' ) ); ?></td>
			<td>
				<mark class="<?php echo $mark; ?>">
					<?php echo ! empty( $post['success'] ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no-alt"></span>'; ?><?php echo ! empty( $post['note'] ) ? wp_kses_data( $post['note'] ) : ''; ?>
				</mark>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>

<table class="give-status-table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="GiveWP Configuration"><h2><?php _e( 'GiveWP Configuration', 'give' ); ?></h2>
		</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td data-export-label="GiveWP Version"><?php _e( 'GiveWP Version', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The version of GiveWP installed on your site.', 'give' ) ); ?></td>
		<td><?php echo esc_html( get_option( 'give_version' ) ); ?></td>
	</tr>
	<tr>
		<td data-export-label="GiveWP Cache"><?php _e( 'GiveWP Cache', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether cache is enabled in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo give_is_setting_enabled( give_get_option( 'cache', 'enabled' ) ) ? __( 'Enabled', 'give' ) : __( 'Disabled', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Database Updates"><?php _e( 'Database Updates', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'This will show the number of pending database updates.', 'give' ) ); ?></td>
		<td>
			<?php
			$updates_text    = __( 'All DB Updates Completed.', 'give' );
			$pending_updates = $give_updates->get_total_new_db_update_count();
			$total_updates   = $give_updates->get_total_db_update_count();

			if ( Give_Updates::$background_updater->is_paused_process() ) {
				// When all the db updates are pending.
				$updates_text = sprintf(
					__( '%1$s updates still need to run. (Paused) ', 'give' ),
					count( $give_updates->get_updates( 'database', 'new' ) )
				);
			} elseif ( $pending_updates === $total_updates ) {

				// When all the db updates are pending.
				$updates_text = sprintf(
					__( '%1$s updates still need to run.', 'give' ),
					$total_updates
				);
			} elseif ( $pending_updates > 0 ) {

				// When some of the db updates are completed and some are pending.
				$updates_text = sprintf(
					__( '%1$s of %2$s updates still need to run.', 'give' ),
					$pending_updates,
					$total_updates
				);
			}

			echo $updates_text;
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Database Updates"><?php _e( 'Database Migrations', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'This will inform you whether database migration completed or not.', 'give' ) ); ?></td>
		<td>
			<?php
			/* @var MigrationsRunner $migrationRunner */
			$migrationRunner = give( MigrationsRunner::class );

			echo $migrationRunner->hasMigrationToRun() ?
				esc_html__( 'Few Database Migrations still need to run.', 'give' ) :
				esc_html__( 'All Database Migrations Completed.', 'give' );
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Database Tables"><?php _e( 'Database Tables', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'This will show list of installed database tables.', 'give' ) ); ?></td>
		<td>
			<?php
			$db_table_list = '';

			/* @var  Give_DB $table */
			foreach ( __give_get_tables() as $table ) {
				$db_table_list .= sprintf(
					'<li><mark class="%1$s"><span class="dashicons dashicons-%2$s"></mark> %3$s</li>',
					$table->installed()
						? 'yes'
						: 'error',
					$table->installed()
						? 'yes'
						: 'no-alt',
					$table->table_name
				);
			}

			$isRevenueTableExist = Table::tableExists( Table::prefixTableName( 'give_revenue' ) );
			$db_table_list      .= sprintf(
				'<li><mark class="%1$s"><span class="dashicons dashicons-%2$s"></mark> %3$s</li>',
				$isRevenueTableExist ? 'yes' : 'error',
				$isRevenueTableExist ? 'yes' : 'no-alt',
				Table::prefixTableName( 'give_revenue' )
			);

			echo "<ul>{$db_table_list}</ul>";
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="GiveWP Cache"><?php _e( 'GiveWP Cache', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether cache is enabled in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo give_is_setting_enabled( give_get_option( 'cache', 'enabled' ) ) ? __( 'Enabled', 'give' ) : __( 'Disabled', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="GiveWP Cache"><?php _e( 'GiveWP Emails', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether emails is enabled in GiveWP settings.', 'give' ) ); ?></td>
		<td>
			<?php
			/* @var Give_Email_Notification $email_notification */
			if ( $email_notifications = Give_Email_Notifications::get_instance()->get_email_notifications() ) {
				ob_start();

				foreach ( Give_Email_Notifications::get_instance()->get_email_notifications() as $email_notification ) {
					$status = Give_Email_Notification_Util::is_email_notification_active( $email_notification ) ?
						'yes' :
						'error';

					echo sprintf(
						'<li><mark class="%1$s"><span class="dashicons dashicons-%2$s"></mark></span>%3$s</li>',
						Give_Email_Notification_Util::is_email_notification_active( $email_notification ) ? 'yes' : 'error',
						Give_Email_Notification_Util::is_email_notification_active( $email_notification ) ? 'yes' : 'no-alt',
						$email_notification->config['label']
					);
				}

				echo sprintf( '<ul>%s</ul>', ob_get_clean() );
			}
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Upgraded From"><?php _e( 'Upgraded From', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The version of GiveWP installed prior to the last update.', 'give' ) ); ?></td>
		<td><?php echo esc_html( get_option( 'give_version_upgraded_from', '&ndash;' ) ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Test Mode"><?php _e( 'Test Mode', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether Test Mode is enabled in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo give_is_test_mode() ? __( 'Enabled', 'give' ) : __( 'Disabled', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Currency Code"><?php _e( 'Currency Code', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The currency code selected in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo esc_html( give_get_currency() ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Currency Position"><?php _e( 'Currency Position', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The currency position selected in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo 'before' === give_get_option( 'currency_position' ) ? __( 'Before', 'give' ) : __( 'After', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Decimal Separator"><?php _e( 'Decimal Separator', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The decimal separator defined in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo esc_html( give_get_price_decimal_separator() ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Thousands Separator"><?php _e( 'Thousands Separator', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The thousands separator defined in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo esc_html( give_get_price_thousand_separator() ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Success Page"><?php _e( 'Success Page', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The page where donors land following a successful transaction.', 'give' ) ); ?></td>
		<td><?php echo ! empty( $give_options['success_page'] ) ? esc_url( get_permalink( $give_options['success_page'] ) ) : '&ndash;'; ?></td>
	</tr>
	<tr>
		<td data-export-label="Failure Page"><?php _e( 'Failure Page', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The page where donors land following a failed transaction.', 'give' ) ); ?></td>
		<td><?php echo ! empty( $give_options['failure_page'] ) ? esc_url( get_permalink( $give_options['failure_page'] ) ) : '&ndash;'; ?></td>
	</tr>
	<tr>
		<td data-export-label="Donation History Page"><?php _e( 'Donation History Page', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The page where past donations are listed.', 'give' ) ); ?></td>
		<td><?php echo ! empty( $give_options['history_page'] ) ? esc_url( get_permalink( $give_options['history_page'] ) ) : '&ndash;'; ?></td>
	</tr>
	<tr>
		<td data-export-label="GiveWP Forms Slug"><?php _e( 'GiveWP Forms Slug', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The slug used for GiveWP donation forms.', 'give' ) ); ?></td>
		<td><?php echo esc_html( defined( 'GIVE_SLUG' ) ? '/' . GIVE_SLUG . '/' : '/donations/' ); ?></td>
	</tr>
	<?php
	$active_gateways  = give_get_enabled_payment_gateways();
	$enabled_gateways = $default_gateway = '';

	if ( $active_gateways ) {
		$default_gateway_is_active = give_is_gateway_active( give_get_default_gateway( null ) );

		if ( $default_gateway_is_active ) {
			$default_gateway = give_get_default_gateway( null );
			$default_gateway = $active_gateways[ $default_gateway ]['admin_label'];
		} else {
			$default_gateway = __( 'Test Donation', 'give' );
		}

		$gateways = [];

		foreach ( $active_gateways as $gateway ) {
			$gateways[] = $gateway['admin_label'];
		}

		$enabled_gateways = implode( ', ', $gateways );
	}
	?>
	<tr>
		<td data-export-label="Enabled Payment Gateways"><?php _e( 'Enabled Payment Gateways', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'All payment gateways enabled in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo esc_html( ! empty( $enabled_gateways ) ? $enabled_gateways : '&ndash;' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Default Payment Gateway"><?php _e( 'Default Payment Gateway', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The default payment gateway selected in GiveWP settings.', 'give' ) ); ?></td>
		<td><?php echo esc_html( ! empty( $default_gateway ) ? $default_gateway : '&ndash;' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="PayPal IPN Verification"><?php _e( 'PayPal IPN Verification', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether admins requires verification of IPN notifications with PayPal.', 'give' ) ); ?></td>
		<td><?php echo 'enabled' === give_get_option( 'paypal_verification' ) ? __( 'Enabled', 'give' ) : __( 'Disabled', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="PayPal IPN Notifications"><?php _e( 'PayPal IPN Notifications', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Displays whether when last PayPal IPN is received with which donation or transaction.', 'give' ) ); ?></td>
		<td>
			<?php
			$last_paypal_ipn_received = get_option( 'give_last_paypal_ipn_received', [] );
			$donation_id              = isset( $last_paypal_ipn_received['payment_id'] ) ? $last_paypal_ipn_received['payment_id'] : null;
			if (
				is_array( $last_paypal_ipn_received )
				&& count( $last_paypal_ipn_received ) > 0
				&& $donation_id !== null
				&& get_post( $donation_id ) instanceof WP_Post
			) {
				$ipn_timestamp   = give_get_meta( $donation_id, 'give_last_paypal_ipn_received', true );
				$transaction_url = 'https://history.paypal.com/cgi-bin/webscr?cmd=_history-details-from-hub&id=' . $last_paypal_ipn_received['transaction_id'];
				$donation_url    = site_url() . '/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $donation_id;
				echo sprintf(
					__( 'IPN received for <a href="%1$s">#%2$s</a> ( <a href="%3$s" target="_blank">%4$s</a> ) on %5$s at %6$s. Status %7$s', 'give' ),
					$donation_url,
					$donation_id,
					$transaction_url,
					$last_paypal_ipn_received['transaction_id'],
					date_i18n( 'm/d/Y', $ipn_timestamp ),
					date_i18n( 'H:i', $ipn_timestamp ),
					$last_paypal_ipn_received['auth_status']
				);
			} else {
				echo 'N/A';
			}
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Donor Email Access"><?php _e( 'Donor Email Access', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether donors can access their donation history using only email.', 'give' ) ); ?></td>
		<td><?php echo 'enabled' === give_get_option( 'email_access' ) ? __( 'Enabled', 'give' ) : __( 'Disabled', 'give' ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Stripe Webhook Notifications"><?php _e( 'Stripe Webhook Notifications', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Displays whether when last Stripe Webhook is received with which donation or transaction.', 'give' ) ); ?></td>
		<td>
			<?php
			$webhook_received_on = give_get_option( 'give_stripe_last_webhook_received_timestamp' );
			if ( ! empty( $webhook_received_on ) ) {
				$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				echo date_i18n( esc_html( $date_time_format ), $webhook_received_on );
			} else {
				echo 'N/A';
			}
			?>
		</td>
	</tr>
	<?php
	/**
	 * This action hook will be used to add system info configuration for GiveWP.
	 *
	 * @since 2.5.14
	 *
	 * @param array $give_options List of Give Settings.
	 *
	 */
	do_action( 'give_add_system_info_configuration', $give_options );
	?>
	</tbody>
</table>

<table class="give-status-table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Active GiveWP Add-ons">
			<h2><?php _e( 'Active GiveWP Add-ons', 'give' ); ?></h2></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $give_add_ons as $plugin_data ) {
		// Only show Give Core Activated Add-Ons.
		if (
			'active' !== $plugin_data['Status']
			|| false !== strpos( $plugin_data['Name'], 'GiveWP - Donation Plugin' )
		) {
			continue;
		}

		$plugin_name = $plugin_data['Name'];
		$author_name = $plugin_data['Author'];

		// Link the plugin name to the plugin URL if available.
		if ( ! empty( $plugin_data['PluginURI'] ) ) {
			$plugin_name = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( $plugin_data['PluginURI'] ),
				esc_attr__( 'Visit plugin homepage', 'give' ),
				$plugin_name
			);
		}

		// Link the author name to the author URL if available.
		if ( ! empty( $plugin_data['AuthorURI'] ) ) {
			$author_name = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( $plugin_data['AuthorURI'] ),
				esc_attr__( 'Visit author homepage', 'give' ),
				$author_name
			);
		}
		?>
		<tr>
			<td><?php echo wp_kses( $plugin_name, wp_kses_allowed_html( 'post' ) ); ?></td>
			<td class="help">&nbsp;</td>
			<td>
				<?php
				if ( isset( $plugin_data['License'] ) && true === $plugin_data['License'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark> ' . __( 'Licensed', 'give' );
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-no-alt"></span></mark> ' . __( 'Unlicensed', 'give' );
				}

				echo ' &ndash; '
					 . sprintf( _x( 'by %s', 'by author', 'give' ), wp_kses( $author_name, wp_kses_allowed_html( 'post' ) ) )
					 . ' &ndash; '
					 . esc_html( $plugin_data['Version'] );
				?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>

<table class="give-status-table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Other Active Plugins"><h2><?php _e( 'Other Active Plugins', 'give' ); ?></h2>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $plugins as $plugin_data ) {
		// Do not show Give Core and it's Add-On plugins.
		if (
			'active' !== $plugin_data['Status']
			|| in_array( $plugin_data['AuthorName'], $give_plugin_authors )
			|| false !== strpos( $plugin_data['PluginURI'], 'givewp.com' )
		) {
			continue;
		}

		$plugin_name = $plugin_data['Name'];
		$author_name = $plugin_data['Author'];

		// Link the plugin name to the plugin URL if available.
		if ( ! empty( $plugin_data['PluginURI'] ) ) {
			$plugin_name = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( $plugin_data['PluginURI'] ),
				esc_attr__( 'Visit plugin homepage', 'give' ),
				$plugin_name
			);
		}

		// Link the author name to the author URL if available.
		if ( ! empty( $plugin_data['AuthorURI'] ) ) {
			$author_name = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( $plugin_data['AuthorURI'] ),
				esc_attr__( 'Visit author homepage', 'give' ),
				$author_name
			);
		}
		?>
		<tr>
			<td><?php echo wp_kses( $plugin_name, wp_kses_allowed_html( 'post' ) ); ?></td>
			<td class="help">&nbsp;</td>
			<td><?php echo sprintf( _x( 'by %s', 'by author', 'give' ), wp_kses( $author_name, wp_kses_allowed_html( 'post' ) ) ) . ' &ndash; ' . esc_html( $plugin_data['Version'] ); ?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>

<table class="give-status-table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Inactive Plugins"><h2><?php _e( 'Inactive Plugins', 'give' ); ?></h2></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $plugins as $plugin_data ) {
		if ( 'inactive' !== $plugin_data['Status'] ) {
			continue;
		}

		$plugin_name = $plugin_data['Name'];
		$author_name = $plugin_data['Author'];

		// Link the plugin name to the plugin URL if available.
		if ( ! empty( $plugin_data['PluginURI'] ) ) {
			$plugin_name = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( $plugin_data['PluginURI'] ),
				esc_attr__( 'Visit plugin homepage', 'give' ),
				$plugin_name
			);
		}

		// Link the author name to the author URL if available.
		if ( ! empty( $plugin_data['AuthorURI'] ) ) {
			$author_name = sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( $plugin_data['AuthorURI'] ),
				esc_attr__( 'Visit author homepage', 'give' ),
				$author_name
			);
		}
		?>
		<tr>
			<td><?php echo wp_kses( $plugin_name, wp_kses_allowed_html( 'post' ) ); ?></td>
			<td class="help">&nbsp;</td>
			<td><?php echo sprintf( _x( 'by %s', 'by author', 'give' ), wp_kses( $author_name, wp_kses_allowed_html( 'post' ) ) ) . ' &ndash; ' . esc_html( $plugin_data['Version'] ); ?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>

<?php
$active_mu_plugins = (array) get_mu_plugins();
if ( ! empty( $active_mu_plugins ) ) {
	?>
	<table class="give-status-table widefat" cellspacing="0">
		<thead>
		<tr>
			<th colspan="3" data-export-label="Active MU Plugins"><h2><?php _e( 'Active MU Plugins', 'give' ); ?></h2>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php

		foreach ( $active_mu_plugins as $mu_plugin_data ) {
			if ( ! empty( $mu_plugin_data['Name'] ) ) {
				// Link the plugin name to the plugin URL if available.
				$plugin_name = esc_html( $mu_plugin_data['Name'] );

				if ( ! empty( $mu_plugin_data['PluginURI'] ) ) {
					$plugin_name = sprintf(
						'<a href="%s" title="%s">%s</a>',
						esc_url( $mu_plugin_data['PluginURI'] ),
						esc_attr__( 'Visit plugin homepage', 'give' ),
						$plugin_name
					);
				}

				// Link the author name to the author URL if available.
				$author_name = esc_html( $mu_plugin_data['Author'] );

				if ( ! empty( $mu_plugin_data['AuthorURI'] ) ) {
					$author_name = sprintf(
						'<a href="%s">%s</a>',
						esc_url( $mu_plugin_data['AuthorURI'] ),
						$author_name
					);
				}
				?>
				<tr>
					<td><?php echo $plugin_name; ?></td>
					<td class="help">&nbsp;</td>
					<td><?php echo sprintf( _x( 'by %s', 'by author', 'give' ), $author_name ) . ' &ndash; ' . esc_html( $mu_plugin_data['Version'] ); ?></td>
				</tr>
				<?php
			}
		}
		?>
		</tbody>
	</table>
<?php } ?>

<table class="give-status-table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Theme"><h2><?php _e( 'Theme', 'give' ); ?></h2></th>
	</tr>
	</thead>
	<?php
	require_once ABSPATH . 'wp-admin/includes/theme-install.php';
	$active_theme = wp_get_theme();
	?>
	<tbody>
	<tr>
		<td data-export-label="Name"><?php _e( 'Name', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The name of the current active theme.', 'give' ) ); ?></td>
		<td><?php echo esc_html( $active_theme->Name ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Version"><?php _e( 'Version', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The installed version of the current active theme.', 'give' ) ); ?></td>
		<td><?php echo esc_html( $active_theme->Version ); ?></td>
	</tr>
	<tr>
		<td data-export-label="Author URL"><?php _e( 'Author URL', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'The theme developer\'s URL.', 'give' ) ); ?></td>
		<td><?php echo $active_theme->{'Author URI'}; ?></td>
	</tr>
	<tr>
		<td data-export-label="Child Theme"><?php _e( 'Child Theme', 'give' ); ?>:</td>
		<td class="help"><?php echo Give()->tooltips->render_help( __( 'Whether the current theme is a child theme.', 'give' ) ); ?></td>
		<td>
			<?php
			echo is_child_theme() ? __( 'Yes', 'give' ) : __( 'No', 'give' ) . ' &ndash; ' . sprintf( __( 'If you\'re modifying GiveWP on a parent theme you didn\'t build personally, then we recommend using a child theme. See: <a href="%s" target="_blank">How to Create a Child Theme</a>', 'give' ), 'https://codex.wordpress.org/Child_Themes' );
			?>
		</td>
	</tr>
	<?php
	if ( is_child_theme() ) {
		$parent_theme = wp_get_theme( $active_theme->Template );
		?>
		<tr>
			<td data-export-label="Parent Theme Name"><?php _e( 'Parent Theme Name', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The name of the parent theme.', 'give' ) ); ?></td>
			<td><?php echo esc_html( $parent_theme->Name ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Version"><?php _e( 'Parent Theme Version', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The installed version of the parent theme.', 'give' ) ); ?></td>
			<td><?php echo esc_html( $parent_theme->Version ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Author URL"><?php _e( 'Parent Theme Author URL', 'give' ); ?>:</td>
			<td class="help"><?php echo Give()->tooltips->render_help( __( 'The parent theme developers URL.', 'give' ) ); ?></td>
			<td><?php echo $parent_theme->{'Author URI'}; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<script type="text/javascript">
	jQuery('.js-give-debug-report-button').click(function () {
		var report = '';
		var first_row = true;

		jQuery('.give-status-table thead, .give-status-table tbody').each(function () {
			if (jQuery(this).is('thead')) {

				var label = jQuery(this).find('th:eq(0)').data('export-label') || jQuery(this).text();

				if (true === first_row) {
					report = '### ' + jQuery.trim(label) + ' ###\n\n';
					first_row = false;
				} else {
					report = report + '\n### ' + jQuery.trim(label) + ' ###\n\n';
				}
			} else {

				jQuery('tr', jQuery(this)).each(function () {

					var label = jQuery(this).find('td:eq(0)').data('export-label') || jQuery(this).find('td:eq(0)').text();
					var the_name = jQuery.trim(label).replace(/(<([^>]+)>)/ig, ''); // Remove HTML.

					// Find value
					var $value_html = jQuery(this).find('td:eq(2)').clone();
					$value_html.find('.private').remove();
					$value_html.find('.dashicons-yes').replaceWith('&#10004;');
					$value_html.find('.dashicons-no-alt, .dashicons-warning').replaceWith('&#10060;');

					// Format value
					var the_value = jQuery.trim($value_html.text());
//					var value_array = the_value.split( ', ' );
//
//					if ( value_array.length > 1 ) {
//						// If value have a list of plugins ','.
//						// Split to add new line.
//						var temp_line ='';
//						jQuery.each( value_array, function( key, line ) {
//							temp_line = temp_line + line + '\n';
//						});
//
//						the_value = temp_line;
//					}

					report = report + '' + the_name + ': ' + the_value + '\n';
				});

			}
		});

		try {
			jQuery('.js-give-debug-report').slideDown();
			jQuery('.js-give-debug-report').find('textarea').val(report).focus().select();
			jQuery(this).hide();
			return false;
		} catch (e) {
			/* jshint devel: true */
			console.log(e);
		}

		return false;
	});
</script>
