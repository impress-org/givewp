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
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'display';
			$this->label = esc_html__( 'Display Options', 'give' );

			$this->default_tab = 'display-settings';

			parent::__construct();
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
				case 'display-settings' :
					$settings = array(
						// Section 1: Display
						array(
							'id'   => 'give_title_display_settings_1',
							'type' => 'title'
						),
						array(
							'name'    => esc_html__( 'Default Give Styles', 'give' ),
							'desc'    => esc_html__( 'Give includes default styles for donation forms and other frontend elements.', 'give' ),
							'id'      => 'enable_css',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name' => esc_html__( 'Floating Labels', 'give' ),
							/* translators: %s: https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels */
							'desc' => sprintf( wp_kses( __( '<a href="%s" target="_blank">Floating labels</a> in set form labels within fields and can improve the donor experience. Note that if the "Disable CSS" option is enabled, you will need to style the floating labels yourself.', 'give' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( 'https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels' ) ),
							'id'   => 'enable_floatlabels',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name'    => esc_html__( 'Welcome Screen', 'give' ),
							/* translators: %s: about page URL */
							'desc'    => sprintf( wp_kses( __( 'Enable this option if you would like to disable the <a href="%s" target="_blank">Give Welcome screen</a> that display each time Give is activated or updated.', 'give' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( admin_url( 'index.php?page=give-about' ) ) ),
							'id'      => 'enable_welcome',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'id'   => 'give_title_display_settings_1',
							'type' => 'sectionend'
						)
					);
					break;

				case 'post-types' :
					$settings = array(
						array(
							'id'   => 'give_title_display_settings_2',
							'type' => 'title'
						),
						array(
							'name'    => esc_html__( 'Form Single Views', 'give' ),
							'desc'    => esc_html__( 'By default, all donation form have single views enabled which creates a specific URL on your website for that form. This option disables the singular posts from being publicly viewable. Note: you will need to embed forms using a shortcode or widget if enabled.', 'give' ),
							'id'      => 'enable_forms_singular',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name'    => esc_html__( 'Form Archives', 'give' ),
							'desc'    => esc_html__( 'Archives pages list all the donation forms you have created. This option will disable only the form\'s archive page(s). The single form\'s view will remain in place. Note: you will need to refresh your permalinks after this option has been enabled.', 'give' ),
							'id'      => 'enable_forms_archives',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name'    => esc_html__( 'Form Excerpts', 'give' ),
							'desc'    => esc_html__( 'The excerpt is an optional summary or description of a donation form; in short, a summary as to why the user should give.', 'give' ),
							'id'      => 'enable_forms_excerpt',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name'    => esc_html__( 'Form Featured Image', 'give' ),
							'desc'    => esc_html__( 'If you do not wish to use the featured image functionality you can disable it using this option and it will not be displayed for single donation forms.', 'give' ),
							'id'      => 'enable_form_featured_img',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
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
							'name'    => esc_html__( 'Single Form Sidebar', 'give' ),
							'desc'    => esc_html__( 'The sidebar allows you to add additional widgets to the Give single form view. If you don\'t plan on using the sidebar you may disable it with this option.', 'give' ),
							'id'      => 'enable_form_sidebar',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
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
							'name' => esc_html__( 'Form Categories', 'give' ),
							'desc' => esc_html__( 'Enable the "Category" taxonomy for all Give forms.', 'give' ),
							'id'   => 'enable_categories',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'name' => esc_html__( 'Form Tags', 'give' ),
							'desc' => esc_html__( 'Enable the "Tag" taxonomy for all Give forms.', 'give' ),
							'id'   => 'enable_tags',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'id'   => 'give_title_display_settings_3',
							'type' => 'sectionend'
						)
					);
					break;

				case 'term-and-conditions':
					$settings = array(
						array(
							'id'   => 'give_title_display_settings_4',
							'type' => 'title'
						),
						array(
							'name'    => esc_html__( 'Terms and Conditions', 'give' ),
							'desc'    => esc_html__( 'Would you like donors to have to agree to your terms when making a donation? Note: You can toggle this option and customize the terms per form.', 'give' ),
							'id'      => 'enable_terms',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled' => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
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
			}

			/**
			 * Filter the display options settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_display', $settings );

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
				'display-settings'    => esc_html__( 'Display', 'give' ),
				'post-types'          => esc_html__( 'Post Types', 'give' ),
				'taxonomies'          => esc_html__( 'Taxonomies', 'give' ),
				'term-and-conditions' => esc_html__( 'Term and Conditions', 'give' )
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}
	}

endif;

return new Give_Settings_Display();
