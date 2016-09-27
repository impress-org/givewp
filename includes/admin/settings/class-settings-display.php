<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Display
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Display' ) ) :

	/**
	 * Give_Settings_Display.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Display extends Give_Settings_Page {

		/**
		 * Setting page id.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $label = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'display';
			$this->label = esc_html__( 'Display Options', 'give' );

			add_filter( 'give_default_setting_tab_section_display', array( $this, 'set_default_setting_tab' ), 10 );
			add_filter( 'give_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give_sections_{$this->id}_page", array( $this, 'output_sections' ) );
			add_action( "give_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( "give_settings_save_{$this->id}", array( $this, 'save' ) );
		}

		/**
		 * Default setting tab.
		 *
		 * @since  1.8
		 * @param  $setting_tab
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			return 'display';
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 * @param  array $pages Lst of pages.
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings = array();
			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'display' :
					$settings = array(
						// Section 1: Display
						array(
							'id'   => 'give_title_display_settings_1',
							'type' => 'title'
						),
						array(
							'name' => esc_html__( 'Disable CSS', 'give' ),
							'desc' => esc_html__( 'Enable this option if you would like to disable all of Give\'s included CSS stylesheets.', 'give' ),
							'id'   => 'disable_css',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Enable Floating Labels', 'give' ),
							/* translators: %s: https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels */
							'desc' => sprintf( wp_kses( __( 'Enable <a href="%s" target="_blank">floating labels</a> in Give\'s donation forms. Note that if the "Disable CSS" option is enabled, you will need to style the floating labels yourself.', 'give' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( 'https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels' ) ),
							'id'   => 'enable_floatlabels',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Welcome Screen', 'give' ),
							/* translators: %s: about page URL */
							'desc' => sprintf( wp_kses( __( 'Enable this option if you would like to disable the <a href="%s" target="_blank">Give Welcome screen</a> every time Give is activated and/or updated.', 'give' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( admin_url( 'index.php?page=give-about' ) ) ),
							'id'   => 'disable_welcome',
							'type' => 'checkbox'
						),
						array(
							'id'   => 'give_title_display_settings_1',
							'type' => 'sectionend'
						)
					);
					break;

				case 'post_types' :
					$settings = array(
						array(
							'id'   => 'give_title_display_settings_2',
							'type' => 'title'
						),
						array(
							'name' => esc_html__( 'Disable Form Single Views', 'give' ),
							'desc' => esc_html__( 'By default, all forms have single views enabled which create a specific URL on your website for that form. This option disables the singular and archive views from being publicly viewable. Note: you will need to embed forms using a shortcode or widget if enabled.', 'give' ),
							'id'   => 'disable_forms_singular',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Form Archives', 'give' ),
							'desc' => esc_html__( 'Archives pages list all the forms you have created. This option will disable only the form\'s archive page(s). The single form\'s view will remain in place. Note: you will need to refresh your permalinks after this option has been enabled.', 'give' ),
							'id'   => 'disable_forms_archives',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Form Excerpts', 'give' ),
							'desc' => esc_html__( 'The excerpt is an optional summary or description of a donation form; in short, a summary as to why the user should give.', 'give' ),
							'id'   => 'disable_forms_excerpt',
							'type' => 'checkbox'
						),

						array(
							'name'    => esc_html__( 'Featured Image Size', 'give' ),
							'desc'    => esc_html__( 'The Featured Image is an image that is chosen as the representative image for a donation form. Some themes may have custom featured image sizes. Please select the size you would like to display for your single donation forms\' featured image.', 'give' ),
							'id'      => 'featured_image_size',
							'type'    => 'select',
							'default' => 'large',
							'options' => give_get_featured_image_sizes()
						),
						array(
							'name' => esc_html__( 'Disable Form Featured Image', 'give' ),
							'desc' => esc_html__( 'If you do not wish to use the featured image functionality you can disable it using this option and it will not be displayed for single donation forms.', 'give' ),
							'id'   => 'disable_form_featured_img',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Disable Single Form Sidebar', 'give' ),
							'desc' => esc_html__( 'The sidebar allows you to add additional widget to the Give single form view. If you don\'t plan on using the sidebar you may disable it with this option.', 'give' ),
							'id'   => 'disable_form_sidebar',
							'type' => 'checkbox'
						),
						array(
							'id'   => 'give_title_display_settings_2',
							'type' => 'sectionend'
						)
					);
					break;

				case 'taxonomies':
					$settings = array(
						array(
							'id'   => 'give_title_display_settings_3',
							'type' => 'title'
						),
						array(
							'name' => esc_html__( 'Enable Form Categories', 'give' ),
							'desc' => esc_html__( 'Enables the "Category" taxonomy for all Give forms.', 'give' ),
							'id'   => 'enable_categories',
							'type' => 'checkbox'
						),
						array(
							'name' => esc_html__( 'Enable Form Tags', 'give' ),
							'desc' => esc_html__( 'Enables the "Tag" taxonomy for all Give forms.', 'give' ),
							'id'   => 'enable_tags',
							'type' => 'checkbox'
						),
						array(
							'id'   => 'give_title_display_settings_3',
							'type' => 'sectionend'
						)
					);
					break;

				case 'term-conditions':
					$settings = array(
						array(
							'id'   => 'give_title_display_settings_4',
							'type' => 'title'
						),
						array(
							'name' => esc_html__( 'Agree to Terms Label', 'give' ),
							'desc' => esc_html__( 'The label shown next to the agree to terms check box. Add your own to customize or leave blank to use the default text placeholder. Note: You can customize the label per form as needed.', 'give' ),
							'id'   => 'agree_to_terms_label',
							'attributes'  => array(
								'placeholder' => esc_attr__( 'Agree to Terms?', 'give' ),
							),
							'type' => 'text'
						),
						array(
							'name' => esc_html__( 'Agreement Text', 'give' ),
							'desc' => esc_html__( 'This is the actual text which the user will have to agree to in order to make a donation. Note: You can customize the content per form as needed.', 'give' ),
							'id'   => 'agreement_text',
							'type' => 'wysiwyg'
						),
						array(
							'id'   => 'give_title_display_settings_4',
							'type' => 'sectionend'
						)
					);
					break;

				default:
					/**
					 * Filter the display options settings.
					 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
					 */
					$settings = apply_filters( 'give_settings_display', $settings );
			}

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'display'         => esc_html__( 'Display Settings', 'give' ),
				'post_types'      => esc_html__( 'Post Types', 'give' ),
				'taxonomies'      => esc_html__( 'Taxonomies', 'give' ),
				'term-conditions' => esc_html__( 'Term and Conditions', 'give' )
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output sections.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output_sections() {
			// Get current section.
			$current_section = give_get_current_setting_section();

			// Get all sections.
			$sections = $this->get_sections();

			if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

			// Get section keys.
			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			echo '</ul><br class="clear" />';
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

return new Give_Settings_Display();
