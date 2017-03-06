<?php
/**
 * Give Admin Settings Class
 *
 * @package     Give
 * @subpackage  Classes/Give_Admin_Settings
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Admin_Settings' ) ) :

	/**
	 * Give_Admin_Settings Class.
	 *
	 * @since 1.8
	 */
	class Give_Admin_Settings {

		/**
		 * Setting pages.
		 *
		 * @since 1.8
		 * @var   array List of settings.
		 */
		private static $settings = array();

		/**
		 * Setting filter and action prefix.
		 *
		 * @since 1.8
		 * @var   string setting fileter and action anme prefix.
		 */
		private static $setting_filter_prefix = '';

		/**
		 * Error messages.
		 *
		 * @since 1.8
		 * @var   array List of errors.
		 */
		private static $errors = array();

		/**
		 * Update messages.
		 *
		 * @since 1.8
		 * @var   array List of messages.
		 */
		private static $messages = array();

		/**
		 * Include the settings page classes.
		 *
		 * @since  1.8
		 * @return array
		 */
		public static function get_settings_pages() {
			/**
			 * Filter the setting page.
			 *
			 * Note: filter dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with give-settings menu slug
			 *              then filter will be give-settings_get_settings_pages
			 *
			 * @since 1.8
			 *
			 * @param array $settings Array of settings class object.
			 */
			self::$settings = apply_filters( self::$setting_filter_prefix . '_get_settings_pages', array() );

			return self::$settings;
		}

		/**
		 * Save the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public static function save() {
			$current_tab = give_get_current_setting_tab();

			if ( empty( $_REQUEST['_give-save-settings'] ) || ! wp_verify_nonce( $_REQUEST['_give-save-settings'], 'give-save-settings' ) ) {
				die( __( 'Action failed. Please refresh the page and retry.', 'give' ) );
			}

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug and current tab.
			 * For example: if you register a setting page with give-settings menu slug and general current tab name
			 *              then action will be give-settings_save_general
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_save_' . $current_tab );

			self::add_message( 'give-setting-updated', __( 'Your settings have been saved.', 'give' ) );

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with give-settings menu slug
			 *              then action will be give-settings_saved
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_saved' );
		}

		/**
		 * Add a message.
		 *
		 * @since  1.8
		 *
		 * @param  string $code    Message code (Note: This should be unique).
		 * @param  string $message Message text.
		 *
		 * @return void
		 */
		public static function add_message( $code, $message ) {
			self::$messages[ $code ] = $message;
		}

		/**
		 * Add an error.
		 *
		 * @since  1.8
		 *
		 * @param  string $code    Message code (Note: This should be unique).
		 * @param  string $message Message text.
		 *
		 * @return void
		 */
		public static function add_error( $code, $message ) {
			self::$errors[ $code ] = $message;
		}

		/**
		 * Output messages + errors.
		 *
		 * @since  1.8
		 * @return void
		 */
		public static function show_messages() {
			$notice_html = '';
			$classes     = 'give-notice settings-error notice is-dismissible';

			self::$errors   = apply_filters( self::$setting_filter_prefix . '_error_notices', self::$errors );
			self::$messages = apply_filters( self::$setting_filter_prefix . '_update_notices', self::$messages );

			if ( 0 < count( self::$errors ) ) {
				foreach ( self::$errors as $code => $message ) {
					$notice_html .= '<div id="setting-error-' . $code . '" class="' . $classes . ' error"><p><strong>' . $message . '</strong></p></div>';
				}
			}

			if ( 0 < count( self::$messages ) ) {
				foreach ( self::$messages as $code => $message ) {
					$notice_html .= '<div id="setting-error-' . $code . '" class="' . $classes . ' updated"><p><strong>' . $message . '</strong></p></div>';
				}
			}

			echo $notice_html;
		}

		/**
		 * Settings page.
		 *
		 * Handles the display of the main give settings page in admin.
		 *
		 * @since  1.8
		 * @return void|bool
		 */
		public static function output() {
			// Get current setting page.
			self::$setting_filter_prefix = give_get_current_setting_page();

			// Bailout: Exit if setting page is not defined.
			if ( empty( self::$setting_filter_prefix ) ) {
				return false;
			}

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug
			 * For example: if you register a setting page with give-settings menu slug
			 *              then action will be give-settings_start
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_start' );

			$current_tab = give_get_current_setting_tab();

			// Include settings pages.
			self::get_settings_pages();

			// Save settings if data has been posted.
			if ( ! empty( $_POST ) ) {
				self::save();
			}

			/**
			 * Filter the tabs for current setting page.
			 *
			 * Note: filter dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with give-settings menu slug and general current tab name
			 *              then action will be give-settings_tabs_array
			 *
			 * @since 1.8
			 */
			$tabs = apply_filters( self::$setting_filter_prefix . '_tabs_array', array() );

			include 'views/html-admin-settings.php';

			return true;
		}

		/**
		 * Get a setting from the settings API.
		 *
		 * @since  1.8
		 *
		 * @param  string $option_name
		 * @param  string $field_id
		 * @param  mixed  $default
		 *
		 * @return string|bool
		 */
		public static function get_option( $option_name = '', $field_id = '', $default = false ) {
			// Bailout.
			if ( empty( $option_name ) && empty( $field_id ) ) {
				return false;
			}

			if ( ! empty( $field_id ) && ! empty( $option_name ) ) {
				// Get field value if any.
				$option_value = get_option( $option_name );

				$option_value = ( is_array( $option_value ) && array_key_exists( $field_id, $option_value ) )
					? $option_value[ $field_id ]
					: $default;
			} else {
				// If option name is empty but not field name then this means, setting is direct store to option table under there field name.
				$option_name = ! $option_name ? $field_id : $option_name;

				// Get option value if any.
				$option_value = get_option( $option_name, $default );
			}

			return $option_value;
		}

		/**
		 * Output admin fields.
		 *
		 * Loops though the give options array and outputs each field.
		 *
		 * @since  1.8
		 *
		 * @param  array  $options     Opens array to output
		 * @param  string $option_name Opens array to output
		 *
		 * @return void
		 */
		public static function output_fields( $options, $option_name = '' ) {
			$current_tab = give_get_current_setting_tab();

			// Field Default values.
			$defaults = array(
				'id'         => '',
				'class'      => '',
				'css'        => '',
				'default'    => '',
				'desc'       => '',
				'table_html' => true,
			);

			foreach ( $options as $value ) {
				if ( ! isset( $value['type'] ) ) {
					continue;
				}

				// Set title.
				$defaults['title'] = isset( $value['name'] ) ? $value['name'] : '';

				// Set default setting.
				$value = wp_parse_args( $value, $defaults );

				// Colorpicker field.
				$value['class'] = ( 'colorpicker' === $value['type'] ? trim( $value['class'] ) . ' give-colorpicker' : $value['class'] );
				$value['type']  = ( 'colorpicker' === $value['type'] ? 'text' : $value['type'] );


				// Custom attribute handling.
				$custom_attributes = array();

				if ( ! empty( $value['attributes'] ) && is_array( $value['attributes'] ) ) {
					foreach ( $value['attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				// Description handling.
				$description          = self::get_field_description( $value );

				// Switch based on type.
				switch ( $value['type'] ) {

					// Section Titles
					case 'title':
						if ( ! empty( $value['title'] ) ) {
							echo '<div class="give-setting-tab-header give-setting-tab-header-' . $current_tab . '"><h2>' . self::get_field_title( $value ) . '</h2><hr></div>';
						}

						if ( ! empty( $value['desc'] ) ) {
							echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
						}

						if ( $value['table_html'] ) {
							echo '<table class="form-table give-setting-tab-body give-setting-tab-body-' . $current_tab . '">' . "\n\n";
						}

						if ( ! empty( $value['id'] ) ) {

							/**
							 * Trigger Action.
							 *
							 * Note: action dynamically fire on basis of field id.
							 *
							 * @since 1.8
							 */
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) );
						}

						break;

					// Section Ends.
					case 'sectionend':
						if ( ! empty( $value['id'] ) ) {

							/**
							 * Trigger Action.
							 *
							 * Note: action dynamically fire on basis of field id.
							 *
							 * @since 1.8
							 */
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) . '_end' );
						}

						if ( $value['table_html'] ) {
							echo '</table>';
						}

						if ( ! empty( $value['id'] ) ) {

							/**
							 * Trigger Action.
							 *
							 * Note: action dynamically fire on basis of field id.
							 *
							 * @since 1.8
							 */
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) . '_after' );
						}

						break;

					// Standard text inputs and subtypes like 'number'.
					case 'colorpicker':
					case 'text':
					case 'email':
					case 'number':
					case 'password' :

						$type = $value['type'];
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp give-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <input
                                    name="<?php echo esc_attr( $value['id'] ); ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    type="<?php echo esc_attr( $type ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    value="<?php echo esc_attr( $option_value ); ?>"
                                    class="give-input-field<?php echo( empty( $value['class'] ) ? '' : ' ' . esc_attr( $value['class'] ) ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
                            /> <?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Textarea.
					case 'textarea':

						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp give-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
								<textarea
                                        name="<?php echo esc_attr( $value['id'] ); ?>"
                                        id="<?php echo esc_attr( $value['id'] ); ?>"
                                        style="<?php echo esc_attr( $value['css'] ); ?>"
                                        class="<?php echo esc_attr( $value['class'] ); ?>"
                                        rows="10"
                                        cols="60"
									<?php echo implode( ' ', $custom_attributes ); ?>
                                ><?php echo esc_textarea( $option_value ); ?></textarea>
							<?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Select boxes.
					case 'select' :
					case 'multiselect' :

						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp give-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <select
                                    name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) {
										echo '[]';
									} ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
                            >

								<?php
								if ( ! empty( $value['options'] ) ) {
									foreach ( $value['options'] as $key => $val ) {
										?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php

										if ( is_array( $option_value ) ) {
											selected( in_array( $key, $option_value ), true );
										} else {
											selected( $option_value, $key );
										}

										?>><?php echo $val ?></option>
										<?php
									}
								}
								?>

                            </select> <?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Radio inputs.
					case 'radio_inline' :
						$value['class'] = empty( $value['class'] ) ? 'give-radio-inline' : $value['class'] . ' give-radio-inline';
					case 'radio' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp give-forminp-<?php echo sanitize_title( $value['type'] ) ?> <?php echo( ! empty( $value['class'] ) ? $value['class'] : '' ); ?>">
                            <fieldset>
                                <ul>
									<?php
									foreach ( $value['options'] as $key => $val ) {
										?>
                                        <li>
                                            <label><input
                                                        name="<?php echo esc_attr( $value['id'] ); ?>"
                                                        value="<?php echo $key; ?>"
                                                        type="radio"
                                                        style="<?php echo esc_attr( $value['css'] ); ?>"
													<?php echo implode( ' ', $custom_attributes ); ?>
													<?php checked( $key, $option_value ); ?>
                                                /> <?php echo $val ?></label>
                                        </li>
										<?php
									}
									?>
									<?php echo $description; ?>
                            </fieldset>
                        </td>
                        </tr><?php
						break;

					// Checkbox input.
					case 'checkbox' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?>
                        <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                            </th>
                            <td class="give-forminp">
                                <input
                                        name="<?php echo esc_attr( $value['id'] ); ?>"
                                        id="<?php echo esc_attr( $value['id'] ); ?>"
                                        type="checkbox"
                                        class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
                                        value="1"
									<?php checked( $option_value, 'on' ); ?>
									<?php echo implode( ' ', $custom_attributes ); ?>
                                />
								<?php echo $description; ?>
                            </td>
                        </tr>
						<?php
						break;

					// Multi Checkbox input.
					case 'multicheck' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						$option_value = is_array( $option_value ) ? $option_value : array();
						?>
                        <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                            </th>
                            <td class="give-forminp give-forminp-<?php echo sanitize_title( $value['type'] ) ?> <?php echo( ! empty( $value['class'] ) ? $value['class'] : '' ); ?>">
                                <fieldset>
                                    <ul>
										<?php
										foreach ( $value['options'] as $key => $val ) {
											?>
                                            <li>
                                                <label>
                                                    <input
                                                            name="<?php echo esc_attr( $value['id'] ); ?>[]"
                                                            value="<?php echo $key; ?>"
                                                            type="checkbox"
                                                            style="<?php echo esc_attr( $value['css'] ); ?>"
														<?php echo implode( ' ', $custom_attributes ); ?>
														<?php if ( in_array( $key, $option_value ) ) {
															echo 'checked="checked"';
														} ?>
                                                    /> <?php echo $val ?>
                                                </label>
                                            </li>
											<?php
										}
										?>
										<?php echo $description; ?>
                                </fieldset>
                            </td>
                        </tr>
						<?php
						break;

					// File input field.
					case 'file' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp">
                            <div class="give-field-wrap">
                                <label for="<?php echo $value['id'] ?>">
                                    <input
                                            name="<?php echo esc_attr( $value['id'] ); ?>"
                                            id="<?php echo esc_attr( $value['id'] ); ?>"
                                            type="text"
                                            class="give-input-field<?php echo esc_attr( isset( $value['class'] ) ? ' ' . $value['class'] : '' ); ?>"
                                            value="<?php echo $option_value; ?>"
                                            style="<?php echo esc_attr( $value['css'] ); ?>"
										<?php echo implode( ' ', $custom_attributes ); ?>
                                    />&nbsp;&nbsp;&nbsp;&nbsp;<input class="give-upload-button button" type="button"
                                                                     value="<?php echo esc_html__( 'Add or Upload File', 'give' ); ?>">
									<?php echo $description ?>
                                    <div class="give-image-thumb<?php echo ! $option_value ? ' give-hidden' : ''; ?>">
                                        <span class="give-delete-image-thumb dashicons dashicons-no-alt"></span>
                                        <img src="<?php echo $option_value; ?>" alt="">
                                    </div>
                                </label>
                            </div>
                        </td>
                        </tr><?php
						break;

					// WordPress Editor.
					case 'wysiwyg' :
						// Get option value.
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						// Get editor settings.
						$editor_settings = ! empty( $value['options'] ) ? $value['options'] : array();
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp">
							<?php wp_editor( $option_value, $value['id'], $editor_settings ); ?>
							<?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Custom: System setting field.
					case 'system_info' :
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp">
							<?php give_system_info_callback(); ?>
							<?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Custom: Default gateways setting field.
					case 'default_gateway' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp">
							<?php give_default_gateway_callback( $value, $option_value ); ?>
							<?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Custom: Enable gateways setting field.
					case 'enabled_gateways' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp">
							<?php give_enabled_gateways_callback( $value, $option_value ); ?>
							<?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Custom: Email preview buttons field.
					case 'email_preview_buttons' :
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp">
							<?php give_email_preview_buttons_callback(); ?>
							<?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Custom: API field.
					case 'api' :
						give_api_callback();
						echo $description;
						break;

					// Custom: Gateway API key.
					case 'api_key' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						$type         = ! empty( $option_value ) ? 'password' : 'text';
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
                        </th>
                        <td class="give-forminp give-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <input
                                    name="<?php echo esc_attr( $value['id'] ); ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    type="<?php echo esc_attr( $type ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    value="<?php echo esc_attr( trim( $option_value ) ); ?>"
                                    class="give-input-field<?php echo( empty( $value['class'] ) ? '' : ' ' . esc_attr( $value['class'] ) ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
                            /> <?php echo $description; ?>
                        </td>
                        </tr><?php
						break;

					// Custom: Log field.
					case 'logs' :
						// Note: there are no need to check for html field param because we want custom html to this field.
						give_reports_tab_logs();
						echo $description;
						break;

					// Custom: API field.
					case 'data' :
						give_tools_recount_stats_display();
						echo $description;
						break;

					// Custom: Give Docs Link field type.
					case 'give_docs_link' :
						?>
                    <tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
                        <td class="give-docs-link" colspan="2">
							<?php
							echo '<p class="give-docs-link"><a href="' . esc_url( $value['url'] )
							     . '" target="_blank">'
							     . sprintf( esc_html__( 'Need Help? See docs on "%s"' ), $value['title'] )
							     . '<span class="dashicons dashicons-editor-help"></span></a></p>';
							?>
                        </td>
                        </tr><?php
						break;

					// Default: run an action
					// You can add or handle your custom field action.
					default:
						// Get option value.
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						do_action( 'give_admin_field_' . $value['type'], $value, $option_value );
						break;
				}
			}
		}

		/**
		 * Helper function to get the formatted description for a given form field.
		 * Plugins can call this when implementing their own custom settings types.
		 *
		 * @since  1.8
		 *
		 * @param  array $value The form field value array
		 *
		 * @return string The HTML description of the field.
		 */
		public static function get_field_description( $value ) {
			$description = '';

			// Support for both 'description' and 'desc' args.
			$description_key = isset( $value['description'] ) ? 'description' : 'desc';
			$value           = ( isset( $value[ $description_key ] ) && ! empty( $value[ $description_key ] ) ) ? $value[ $description_key ] : '';

			if ( ! empty( $value ) ) {
				$description = '<p class="give-field-description">' . wp_kses_post( $value ) . '</p>';
			}

			return $description;
		}


		/**
		 * Helper function to get the formated title.
		 * Plugins can call this when implementing their own custom settings types.
		 *
		 * @since  1.8
		 *
		 * @param  array $value The form field value array
		 *
		 * @return array The description and tip as a 2 element array
		 */
		public static function get_field_title( $value ) {
			$title = esc_html( $value['title'] );

			// If html tag detected then allow them to print.
			if ( strip_tags( $title ) ) {
				$title = $value['title'];
			}

			return $title;
		}

		/**
		 * Save admin fields.
		 *
		 * Loops though the give options array and outputs each field.
		 *
		 * @since  1.8
		 *
		 * @param  array  $options     Options array to output
		 * @param  string $option_name Option name to save output. If empty then option will be store in there own option name i.e option id.
		 *
		 * @return bool
		 */
		public static function save_fields( $options, $option_name = '' ) {
			if ( empty( $_POST ) ) {
				return false;
			}

			// Options to update will be stored here and saved later.
			$update_options = array();

			// Loop options and get values to save.
			foreach ( $options as $option ) {
				if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
					continue;
				}

				// Get posted value.
				if ( strstr( $option['id'], '[' ) ) {
					parse_str( $option['id'], $option_name_array );
					$field_option_name = current( array_keys( $option_name_array ) );
					$setting_name      = key( $option_name_array[ $field_option_name ] );
					$raw_value         = isset( $_POST[ $field_option_name ][ $setting_name ] ) ? wp_unslash( $_POST[ $field_option_name ][ $setting_name ] ) : null;
				} else {
					$field_option_name = $option['id'];
					$setting_name      = '';
					$raw_value         = isset( $_POST[ $option['id'] ] ) ? wp_unslash( $_POST[ $option['id'] ] ) : null;
				}

				// Format the value based on option type.
				switch ( $option['type'] ) {
					case 'checkbox' :
						$value = is_null( $raw_value ) ? '' : 'on';
						break;
					case 'wysiwyg'  :
					case 'textarea' :
						$value = wp_kses_post( trim( $raw_value ) );
						break;
					case 'multiselect' :
						$value = array_filter( array_map( 'give_clean', (array) $raw_value ) );
						break;
					default :
						$value = give_clean( $raw_value );
						break;
				}

				/**
				 * Sanitize the value of an option.
				 *
				 * @since 1.8
				 */
				$value = apply_filters( 'give_admin_settings_sanitize_option', $value, $option, $raw_value );

				/**
				 * Sanitize the value of an option by option name.
				 *
				 * @since 1.8
				 */
				$value = apply_filters( "give_admin_settings_sanitize_option_{$field_option_name}", $value, $option, $raw_value );

				if ( is_null( $value ) ) {
					continue;
				}

				// Check if option is an array and handle that differently to single values.
				if ( $field_option_name && $setting_name ) {
					if ( ! isset( $update_options[ $field_option_name ] ) ) {
						$update_options[ $field_option_name ] = get_option( $field_option_name, array() );
					}
					if ( ! is_array( $update_options[ $field_option_name ] ) ) {
						$update_options[ $field_option_name ] = array();
					}
					$update_options[ $field_option_name ][ $setting_name ] = $value;
				} else {
					$update_options[ $field_option_name ] = $value;
				}
			}

			// Save all options in our array or there own option name i.e. option id.
			if ( empty( $option_name ) ) {
				foreach ( $update_options as $name => $value ) {
					update_option( $name, $value );

					/**
					 * Trigger action.
					 *
					 * Note: This is dynamically fire on basis of option name.
					 *
					 * @since 1.8
					 */
					do_action( "give_save_option_{$name}", $value, $name );
				}
			} else {
				$old_options    = ( $old_options = get_option( $option_name ) ) ? $old_options : array();
				$update_options = array_merge( $old_options, $update_options );

				update_option( $option_name, $update_options );

				/**
				 * Trigger action.
				 *
				 * Note: This is dynamically fire on basis of setting name.
				 *
				 * @since 1.8
				 */
				do_action( "give_save_settings_{$option_name}", $update_options, $option_name );
			}

			return true;
		}
	}

endif;
