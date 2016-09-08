<?php
/**
 * Give cmb2 settings backward compatibility.
 *
 * @author      WordImpress
 * @version     1.8
 */

if( ! class_exists( 'Give_CMB2_Settings_Loader' ) ) :
	Class Give_CMB2_Settings_Loader extends Give_Settings_Page {

		/* @var Give_Plugin_Settings $prev_settings Previous setting class object. */
		private $prev_settings;

		/* @var string $current_tab Current setting tab. */
		private $current_tab;

		/* @var string $current_tab Current setting section. */
		private $current_section;

		/**
		 * Give_CMB2_Settings_Loader constructor.
		 */
		function __construct(){
			// Get previous setting class object.
			$this->prev_settings = new Give_Plugin_Settings();

			// Get current tab/section
			$this->current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
			$this->current_section = empty( $_REQUEST['section'] ) ? ( current( array_keys( $this->get_sections() ) ) ) : sanitize_title( $_REQUEST['section'] );


			// Hide save button on api and system_info setting tab.
			if( in_array( $this->current_tab, array( 'api', 'system_info' ) ) ) {
				$GLOBALS['give_hide_save_button'] = true;
			}

			// Filter & actions.
			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_pages' ), 20 );
			add_action( "give_sections_{$this->current_tab}_page", array( $this, 'output_sections' ) );
			add_action( "give_settings_{$this->current_tab}_page", array( $this, 'output' ), 10 );
			add_action( "give_settings_save_{$this->current_tab}", array( $this, 'save' ) );
		}


		/**
		 * Add all old setting pages to settings.
		 *
		 * @since  1.8
		 * @param  array $pages Setting tabs.
		 * @return array
		 */
		function add_settings_pages( $pages ) {
			if( $old_settings = $this->prev_settings->give_get_settings_tabs() ) {
				foreach ( $old_settings as $old_setting_name => $old_setting_label ) {
					$pages[ $old_setting_name ] = $old_setting_label;
				}

				return $pages;
			}
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array();

			if( ( $setting_fields = $this->prev_settings->give_settings( $this->current_tab ) ) && ! empty( $setting_fields['fields'] ) ) {
				foreach ( $setting_fields['fields'] as $field ) {
					if( 'give_title' == $field['type'] ) {
						$sections[ sanitize_title( $field['name'] ) ] = str_replace( array( 'Settings' ), '', $field['name'] );
					}
				}
			}

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output sections.
		 */
		public function output_sections() {
			$sections = $this->get_sections();

			if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=' . $this->current_tab . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $this->current_section == $id  ? 'current' : '' ) . '">' . strip_tags( $label ) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			echo '</ul><br class="clear" />';
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

			if( $setting_fields = $this->prev_settings->give_settings( $this->current_tab ) ){

				if( ! empty( $setting_fields['fields'] ) ) {
					// Store title field id.
					$prev_title_field_id = '';

					// Create new setting fields.
					foreach ( $setting_fields['fields'] as $index => $field ) {

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
				}

				// Check if setting page has title section or not.
				// If setting page does not have title section  then add title section to it and fix section end array id.
				if( 'title' !== $new_setting_fields[0]['type'] ) {
					array_unshift(
						$new_setting_fields,
						array(
							'title' => $setting_fields['give_title'],
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
				if( $sections = $this->get_sections() ) {
					$new_setting_fields = self::get_section_settiings( $new_setting_fields );
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
			}

			return apply_filters( "give_get_settings_{$this->id}", $new_setting_fields );
		}


		/**
		 * Get section related setting.
		 *
		 * @since 1.8
		 * @param $tab_settings
		 * @return array
		 */
		function get_section_settiings( $tab_settings ) {
			$section_start = false;
			$section_end   = false;
			$section_only_setting_fields = array();

			foreach ( $tab_settings as $field ) {
				if( 'title' == $field['type'] &&  $this->current_section == sanitize_title( $field['title'] ) ) {
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

			return apply_filters( "give_get_settings_{$this->current_tab}_{$this->current_section}", $section_only_setting_fields, $tab_settings );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			$settings = $this->get_settings();

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			$settings = $this->get_settings();

			Give_Admin_Settings::save_fields( $settings, 'give_settings' );
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
			?>
			<div class="give-settings-wrap give-settings-wrap-<?php echo $this->current_tab; ?>">
				<?php $field['func']( $field_obj, $saved_value, '', '', $field_type_object ); ?>
			</div>
			<?php
		}
	}
endif;

return new Give_CMB2_Settings_Loader();