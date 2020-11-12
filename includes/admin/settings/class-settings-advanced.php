<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Advanced
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

use Give\Onboarding\Setup\Page as SetupPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Advanced' ) ) :

	/**
	 * Give_Settings_Advanced.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Advanced extends Give_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'advanced';
			$this->label = __( 'Advanced', 'give' );

			$this->default_tab = 'advanced-options';

			if ( $this->id === give_get_current_setting_tab() ) {
				add_action(
					'give_admin_field_remove_cache_button',
					[
						$this,
						'render_remove_cache_button',
					],
					10,
					1
				);
				add_action( 'give_save_settings_give_settings', [ $this, 'validate_settngs' ] );
			}

			parent::__construct();
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 * @since  1.8
		 */
		public function get_settings() {
			$settings = [];

			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'advanced-options':
					$settings = [
						[
							'id'   => 'give_title_data_control_2',
							'type' => 'title',
						],
						[
							'name'    => __( 'Default GiveWP Styles', 'give' ),
							'desc'    => __( 'You can disable Give\'s default styles for donation forms and other frontend elements.', 'give' ),
							'id'      => 'css',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => [
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							],
						],
						[
							'name'    => __( 'Remove Data on Uninstall', 'give' ),
							'desc'    => __( 'When the plugin is deleted, completely remove all GiveWP data. This includes all GiveWP settings, forms, form meta, donor, donor data, donations. Everything.', 'give' ),
							'id'      => 'uninstall_on_delete',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => [
								'enabled'  => __( 'Yes, Remove all data', 'give' ),
								'disabled' => __( 'No, keep my GiveWP settings and donation data', 'give' ),
							],
						],
						[
							'name'    => __( 'Default User Role', 'give' ),
							'desc'    => __( 'Assign default user roles for donors when donors opt to register as a WP User.', 'give' ),
							'id'      => 'donor_default_user_role',
							'type'    => 'select',
							'default' => 'give_donor',
							'options' => give_get_user_roles(),
						],
						[
							/* translators: %s: the_content */
							'name'    => sprintf( __( '%s filter', 'give' ), '<code>the_content</code>' ),
							/* translators: 1: https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content 2: the_content */
							'desc'    => sprintf( __( 'If you are seeing extra social buttons, related posts, or other unwanted elements appearing within your forms then you can disable WordPress\' content filter. <a href="%1$s" target="_blank">Learn more</a> about %2$s filter.', 'give' ), esc_url( 'https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content' ), '<code>the_content</code>' ),
							'id'      => 'the_content_filter',
							'default' => 'enabled',
							'type'    => 'radio_inline',
							'options' => [
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							],
						],
						[
							'name'    => __( 'Script Loading Location', 'give' ),
							'desc'    => __( 'This allows you to load your GiveWP scripts either in the <code>&lt;head&gt;</code> or footer of your website.', 'give' ),
							'id'      => 'scripts_footer',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => [
								'disabled' => __( 'Head', 'give' ),
								'enabled'  => __( 'Footer', 'give' ),
							],
						],
						[
							'name'    => __( 'Babel Polyfill Script', 'give' ),
							'desc'    => __( 'Decide whether to load the Babel polyfill, which provides backwards compatibility for older browsers such as IE 11. The polyfill may be disabled to avoid conflicts with other themes or plugins that load the same script.', 'give' ),
							'id'      => 'babel_polyfill_script',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => [
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							],
						],
						[
							'name'          => __( 'Setup Page', 'give' ),
							/* translators: %s: about page URL */
							'desc'          => sprintf(
								wp_kses(
									__( 'Disable this option if you would like to disable the <a href="%s" target="_blank">GiveWP Setup page</a> that displays when GiveWP is first installed.', 'give' ),
									[
										'a' => [
											'href'   => [],
											'target' => [],
										],
									]
								),
								esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-setup' ) )
							),
							'id'            => 'setup_page_enabled',
							'type'          => 'radio_inline',
							'default'       => give_is_setting_enabled(
								SetupPage::getSetupPageEnabledOrDisabled()
							)
								? SetupPage::ENABLED
								: SetupPage::DISABLED,
							'options'       => [
								SetupPage::ENABLED  => __( 'Enabled', 'give' ),
								SetupPage::DISABLED => __( 'Disabled', 'give' ),
							],
							'wrapper_class' => version_compare( get_bloginfo( 'version' ), '5.0', '<=' ) ? 'give-hidden' : null,
						],
						[
							'name'    => __( 'Form Page URL Prefix', 'give' ),
							'desc'    => sprintf(
								__( 'This slug will be used as a base for the iframe URL that contains all form templates besides the legacy form template. Currently the iframe URLs look like %1$s. This options adds a rewrite rule that transforms the URL structure to a set of query vars. If you modify this URL prefix please ensure it is unique as it can cause a 404 error if a similar URL already exists.', 'give' ),
								'<code>' . trailingslashit( home_url() ) . Give()->routeForm->getBase() . '/{form-slug}</code>'
							),
							'id'      => Give()->routeForm->getOptionName(),
							'type'    => 'text',
							'default' => Give()->routeForm->getBase(),
						],
						[
							'name'        => 'GiveWP Cache',
							'id'          => 'give-clear-cache',
							'buttonTitle' => __( 'Clear Cache', 'give' ),
							'desc'        => __( 'Click this button if you want to clear Give\'s cache. The plugin stores common settings and queries in cache to optimize performance. Clearing cache will remove and begin rebuilding these saved queries.', 'give' ),
							'type'        => 'remove_cache_button',
						],
						[
							'name'  => __( 'Advanced Settings Docs Link', 'give' ),
							'id'    => 'advanced_settings_docs_link',
							'url'   => esc_url( 'http://docs.givewp.com/settings-advanced' ),
							'title' => __( 'Advanced Settings', 'give' ),
							'type'  => 'give_docs_link',
						],
						[
							'id'   => 'give_title_data_control_2',
							'type' => 'sectionend',
						],
					];
					break;

				case 'akismet-spam-protection':
					$settings = [
						[
							'id'   => 'give_setting_advanced_section_akismet_spam_protection',
							'type' => 'title',
						],
						[
							'name'    => __( 'Akismet SPAM Protection', 'give' ),
							'desc'    => __( 'Add a layer of SPAM protection to your donation submissions with Akismet. When enabled, donation submissions will be first sent through Akismet\'s SPAM check API if you have the plugin activated and configured.', 'give' ),
							'id'      => 'akismet_spam_protection',
							'type'    => 'radio_inline',
							'default' => ( give_check_akismet_key() ) ? 'enabled' : 'disabled',
							'options' => [
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							],
						],
						[
							'name'             => __( 'Whitelist by Email', 'give' ),
							'desc'             => sprintf(
								'%1$s %2$s',
								__( 'Add emails one at a time to ensure that donations using that email bypass GiveWP\'s Akismet SPAM filtering. Emails added to the list here are always allowed to donate, even if they\'ve been flagged by Akismet.', 'give' ),
								sprintf(
									__( 'To permanently prevent emails from being flagged as SPAM by Akismet <a href="%1$s" target="_blank">contact their team here</a>.', 'give' ),
									esc_url( 'https://akismet.com/contact/' )
								)
							),
							'id'               => 'akismet_whitelisted_email_addresses',
							'type'             => 'email',
							'attributes'       => [
								'placeholder' => 'jon@email.com',
							],
							'repeat'           => true,
							'repeat_btn_title' => esc_html__( 'Add Email', 'give' ),
						],
						[
							'id'   => 'give_setting_advanced_section_akismet_spam_protection',
							'type' => 'sectionend',
						],
					];
					break;
			}

			/**
			 * Hide caching setting by default.
			 *
			 * @since 2.0
			 */
			if ( apply_filters( 'give_settings_advanced_show_cache_setting', false ) ) {
				array_splice(
					$settings,
					1,
					0,
					[
						[
							'name'    => __( 'Cache', 'give' ),
							'desc'    => __( 'If caching is enabled the plugin will start caching custom post type related queries and reduce the overall load time.', 'give' ),
							'id'      => 'cache',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => [
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							],
						],
					]
				);
			}

			/**
			 * Filter the advanced settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_advanced', $settings );

			/**
			 * Filter the settings.
			 *
			 * @param array $settings
			 *
			 * @since  1.8
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 * @since 1.8
		 */
		public function get_sections() {
			$sections = [
				'advanced-options'        => __( 'Advanced Options', 'give' ),
				'akismet-spam-protection' => __( 'Akismet SPAM Protection', 'give' ),
			];

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}


		/**
		 *  Render remove_cache_button field type
		 *
		 * @param array $field
		 *
		 * @since  2.1
		 * @access public
		 */
		public function render_remove_cache_button( $field ) {
			?>
			<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : ''; ?>>
				<th scope="row" class="titledesc">
					<label
						for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['name'] ); ?></label>
				</th>
				<td class="give-forminp">
					<button type="button" id="<?php echo esc_attr( $field['id'] ); ?>"
							class="button button-secondary"><?php echo esc_html( $field['buttonTitle'] ); ?></button>
					<?php echo Give_Admin_Settings::get_field_description( $field ); ?>
				</td>
			</tr>
			<?php
		}


		/**
		 * Validate setting
		 *
		 * @param array $options
		 *
		 * @since  2.2.0
		 * @access public
		 */
		public function validate_settngs( $options ) {
			// Sanitize data.
			$akismet_spam_protection = isset( $options['akismet_spam_protection'] )
				? $options['akismet_spam_protection']
				: ( give_check_akismet_key() ? 'enabled' : 'disabled' );

			// Show error message if Akismet not configured and Admin try to save 'enabled' option.
			if (
				give_is_setting_enabled( $akismet_spam_protection )
				&& ! give_check_akismet_key()
			) {
				Give_Admin_Settings::add_error(
					'give-akismet-protection',
					__( 'Please properly configure Akismet to enable SPAM protection.', 'give' )
				);

				give_update_option( 'akismet_spam_protection', 'disabled' );
			}
		}
	}

endif;

return new Give_Settings_Advanced();
