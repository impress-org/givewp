<?php
/**
 * Give CMB2 settings backward compatibility.
 *
 * @package     Give
 * @subpackage  Classes/Give_CMB2_Settings_Loader
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! class_exists( 'Give_CMB2_Settings_Loader' ) ) :

	/**
	 * This class loads the cmb2 settings.
	 *
	 * @since 1.8
	 */
	class Give_CMB2_Settings_Loader {

		/**
		 * @since 1.8
		 * @var   Give_Plugin_Settings $prev_settings Previous setting class object.
		 */
		private $id;

		/**
		 * @since 1.8
		 * @var   Give_Plugin_Settings $prev_settings Previous setting class object.
		 */
		private $prev_settings;

		/**
		 * @since 1.8
		 * @var   string $current_tab Current setting section.
		 */
		protected $current_tab;

		/**
		 * @since 1.8
		 * @var   string $current_tab Current setting section.
		 */
		private $current_section;


		/**
		 * Give_CMB2_Settings_Loader constructor.
		 */
		function __construct() {
			// Get previous setting class object.
			$this->prev_settings = new Give_Plugin_Settings();

			// Get current tab.
			$this->current_tab     = give_get_current_setting_tab();
			$this->current_section = empty( $_REQUEST['section'] ) ? ( current( array_keys( $this->get_sections() ) ) ) : sanitize_title( $_REQUEST['section'] );

			// Tab ID.
			$this->id = $this->current_tab;

			// Add addon tabs.
			add_filter( 'give-settings_tabs_array', array( $this, 'add_addon_settings_page' ), 999999 );

			// Add save hook to addons.
			add_action( 'give-settings_get_settings_pages', array( $this, 'setup_addon_save_hook' ), 999999 );

			// Add backward compatibility filters plugin settings.
			$setting_tabs = array( 'general', 'gateways', 'display', 'emails', 'addons', 'licenses' );

			// Filter Payment Gateways settings.
			if ( in_array( $this->current_tab, $setting_tabs ) ) {
				add_filter( "give_get_settings_{$this->current_tab}", array(
					$this,
					'get_filtered_addon_settings',
				), 999999, 1 );
				add_filter( "give_get_sections_{$this->current_tab}", array(
					$this,
					'get_filtered_addon_sections',
				), 999999, 1 );
			}
		}

		/**
		 * Default setting tab.
		 *
		 * @since  1.8
		 *
		 * @param  $setting_tab
		 *
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			$default_tab = '';

			// Set default tab to first setting tab.
			if ( $sections = array_keys( $this->get_sections() ) ) {
				$default_tab = current( $sections );
			}

			return $default_tab;
		}

		/**
		 * Add addon setting pages.
		 *
		 * @since  1.8
		 *
		 * @param  $pages
		 *
		 * @return mixed
		 */
		function add_addon_settings_page( $pages ) {
			// Previous setting page.
			$previous_pages = $this->prev_settings->give_get_settings_tabs();

			// API and System Info setting tab merge to Tools setting tab, so remove them from tabs.
			unset( $previous_pages['api'] );
			unset( $previous_pages['system_info'] );

			// Tab is not register.
			$pages_diff = array_keys( array_diff( $previous_pages, $pages ) );

			// Merge old settings with new settings.
			$pages = array_merge( $pages, $previous_pages );

			if ( in_array( $this->current_tab, $pages_diff ) ) {
				// Filter & actions.
				add_filter( "give_default_setting_tab_section_{$this->current_tab}", array(
					$this,
					'set_default_setting_tab',
				), 10 );
				add_action( "give-settings_sections_{$this->current_tab}_page", array( $this, 'output_sections' ) );
				add_action( "give-settings_settings_{$this->current_tab}_page", array( $this, 'output' ), 10 );
				add_action( "give-settings_save_{$this->current_tab}", array( $this, 'save' ) );
			}

			return $pages;
		}


		/**
		 * Setup save addon data hook.
		 *
		 * @since  1.8
		 *
		 * @param  $pages
		 *
		 * @return mixed
		 */
		function setup_addon_save_hook( $pages ) {
			$page_ids = array();

			foreach ( $pages as $page ) {
				$page_ids = $page->add_settings_page( $page_ids );
			}

			// Previous setting page.
			$previous_pages = $this->prev_settings->give_get_settings_tabs();

			// API and System Info setting tab merge to Tools setting tab, so remove them from tabs.
			unset( $previous_pages['api'] );
			unset( $previous_pages['system_info'] );

			// Tab is not register.
			$pages_diff = array_keys( array_diff( $previous_pages, $page_ids ) );

			// Merge old settings with new settings.
			$pages = array_merge( $page_ids, $previous_pages );

			if ( in_array( $this->current_tab, $pages_diff ) ) {
				// Filter & actions.
				add_action( "give-settings_save_{$this->current_tab}", array( $this, 'save' ) );
			}

			return $pages;
		}

		/**
		 * Get section name from section title
		 *
		 * @since  1.8
		 *
		 * @param  $field_name
		 *
		 * @return string
		 */
		function get_section_name( $field_name ) {
			// Bailout.
			if ( empty( $field_name ) ) {
				return $field_name;
			}

			$section_name = explode( ' ', $field_name );

			// Output.
			return strip_tags( implode( ' ', $section_name ) );
		}


		/**
		 * Do not translate string
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @param $translation
		 * @param $text
		 *
		 * @return mixed
		 */
		public function en_translation( $translation, $text ) {
			return $text;
		}


		/**
		 * Get addon sections.
		 *
		 * @since  1.8
		 *
		 * @param  array $sections Array of setting fields (Optional).
		 *
		 * @return mixed
		 */
		function get_filtered_addon_sections( $sections = array() ) {
			// New sections.
			$new_sections = array();
			$sections_ID  = array_keys( $sections );
			$setting_fields = $this->prev_settings->give_settings( $this->current_tab );

			// We need untranslated settings for backward compatibility.
			add_filter( 'gettext', array( $this, 'en_translation' ), 10, 2 );
			$en_setting_fields = $this->prev_settings->give_settings( $this->current_tab );
			remove_filter( 'gettext', array( $this, 'en_translation' ), 10, 2 );
			
			if ( ! empty( $setting_fields ) && ! empty( $setting_fields['fields'] ) ) {

				foreach ( $setting_fields['fields'] as $index => $field ) {
					// Collect new sections from addons.
					if ( 'give_title' !== $field['type'] ) {
						continue;
					}

					// Untranslated setting name.
					$en_setting_field_name = isset( $en_setting_fields['fields'][ $index ]['name'] ) ? $en_setting_fields['fields'][ $index ]['name'] : '';

					// Section name.
					$field['name'] = isset( $field['name'] ) ? $field['name'] : '';
					$section_name  = $this->get_section_name( $field['name'] );

					// Check if section name exit and section title array is not empty.
					if ( ! empty( $sections ) && ! empty( $en_setting_field_name ) ) {

						// Bailout: Do not load section if it is already exist.
						if (
							in_array( sanitize_title( $en_setting_field_name ), $sections_ID ) // Check section id.
							|| in_array( $section_name, $sections )                            // Check section name.
						) {
							continue;
						}
					}

					// Collect new sections from addons.
					$new_sections[ sanitize_title( $field['name'] ) ] = $section_name;
				}
			}

			// Add new section.
			if ( ! empty( $new_sections ) ) {
				$sections = array_merge( $sections, $new_sections );
			}

			// Output.
			return $sections;
		}


		/**
		 * Get setting fields.
		 *
		 * @since  1.8
		 *
		 * @param  array $settings       List of settings.
		 * @param  array $setting_fields Main tab settings data.
		 *
		 * @return array
		 */
		function get_filtered_addon_settings( $settings, $setting_fields = array() ) {
			global $wp_filter;

			$new_setting_fields = array();

			if ( ! empty( $settings ) ) {
				// Bailout: If setting array contain first element of type title then it means it is already created with new setting api (skip this section ).
				if ( isset( $settings[0]['type'] ) && 'title' == $settings[0]['type'] ) {
					foreach ( $settings as $setting ) {
						$new_setting_fields[] = $setting;

						// We need setting only till first section end.
						if ( 'sectionend' === $setting['type'] ) {
							break;
						}
					}

					return $new_setting_fields;
				}

				// Store title field id.
				$prev_title_field_id = '';

				// Create new setting fields.
				foreach ( $settings as $index => $field ) {

					// Bailout: Must need field type to process.
					if ( ! isset( $field['type'] ) ) {
						continue;
					}

					// Set wrapper class if any.
					if ( ! empty( $field['row_classes'] ) ) {
						$field['wrapper_class'] = $field['row_classes'];
						unset( $field['row_classes'] );
					}

					$field['name'] = ! isset( $field['name'] ) ? '' : $field['name'];
					$field['desc'] = ! isset( $field['desc'] ) ? '' : $field['desc'];

					// Modify cmb2 setting fields.
					switch ( $field['type'] ) {
						case 'text' :
						case 'file' :
							$field['css'] = 'width:25em;';
							break;

						case 'text_small' :
							$field['type'] = 'text';
							break;

						case 'text_email' :
							$field['type'] = 'email';
							$field['css']  = 'width:25em;';
							break;

						case 'radio_inline' :
							$field['type']  = 'radio';
							$field['class'] = 'give-radio-inline';
							break;

						case 'give_title' :
							$field['type'] = 'title';

							// Since we are showing sections, so there now ned to show horizontal rules.
							if ( '<hr>' === $field['desc'] ) {
								$field['desc'] = '';
							}

							break;
					}

					if ( 'title' === $field['type'] ) {

						// If we do not have first element as title then these field will be skip from frontend
						// because there are not belong to any section, so put all abandon fields under first section.
						if ( $index && empty( $prev_title_field_id ) ) {
							array_unshift(
								$new_setting_fields,
								array(
									'title' => $field['name'],
									'type'  => $field['type'],
									'desc'  => $field['desc'],
									'id'    => $field['id'],
								)
							);

							$prev_title_field_id = $field['id'];

							continue;
						} elseif ( $index ) {
							// Section end.
							$new_setting_fields[] = array(
								'type' => 'sectionend',
								'id'   => $prev_title_field_id,
							);
						}

						// Section start.
						$new_setting_fields[] = array(
							'title' => $field['name'],
							'type'  => $field['type'],
							'desc'  => $field['desc'],
							'id'    => $field['id'],
						);

						$prev_title_field_id = $field['id'];
					} else {

						// setting fields
						$new_setting_fields[] = $field;
					}// End if().
				}// End foreach().

				// Section end.
				$new_setting_fields[] = array(
					'type' => 'sectionend',
					'id'   => $prev_title_field_id,
				);

				// Check if setting page has title section or not.
				// If setting page does not have title section  then add title section to it and fix section end array id.
				if ( 'title' !== $new_setting_fields[0]['type'] ) {
					array_unshift(
						$new_setting_fields,
						array(
							'title' => ( isset( $settings['give_title'] ) ? $settings['give_title'] : '' ),
							'type'  => 'title',
							'desc'  => ! empty( $setting_fields['desc'] ) ? $setting_fields['desc'] : '',
							'id'    => ( isset( $settings['id'] ) ? $settings['id'] : '' ),
						)
					);

					// Update id in section end array if does not contain.
					if ( empty( $new_setting_fields[ count( $new_setting_fields ) - 1 ]['id'] ) ) {
						$new_setting_fields[ count( $new_setting_fields ) - 1 ]['id'] = ( isset( $settings['id'] ) ? $settings['id'] : '' );
					}
				}

				// Return only section related settings.
				if ( $sections = $this->get_filtered_addon_sections() ) {
					$new_setting_fields = $this->get_section_settings( $new_setting_fields );
				}

				// Third party plugin backward compatibility.
				$wp_filter_keys = array_keys( $wp_filter );
				foreach ( $new_setting_fields as $index => $field ) {
					if ( ! isset( $field['type'] ) || in_array( $field['type'], array( 'title', 'sectionend' ) ) ) {
						continue;
					}

					$cmb2_filter_name = "cmb2_render_{$field['type']}";

					if ( in_array( $cmb2_filter_name, $wp_filter_keys ) ) {

						if ( 0 >= version_compare( 4.7, get_bloginfo( 'version' ) ) && ! empty( $wp_filter[ $cmb2_filter_name ]->callbacks ) ) {
							$cmb2_filter_arr = current( $wp_filter[ $cmb2_filter_name ]->callbacks );
						} else {
							$cmb2_filter_arr = current( $wp_filter[ $cmb2_filter_name ] );
						}

						if ( ! empty( $cmb2_filter_arr ) ) {
							// Note: function can be called either globally or with class object, it depends on how developer invoke it.
							$new_setting_fields[ $index ]['func'] = current( $cmb2_filter_arr );
							add_action( "give_admin_field_{$field['type']}", array(
								$this,
								'addon_setting_field',
							), 10, 2 );
						}
					}
				}

				return $new_setting_fields;
			}// End if().

			return $settings;
		}


		/**
		 * Get section related setting.
		 *
		 * @since 1.8
		 *
		 * @param $tab_settings
		 *
		 * @return array
		 */
		function get_section_settings( $tab_settings ) {
			$current_section = give_get_current_setting_section();

			// Note: If we are opening default tab for addon setting then it is possible that we will get empty string as current section
			// because default section filter added after save hook fire, so we will always get problem to save first section [default] or if there are only on section
			// This is hack to fix this.
			if ( empty( $current_section ) ) {
				$current_section = $this->set_default_setting_tab( $current_section );
			}

			$section_start               = false;
			$section_end                 = false;
			$section_only_setting_fields = array();

			foreach ( $tab_settings as $field ) {
				if ( 'title' == $field['type'] && $current_section == sanitize_title( $field['title'] ) ) {
					$section_start = true;
				}

				if ( ! $section_start || $section_end ) {
					continue;
				}

				if ( $section_start && ! $section_end ) {
					if ( 'sectionend' == $field['type'] ) {
						$section_end = true;
					}
					$section_only_setting_fields[] = $field;
				}
			}

			// Remove title from setting, prevent it from render in setting tab.
			$section_only_setting_fields[0]['title'] = '';

			return apply_filters( "give_get_settings_{$this->current_tab}_{$current_section}", $section_only_setting_fields, $tab_settings );
		}


		/**
		 * CMB2 addon setting fields backward compatibility.
		 *
		 * @since  1.8
		 *
		 * @param  array $field
		 * @param  mixed $saved_value
		 *
		 * @return void
		 */
		function addon_setting_field( $field, $saved_value ) {
			// Create object for cmb2  function callback backward compatibility.
			// Note: Do not call any cmb2 function on these objects
			$field_obj      = (object) array( 'args' => $field );
			$field_type_obj = (object) array( 'field' => $field_obj );

			switch ( $this->current_tab ) :
				case 'licenses':
					?>
					<div class="give-settings-wrap give-settings-wrap-<?php echo $this->current_tab; ?>">
						<?php $field['func']['function']( $field_obj, $saved_value, '', '', $field_type_obj ); ?>
					</div>
					<?php break;

				default :
					$colspan = 'colspan="2"';
					?>
					<tr valign="top">
						<?php if ( ! empty( $field['name'] ) && ! in_array( $field['name'], array( '&nbsp;' ) ) ) : ?>
							<th scope="row" class="titledesc">
								<label
										for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo $field['title']; ?></label>
							</th>
							<?php $colspan = ''; ?>
						<?php endif; ?>
						<td class="give-forminp" <?php echo $colspan; ?>>
							<?php
							if ( is_array( $field['func']['function'] ) ) {
								$classname = $field['func']['function'][0];
								$function_name = $field['func']['function'][1];
								$classname->$function_name( $field_obj, $saved_value, '', '', $field_type_obj );
							} else {
								$function_name = $field['func']['function'];
								$function_name( $field_obj, $saved_value, '', '', $field_type_obj );
							}
							?>
						</td>
					</tr>
					<?php
			endswitch;
		}

		/**
		 * Get sections.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array();

			if ( ( $setting_fields = $this->prev_settings->give_settings( $this->current_tab ) ) && ! empty( $setting_fields['fields'] ) ) {
				foreach ( $setting_fields['fields'] as $field ) {
					if ( 'give_title' == $field['type'] ) {
						$sections[ sanitize_title( $field['name'] ) ] = $this->get_section_name( $field['name'] );
					}
				}
			}

			return $sections;
		}


		/**
		 * Get setting fields.
		 *
		 * @since  1.8
		 * @return array
		 */
		function get_settings() {
			global $wp_filter;

			$new_setting_fields = array();

			if ( $setting_fields = $this->prev_settings->give_settings( $this->current_tab ) ) {
				if ( isset( $setting_fields['fields'] ) ) {

					$tab_data = array(
						'id'         => $setting_fields['id'],
						'give_title' => $setting_fields['give_title'],
						'desc'       => ( isset( $setting_fields['desc'] ) ? $setting_fields['desc'] : '' ),
					);

					$new_setting_fields = $this->get_filtered_addon_settings( $setting_fields['fields'], $tab_data );
				}
			}

			return $new_setting_fields;
		}

		/**
		 * Output sections.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output_sections() {
			$sections = $this->get_sections();

			// Show section settings only if setting section exist.
			if ( $this->current_section && ! in_array( $this->current_section, array_keys( $sections ) ) ) {
				echo '<div class="error"><p>' . __( 'Oops, this settings page does not exist.', 'give' ) . '</p></div>';
				$GLOBALS['give_hide_save_button'] = true;

				return;
			}

			// Bailout.
			if ( empty( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=' . $this->current_tab . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $this->current_section == $id ? 'current' : '' ) . '">' . strip_tags( $label ) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			echo '</ul><br class="clear" /><hr>';
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			$settings = $this->get_settings();

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Save settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function save() {
			$settings = $this->get_settings();

			Give_Admin_Settings::save_fields( $settings, 'give_settings' );
		}
	}
endif;

new Give_CMB2_Settings_Loader();
