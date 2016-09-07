<?php
/**
 * Give Admin Settings Class
 *
 * @author   WordImpress
 * @since    1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Admin_Settings' ) ) :

	/**
	 * Give_Admin_Settings Class.
	 */
	class Give_Admin_Settings {

		/**
		 * Setting pages.
		 *
		 * @var array
		 */
		private static $settings = array();

		/**
		 * Error messages.
		 *
		 * @var array
		 */
		private static $errors   = array();

		/**
		 * Update messages.
		 *
		 * @var array
		 */
		private static $messages = array();

		/**
		 * Include the settings page classes.
		 */
		public static function get_settings_pages() {
			if ( empty( self::$settings ) ) {
				$settings = array();

				include_once( 'settings/class-settings-page.php' );

//				$settings[] = include( 'settings/class-settings-general.php' );
//				$settings[] = include( 'settings/class-settings-products.php' );
				$settings[] = include( 'settings/class-settings-cmb2-backward-compatibility.php' );
//				$settings[] = include( 'settings/class-wc-settings-tax.php' );
//				$settings[] = include( 'settings/class-wc-settings-shipping.php' );
//				$settings[] = include( 'settings/class-wc-settings-checkout.php' );
//				$settings[] = include( 'settings/class-wc-settings-accounts.php' );
//				$settings[] = include( 'settings/class-wc-settings-emails.php' );
//				$settings[] = include( 'settings/class-wc-settings-integrations.php' );
//				$settings[] = include( 'settings/class-wc-settings-api.php' );

				self::$settings = apply_filters( 'give_get_settings_pages', $settings );
			}

			return self::$settings;
		}

		/**
		 * Save the settings.
		 */
		public static function save() {
			global $current_tab;

			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'give-settings' ) ) {
				die( __( 'Action failed. Please refresh the page and retry.', 'give' ) );
			}

			// Trigger actions
			do_action( 'give_settings_save_' . $current_tab );
			do_action( 'give_update_options_' . $current_tab );
			do_action( 'give_update_options' );

			self::add_message( __( 'Your settings have been saved.', 'give' ) );
			self::check_download_folder_protection();

			// Clear any unwanted data and flush rules
