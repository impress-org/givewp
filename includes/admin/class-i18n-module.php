<?php

/**
 * Class Give_i18n
 */
class Give_i18n_Banner {

	/**
	 * Your translation site's URL.
	 *
	 * @var string
	 */
	private $glotpress_url;

	/**
	 * Hook where you want to show the promo box.
	 *
	 * @var string
	 */
	private $hook;

	/**
	 * Will contain the site's locale.
	 *
	 * @access private
	 * @var string
	 */
	private $locale;

	/**
	 * Will contain the locale's name, obtained from your translation site.
	 *
	 * @access private
	 * @var string
	 */
	private $locale_name;

	/**
	 * Will contain the percentage translated for the plugin translation project in the locale.
	 *
	 * @access private
	 * @var int
	 */
	private $percent_translated;


	/**
	 * Indicates whether there's a translation available at all.
	 *
	 * @access private
	 * @var bool
	 */
	private $translation_exists;

	/**
	 * Indicates whether the translation's loaded.
	 *
	 * @access private
	 * @var bool
	 */
	private $translation_loaded;

	/**
	 * Give_i18n constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {

		// Only for admins.
		if ( ! is_admin() ) {
			return;
		}

		foreach ( $args as $key => $arg ) {
			$this->$key = $arg;
		}

		add_action( 'admin_init', array( $this, 'init' ) );

	}

	/**
	 * Initialize i18n banner.
	 */
	function init() {

		// First get user's locale (4.7+).
		$this->locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();

		// This plugin is en_US native.
		if ( 'en_US' === $this->locale ) {
			return;
		}

		if (
			! $this->hide_promo()
			&& ( ! empty( $_GET['post_type'] ) && 'give_forms' === $_GET['post_type'] )
			&& ( ! empty( $_GET['page'] ) && 'give-settings' === $_GET['page'] )
		) {
			add_action( $this->hook, array( $this, 'promo' ) );
		}
	}


	/**
	 * Check whether the promo should be hidden or not.
	 *
	 * @access private
	 *
	 * @return bool
	 */
	private function hide_promo() {
		$hide_promo = Give_Cache::get( 'give_i18n_give_promo_hide', true );
		if ( ! $hide_promo ) {
			if ( filter_input( INPUT_GET, 'remove_i18n_promo', FILTER_VALIDATE_INT ) === 1 ) {
				// No expiration time, so this would normally not expire, but it wouldn't be copied to other sites etc.
				Give_Cache::set( 'give_i18n_give_promo_hide', true, null, true );
				$hide_promo = true;
			}
		}

		return $hide_promo;
	}

	/**
	 * Generates a promo message.
	 *
	 * @access private
	 *
	 * @return bool|string $message
	 */
	private function promo_message() {
		$message = false;

		// Using a translation less than 90% complete.
		if ( $this->translation_exists && $this->translation_loaded && $this->percent_translated < 90 ) {
			$message = __( 'As you can see, there is a translation of this plugin in %1$s. This translation is currently %3$d%% complete. We need your help to make it complete and to fix any errors. Please register at %4$s to help %5$s to %1$s!', 'give' );
		} elseif ( ! $this->translation_loaded && $this->translation_exists ) {
			$message = __( 'You\'re using WordPress in %1$s. While %2$s has been %3$d%% translated to %1$s, it has not been shipped with the plugin yet. You can help! Register at %4$s to help complete the translation to %1$s!', 'give' );
		} elseif ( ! $this->translation_exists ) {
			$message = __( 'You\'re using WordPress in a language we don\'t support yet. We\'d love for %2$s to be translated in that language too, but unfortunately, it isn\'t right now. You can change that! Register at %4$s to help translate it!', 'give' );
		}

		// Links.
		$registration_link = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://wordpress.org/support/register.php', esc_html__( 'WordPress.org', 'give' ) );
		$translations_link = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://translate.wordpress.org/projects/wp-plugins/give', esc_html__( 'complete the translation', 'give' ) );

		// Message.
		$message = sprintf( $message, $this->locale_name, 'Give', $this->percent_translated, $registration_link, $translations_link );

		return $message;

	}

