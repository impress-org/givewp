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
			// Get current tab/section
			$this->current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
			$this->current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

			// Get previous setting class object.
			$this->prev_settings = new Give_Plugin_Settings();

			// Filter & actions.
			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_pages' ), 20 );
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
		 * Get setting fields.
		 *
		 * @since  1.8
		 * @return array
		 */
		function get_settings() {
			global $wp_filter, $wp_actions;
			$new_setting_fields = array();

			if( $setting_fields = $this->prev_settings->give_settings( $this->current_tab ) ){

				if( ! empty( $setting_fields['fields'] ) ) {
					// Store title field id.
					$prev_title_field_id = '';

					// Create new setting fields.
					foreach ( $setting_fields['fields'] as $index => $field ) {

						// Update field type.
						switch ( $field['type'] ) {
							case 'text_small' :
								$field['type'] = 'text';
								break;

							case 'give_title' :
								$field['type'] = 'title';
								break;
						}

						if( 'title' === $field['type'] ) {

							// Section end.
							if( $index ) {
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
			}

			// Check if setting page has title section or not.
			// If setting page does not have title sectio  then add title section to it and fix section end array id.
			if( 'title' !== $new_setting_fields[0]['type'] ) {
				array_unshift(
					$new_setting_fields,
					array(
						'title' => $setting_fields['give_title'],
						'type' => 'title',
						'desc' => $setting_fields['desc'],
						'id' => $setting_fields['id']
					)
				);

				// Update id in section end array if does not contain.
				if( empty( $new_setting_fields[count( $new_setting_fields ) - 1 ]['id'] ) ) {
					$new_setting_fields[count( $new_setting_fields ) - 1 ]['id'] = $setting_fields['id'];
				}
			}

			return apply_filters( "give_get_settings_{$this->id}", $new_setting_fields );
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
	}
endif;

return new Give_CMB2_Settings_Loader();