//			delete_transient( 'give_cache_excluded_uris' );
//			Give()->query->init_query_vars();
//			Give()->query->add_endpoints();
//			flush_rewrite_rules();

			do_action( 'give_settings_saved' );
		}

		/**
		 * Add a message.
		 * @param string $text
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text;
		}

		/**
		 * Add an error.
		 * @param string $text
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text;
		}

		/**
		 * Output messages + errors.
		 * @return string
		 */
		public static function show_messages() {
			if ( sizeof( self::$errors ) > 0 ) {
				foreach ( self::$errors as $error ) {
					echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
				}
			} elseif ( sizeof( self::$messages ) > 0 ) {
				foreach ( self::$messages as $message ) {
					echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
				}
			}
		}

		/**
		 * Settings page.
		 *
		 * Handles the display of the main give settings page in admin.
		 */
		public static function output() {
			global $current_section, $current_tab;

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			do_action( 'give_settings_start' );

			//wp_enqueue_script( 'give_settings', Give()->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'select2' ), Give()->version, true );

			wp_localize_script( 'give_settings', 'give_settings_params', array(
				'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'give' )
			) );

			// Include settings pages
			self::get_settings_pages();

			// Get current tab/section
			$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
			$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

			// Save settings if data has been posted
			if ( ! empty( $_POST ) ) {
				self::save();
			}

			// Add any posted messages
			if ( ! empty( $_GET['give_error'] ) ) {
				self::add_error( stripslashes( $_GET['give_error'] ) );
			}

			if ( ! empty( $_GET['give_message'] ) ) {
				self::add_message( stripslashes( $_GET['give_message'] ) );
			}

			// Get tabs for the settings page
			$tabs = apply_filters( 'give_settings_tabs_array', array() );

			include 'views/html-admin-settings.php';
		}

		/**
		 * Get a setting from the settings API.
		 *
 		 * @since  1.8
		 * @param  string      $option_name
		 * @param  string      $field_id
		 * @param  mixed       $default
		 * @return string|bool
		 */
		public static function get_option( $option_name = '', $field_id = '', $default = false ) {
			// Bailout.
			if( empty( $option_name ) ) {
				return false;
			}


			if( ! empty( $field_id ) ) {
				// Get field value if any.
				$option_value = get_option( $option_name );

				$option_value = ( is_array( $option_value ) && array_key_exists( $field_id, $option_value ) )
				? $option_value[ $field_id ]
				: $default;
			} else {
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
		 * @param array  $options     Opens array to output
		 * @param string $option_name Opens array to output
		 */
		public static function output_fields( $options, $option_name = '' ) {
			foreach ( $options as $value ) {
				if ( ! isset( $value['type'] ) ) {
					continue;
				}
				if ( ! isset( $value['id'] ) ) {
					$value['id'] = '';
				}
				if ( ! isset( $value['title'] ) ) {
					$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
				}
				if ( ! isset( $value['class'] ) ) {
					$value['class'] = '';
				}
				if ( ! isset( $value['css'] ) ) {
					$value['css'] = '';
				}
				if ( ! isset( $value['default'] ) ) {
					$value['default'] = '';
				}
				if ( ! isset( $value['desc'] ) ) {
					$value['desc'] = '';
				}
				if ( ! isset( $value['desc_tip'] ) ) {
					$value['desc_tip'] = false;
				}
				if ( ! isset( $value['placeholder'] ) ) {
					$value['placeholder'] = '';
				}

				// Custom attribute handling
				$custom_attributes = array();

				if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
					foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				// Description handling
				$description = self::get_field_description( $value );

				// Switch based on type
				switch ( $value['type'] ) {

					// Section Titles
					case 'title':
						if ( ! empty( $value['title'] ) ) {
							echo '<div class="give-section-header"><h2>' . esc_html( $value['title'] ) . '</h2><hr></div>';
						}
						if ( ! empty( $value['desc'] ) ) {
							echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
						}
						echo '<table class="form-table give-section-body">'. "\n\n";
						if ( ! empty( $value['id'] ) ) {
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) );
						}
						break;

					// Section Ends
					case 'sectionend':
						if ( ! empty( $value['id'] ) ) {
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) . '_end' );
						}
						echo '</table>';
						if ( ! empty( $value['id'] ) ) {
							do_action( 'give_settings_' . sanitize_title( $value['id'] ) . '_after' );
						}
						break;

					// Standard text inputs and subtypes like 'number'
					case 'text':
					case 'email':
					case 'number':
					case 'color' :
					case 'password' :

						$type         = $value['type'];
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						if ( $value['type'] == 'color' ) {
							$type = 'text';
							$value['class'] .= 'colorpick';
							$description .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>';
						}

						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
								<?php
								if ( 'color' == $value['type'] ) {
									echo '<span class="colorpickpreview" style="background: ' . esc_attr( $option_value ) . ';"></span>';
								}
								?>
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="<?php echo esc_attr( $type ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); ?>
									/> <?php echo $description; ?>
							</td>
						</tr><?php
						break;

					// Textarea
					case 'textarea':

						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
								<textarea
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									rows="10"
									cols="60"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); ?>
									><?php echo esc_textarea( $option_value );  ?></textarea>
								<?php echo $description; ?>
							</td>
						</tr><?php
						break;

					// Select boxes
					case 'select' :
					case 'multiselect' :

						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
								<select
									name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); ?>
									<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
									>
									<?php
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
									?>
								</select> <?php echo $description; ?>
							</td>
						</tr><?php
						break;

					// Radio inputs
					case 'radio' :

						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?> <?php echo ( ! empty( $value['class'] ) ? $value['class'] : '' ); ?>">
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
													class="<?php echo esc_attr( $value['class'] ); ?>"
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

					// Checkbox input
					case 'checkbox' :

						$option_value    = self::get_option( $option_name, $value['id'], $value['default'] );
						$visbility_class = array();

						if ( ! isset( $value['hide_if_checked'] ) ) {
							$value['hide_if_checked'] = false;
						}
						if ( ! isset( $value['show_if_checked'] ) ) {
							$value['show_if_checked'] = false;
						}
						if ( 'yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked'] ) {
							$visbility_class[] = 'hidden_option';
						}
						if ( 'option' == $value['hide_if_checked'] ) {
							$visbility_class[] = 'hide_options_if_checked';
						}
						if ( 'option' == $value['show_if_checked'] ) {
							$visbility_class[] = 'show_options_if_checked';
						}

						if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
							?>
								<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
									<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
									<td class="forminp forminp-checkbox">
										<fieldset>
							<?php
						} else {
							?>
								<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
							<?php
						}

						if ( ! empty( $value['title'] ) ) {
							?>
								<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
							<?php
						}

						?>
							<label for="<?php echo $value['id'] ?>">
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="checkbox"
									class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
									value="1"
									<?php checked( $option_value, 'yes'); ?>
									<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description ?>
							</label>
						<?php

						if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
										?>
										</fieldset>
									</td>
								</tr>
							<?php
						} else {
							?>
								</fieldset>
							<?php
						}
						break;

					case 'file' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp">
								<div class="give-field-wrap">
									<label for="<?php echo $value['id'] ?>">
										<input
											name="<?php echo esc_attr( $value['id'] ); ?>"
											id="<?php echo esc_attr( $value['id'] ); ?>"
											type="text"
											class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
											value="<?php echo $option_value; ?>"
											style="<?php echo esc_attr( $value['css'] ); ?>"
											<?php echo implode( ' ', $custom_attributes ); ?>
										/>&nbsp;&nbsp;&nbsp;&nbsp;<input class="give-upload-button button" type="button" value="<?php echo esc_html__( 'Add or Upload File', 'give' ); ?>">
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

					case 'system_info' :
						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp">
								<?php give_system_info_callback(); ?>
								<?php echo $description; ?>
							</td>
						</tr><?php
						break;

					case 'default_gateway' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp">
								<?php give_default_gateway_callback( $value, $option_value ); ?>
								<?php echo $description; ?>
							</td>
						</tr><?php
						break;

					case 'enabled_gateways' :
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp">
								<?php give_enabled_gateways_callback( $value, $option_value ); ?>
								<?php echo $description; ?>
							</td>
						</tr><?php
						break;

					case 'wysiwyg' :
						// Get option value.
						$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

						// Get editor settings.
						$editor_settings = ! empty( $value['options'] ) ? $value['options'] : array();
						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp">
								<?php wp_editor( $option_value, $value['id'], $editor_settings ); ?>
								<?php echo $description; ?>
							</td>
						</tr><?php
						break;

					case 'email_preview_buttons' :
						?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp">
								<?php give_email_preview_buttons_callback(); ?>
								<?php echo $description; ?>
							</td>
						</tr><?php
						break;


					// Default: run an action
					default:
						do_action( 'give_admin_field_' . $value['type'], $value );
						break;
				}
			}
		}

		/**
		 * Helper function to get the formated description and tip HTML for a
		 * given form field. Plugins can call this when implementing their own custom
		 * settings types.
		 *
		 * @param  array $value The form field value array
		 * @return array The description and tip as a 2 element array
		 */
		public static function get_field_description( $value ) {
			$description  = '';

			if( ! empty( $value['desc'] ) ) {
				$description = '<p class="give-setting-field-desc">' . wp_kses_post( $value['desc'] ) . '</p>';
			}

			return $description;
		}


		/**
		 * Helper function to get the formated title.
		 * Plugins can call this when implementing their own custom settings types.
		 *
		 * @param  array $value The form field value array
		 * @return array The description and tip as a 2 element array
		 */
		public static function get_field_title( $value ) {
			$title  = esc_html( $value['title'] );

			// If html tag detected then allow them to print.
			if( strip_tags( $title )  ) {
				$title = $value['title'];
			}

			return $title;
		}

		/**
		 * Save admin fields.
		 *
		 * Loops though the give options array and outputs each field.
		 * @since  1.8
		 * @param  array  $options     Options array to output
		 * @param  string $option_name Option name to save output. If empty then option will be store in there own option name i.e option id.
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
					$field_option_name  = current( array_keys( $option_name_array ) );
					$setting_name = key( $option_name_array[ $field_option_name ] );
					$raw_value    = isset( $_POST[ $field_option_name ][ $setting_name ] ) ? wp_unslash( $_POST[ $field_option_name ][ $setting_name ] ) : null;
				} else {
					$field_option_name  = $option['id'];
					$setting_name = '';
					$raw_value    = isset( $_POST[ $option['id'] ] ) ? wp_unslash( $_POST[ $option['id'] ] ) : null;
				}

				// Format the value based on option type.
				switch ( $option['type'] ) {
					case 'checkbox' :
						$value = is_null( $raw_value ) ? 'no' : 'yes';
						break;
					case 'textarea' :
						$value = wp_kses_post( trim( $raw_value ) );
						break;
					case 'multiselect' :
					case 'multi_select_countries' :
						$value = array_filter( array_map( 'give_clean', (array) $raw_value ) );
						break;
					case 'image_width' :
						$value = array();
						if ( isset( $raw_value['width'] ) ) {
							$value['width']  = give_clean( $raw_value['width'] );
							$value['height'] = give_clean( $raw_value['height'] );
							$value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
						} else {
							$value['width']  = $option['default']['width'];
							$value['height'] = $option['default']['height'];
							$value['crop']   = $option['default']['crop'];
						}
						break;
					default :
						$value = give_clean( $raw_value );
						break;
				}

				/**
				 * Sanitize the value of an option.
				 * @since 1.8
				 */
				$value = apply_filters( 'give_admin_settings_sanitize_option', $value, $option, $raw_value );

				/**
				 * Sanitize the value of an option by option name.
				 * @since 1.8
				 */
				$value = apply_filters( "give_admin_settings_sanitize_option_$field_option_name", $value, $option, $raw_value );

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

				/**
				 * Fire an action before saved.
				 * @deprecated 1.8 - doesn't allow manipulation of values!
				 */
				do_action( 'give_update_option', $option );
			}

			// Save all options in our array or there own option name i.e. option id.
			if( empty( $option_name ) ) {
				foreach ( $update_options as $name => $value ) {
					update_option( $name, $value );
				}
			} else {
				$old_options = ( $old_options = get_option( $option_name ) ) ? $old_options : array();
				$update_options = array_merge( $old_options, $update_options );

				update_option( $option_name, $update_options );
			}

			return true;
		}

		/**
		 * Checks which method we're using to serve downloads.
		 *
		 * If using force or x-sendfile, this ensures the .htaccess is in place.
		 */
		public static function check_download_folder_protection() {
			$upload_dir      = wp_upload_dir();
			$downloads_url   = $upload_dir['basedir'] . '/give_uploads';
			$download_method = get_option( 'give_file_download_method' );

			if ( 'redirect' == $download_method ) {

				// Redirect method - don't protect
				if ( file_exists( $downloads_url . '/.htaccess' ) ) {
					unlink( $downloads_url . '/.htaccess' );
				}

			} else {

				// Force method - protect, add rules to the htaccess file
				if ( ! file_exists( $downloads_url . '/.htaccess' ) ) {
					if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
						fwrite( $file_handle, 'deny from all' );
						fclose( $file_handle );
					}
				}
			}
		}
	}

endif;
