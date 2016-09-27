<?php
/**
 * Give cmb2 settings backward compatibility.
 *
 * @package     Give
 * @subpackage  Classes/Give_CMB2_Settings_Loader
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if( ! class_exists( 'Give_CMB2_Settings_Loader' ) ) :

	/**
	 * This class load cmb2 settings.
	 *
	 * @since 1.8
	 */
	Class Give_CMB2_Settings_Loader {

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
		 * Give_CMB2_Settings_Loader constructor.
		 */
		function __construct(){
			// Get previous setting class object.
			$this->prev_settings = new Give_Plugin_Settings();

			// Get current tab.
			$this->current_tab     = give_get_current_setting_tab();

			// Filter Payment Gateways settings.
			add_filter( 'give_get_settings_gateways', array( $this, 'get_filtered_addon_settings' ), 999999, 1 );
			add_filter( 'give_get_sections_gateways', array( $this, 'get_filtered_addon_sections' ), 999999, 1 );

			// Filter Addons settings.
			add_filter( 'give_get_settings_addons', array( $this, 'get_filtered_addon_settings' ), 999999, 1 );
			add_filter( 'give_get_sections_addons', array( $this, 'get_filtered_addon_sections' ), 999999, 1 );

			// Filter Licenses settings.
			add_filter( 'give_get_settings_licenses', array( $this, 'get_filtered_addon_settings' ), 999999, 1 );
		}

		/**
		 * Get section name from section title
		 *
		 * @since  1.8
		 * @param  $field_name
		 * @return string
		 */
		function get_section_name ( $field_name ) {
			$section_name = explode( ' ', $field_name );
			unset( $section_name[ count( $section_name ) - 1 ] );

			// Output.
			return strip_tags( implode( ' ', $section_name ) );
		}


		/**
		 * Get addon sections.
		 *
		 * @since  1.8
		 * @param  array $sections Array of setting fields (Optional).
		 * @return mixed
		 */
		function get_filtered_addon_sections( $sections = array() ) {
			// New sections.
			$new_sections = array();
			
			if( ( $setting_fields = $this->prev_settings->give_settings( $this->current_tab ) ) && ! empty( $setting_fields['fields'] ) ) {
				foreach ( $setting_fields['fields'] as $field ) {
					// Section name.
					$section_name = $this->get_section_name( $field['name'] );

					// Bailout: Do not load section if it is already exist.
					if( in_array( $section_name, $sections ) ) {
						continue;
					}

					// Collect new sections from addons.
					if( 'give_title' == $field['type'] ) {
						$new_sections[ sanitize_title( $field['name'] ) ] = $section_name;
					}
				}
			}

			// Add new section.
			$sections = array_merge( $sections, $new_sections );

			// Output.
			return $sections;
		}


		/**
		 * Get setting fields.
		 *
		 * @since  1.8
		 * @param  array  $settings List of settings.
		 * @return array
		 */
		function get_filtered_addon_settings( $settings ) {
			global $wp_filter;

			$new_setting_fields = array();

			if( ! empty( $settings ) ) {
				// Bailout: If setting array contain first element of tytpe title then it means it is already created with new setting api (skip this section ).
				if( 'title' == $settings[0]['type'] ){
					return $settings;
				}

				// Store title field id.
				$prev_title_field_id = '';

				// Create new setting fields.
				foreach ( $settings as $index => $field ) {

					$field['name'] = empty( $field['name'] ) ? '' : $field['name'];
					$field['desc'] = empty( $field['desc'] ) ? '' : $field['desc'];

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
							$field['css'] = 'width:25em;';
							break;

						case 'radio_inline' :
							$field['type']  = 'radio';
							$field['class'] = 'give-radio-inline';
							break;

						case 'give_title' :
							$field['type'] = 'title';
							break;
					}

					if( 'title' === $field['type'] ) {

						// If we do not have first element as title then these field will be skip from frontend
						// because there are not belong to any section, so put all abandon fields under first section.
						if( $index && empty( $prev_title_field_id )  ) {
							array_unshift(
								$new_setting_fields,
								array(
									'title' => $field['name'],
									'type' => $field['type'],
									'desc' => $field['desc'],
									'id' => $field['id']
								)
							);

							$prev_title_field_id = $field['id'];

							continue;
						} elseif ( $index ) {
							// Section end.
							$new_setting_fields[] = array(
								'type' => 'sectionend',
								'id'   => $prev_title_field_id
							);
						}

						// Section start.
						$new_setting_fields[] = array(
							'title' => $field['name'],
							'type' => $field['type'],
							'desc' => $field['desc'],
							'id' => $field['id']
						);

						$prev_title_field_id = $field['id'];
					} else {

						// setting fields
						$new_setting_fields[] = $field;
					}
				}

				// Section end.
				$new_setting_fields[] = array(
					'type' => 'sectionend',
					'id'   => $prev_title_field_id
				);

				// Check if setting page has title section or not.
				// If setting page does not have title section  then add title section to it and fix section end array id.
				if( 'title' !== $new_setting_fields[0]['type'] ) {
					array_unshift(
						$new_setting_fields,
						array(
							'title' => $settings['give_title'],
							'type' => 'title',
							'desc' => ! empty( $setting_fields['desc'] ) ? $setting_fields['desc'] : '',
							'id' => $setting_fields['id']
						)
					);

					// Update id in section end array if does not contain.
					if( empty( $new_setting_fields[count( $new_setting_fields ) - 1 ]['id'] ) ) {
						$new_setting_fields[count( $new_setting_fields ) - 1 ]['id'] = $setting_fields['id'];
					}
				}

				// Return only section related settings.
				if( $sections = $this->get_filtered_addon_sections() ) {
					$new_setting_fields = $this->get_section_settings( $new_setting_fields );
				}

				// Third party plugin backward compatibility.
				foreach ( $new_setting_fields  as $index => $field ) {
					if( in_array( $field['type'], array( 'title', 'sectionend') ) ) {
						continue;
					}

					$cmb2_filter_name = "cmb2_render_{$field['type']}";

					if( ! empty( $wp_filter[ $cmb2_filter_name ] ) ) {
						$cmb2_filter_arr = current( $wp_filter[ $cmb2_filter_name ] );

						if( ! empty( $cmb2_filter_arr ) ) {
							$new_setting_fields[$index]['func'] = current( array_keys( $cmb2_filter_arr ) );
							add_action( "give_admin_field_{$field['type']}", array( $this, 'addon_setting_field' ), 10, 2 );
						}
					}
				}

				return $new_setting_fields;
			}

			return $settings;
		}


		/**
		 * Get section related setting.
		 *
		 * @since 1.8
		 * @param $tab_settings
		 * @return array
		 */
		function get_section_settings( $tab_settings ) {
			$current_section = give_get_current_setting_section();

			$section_start = false;
			$section_end   = false;
			$section_only_setting_fields = array();

			foreach ( $tab_settings as $field ) {
				if( 'title' == $field['type'] && $current_section == sanitize_title( $field['title'] ) ) {
					$section_start = true;
				}

				if( ! $section_start || $section_end ) {
					continue;
				}

				if( $section_start && ! $section_end ) {
					if( 'sectionend' == $field['type'] ) {
						$section_end = true;
					}
					$section_only_setting_fields[] = $field;
				}
			}

			// Remove title from setting, pevent it from render in setting tab.
			$section_only_setting_fields[0]['title'] = '';

			return apply_filters( "give_get_settings_{$this->current_tab}_{$current_section}", $section_only_setting_fields, $tab_settings );
		}


		/**
		 * CMB2 addon setting fields backward compatibility.
		 *
		 * @since  1.8
		 * @param  array $field
		 * @param  mixed $saved_value
		 * @return void
		 */
		function addon_setting_field ( $field, $saved_value ) {
			// Create object for cmb2  function callback backward compatibility.
			$field_obj = (object) array( 'args' => $field );
			$field_type_object = (object) array( 'field' => $field_obj );

			switch ( $this->current_tab ) :
				case 'licenses':
					?>
					<div class="give-settings-wrap give-settings-wrap-<?php echo $this->current_tab; ?>">
						<?php $field['func']( $field_obj, $saved_value, '', '', $field_type_object ); ?>
					</div>
					<? break;

				default :
					$colspan = "colspan=\"2\"";
					?>
					<tr valign="top">
						<?php if( ! empty( $field['name'] ) && ! in_array( $field['name'], array( '&nbsp;' ) ) ) : ?>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo $field['title']; ?></label>
							</th>
							<?php $colspan = ''; ?>
						<?php endif; ?>
						<td class="give-forminp" <?php echo $colspan; ?>>
							<?php $field['func']( $field_obj, $saved_value, '', '', $field_type_object ); ?>
						</td>
					</tr>
					<?php
			endswitch;
		}
	}
endif;

new Give_CMB2_Settings_Loader();