	/**
	 * Outputs a promo box
	 */
	public function promo() {

		$this->translation_details();
		$message = $this->promo_message();

		if ( $message ) {
			$this->print_css();

			ob_start();
			?>
			<div id="give-i18n-notice" class="give-addon-alert updated give-notice" style="display: none">

				<a href="https://wordpress.org/support/register.php" class="alignleft give-i18n-icon" style="margin:0" target="_blank"><span class="dashicons dashicons-translation"
																																			 style="font-size: 110px; text-decoration: none;"></span></a>

				<div class="give-i18n-notice-content">
					<a href="<?php echo esc_url( add_query_arg( array( 'remove_i18n_promo' => '1' ) ) ); ?>" class="dismiss"><span class="dashicons dashicons-dismiss"></span></a>

					<h2 style="margin: 10px 0;"><?php printf( esc_html__( 'Help Translate GiveWP to %s', 'give' ), $this->locale_name ); ?></h2>
					<p><?php echo $message; ?></p>
					<p>
						<a href="https://wordpress.org/support/register.php" target="_blank"><?php _e( 'Register now &raquo;', 'give' ); ?></a>
					</p>
				</div>
			</div>
			<?php

			$notice_html = ob_get_clean();

			// Register notice.
			Give()->notices->register_notice(
				array(
					'id'               => 'give-i18n-notice',
					'type'             => 'updated',
					'description_html' => $notice_html,
					'show'             => true,
				)
			);
		}
	}


	/**
	 * Output notice css
	 *
	 * @since  1.8.16
	 * @access private
	 */
	private function print_css() {
		?>
		<style>
			/* Banner specific styles */
			div.give-addon-alert.updated {
				padding: 10px 20px;
				position: relative;
				border-color: #69B868;
				overflow: hidden;
			}

			div.give-addon-alert a {
				color: #69B868;
			}

			#give-i18n-notice > .give-i18n-icon {
				overflow: hidden;
			}

			#give-i18n-notice > .give-i18n-icon .dashicons {
				width: 110px;
				height: 110px;
			}

			#give-i18n-notice > .give-i18n-icon:focus {
				box-shadow: none;
			}

			.give-i18n-notice-content {
				margin: 0 30px 0 125px;
			}

			div.give-addon-alert .dismiss {
				position: absolute;
				right: 20px;
				height: 100%;
				top: 50%;
				margin-top: -10px;
				outline: none;
				box-shadow: none;
				text-decoration: none;
				color: #AAA;
			}

			div.give-addon-alert .dismiss:hover {
				color: #333;
			}

			/* RTL Styles for banner */
			body.rtl .give-i18n-notice-content {
				margin: 0 125px 0 30px;
			}

			body.rtl div.give-addon-alert .dismiss {
				left: 20px;
				right: auto;
			}

		</style>
		<?php
	}

	/**
	 * Try to find the transient for the translation set or retrieve them.
	 *
	 * @access private
	 *
	 * @return object|null
	 */
	private function find_or_initialize_translation_details() {

		$set = Give_Cache::get( "give_i18n_give_{$this->locale}", true );

		if ( ! $set ) {
			$set = $this->retrieve_translation_details();
			Give_Cache::set( "give_i18n_give_{$this->locale}", $set, DAY_IN_SECONDS, true );
		}

		return $set;
	}

	/**
	 * Try to get translation details from cache, otherwise retrieve them, then parse them.
	 *
	 * @access private
	 */
	private function translation_details() {
		$set = $this->find_or_initialize_translation_details();

		$this->translation_exists = ! is_null( $set );
		$this->translation_loaded = is_textdomain_loaded( 'give' );

		$this->parse_translation_set( $set );
	}

	/**
	 * Retrieve the translation details from Give Translate.
	 *
	 * @access private
	 *
	 * @return object|null
	 */
	private function retrieve_translation_details() {

		$api_url = trailingslashit( $this->glotpress_url );

		$resp = wp_remote_get( $api_url );

		if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) === '404' ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $resp );
		unset( $resp );

		if ( $body ) {
			$body = json_decode( $body );

			foreach ( $body->translation_sets as $set ) {
				if ( ! property_exists( $set, 'wp_locale' ) ) {
					continue;
				}

				if ( $this->locale == $set->wp_locale ) {
					return $set;
				}
			}
		}

		return null;
	}

	/**
	 * Set the needed private variables based on the results from Give Translate.
	 *
	 * @param object $set The translation set
	 *
	 * @access private
	 */
	private function parse_translation_set( $set ) {
		if ( $this->translation_exists && is_object( $set ) ) {
			$this->locale_name        = $set->name;
			$this->percent_translated = $set->percent_translated;
		} else {
			$this->locale_name        = '';
			$this->percent_translated = '';
		}
	}
}

$give_i18n = new Give_i18n_Banner(
	array(
		'hook'          => 'admin_notices',
		'glotpress_url' => 'https://translate.wordpress.org/api/projects/wp-plugins/give/stable/',
	)
);
