<?php
/**
 * Give Form Shortcode
 *
 * @description: Adds the ability for users to add Give forms to Tiny MCE and across the site
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0.0
 * @created    : 26/09/2015
 */

defined( 'ABSPATH' ) || exit;

class Give_Form_Shortcode extends Give_Shortcode
{
	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->shortcode = 'give_form';

		$this->dialog_title = __( 'Insert Give Form Shortcode', 'give' );
		$this->dialog_alert = __( 'You must first choose a form!', 'give' );
		$this->dialog_okay  = __( 'Insert Shortcode', 'give' );
		$this->dialog_close = __( 'Close', 'give' );

		parent::__construct();
	}

	/**
	 * Define the shortcode dialog fields
	 *
	 * @return array
	 */
	public function define_fields()
	{
		return array(
			array(
				'type' => 'container',
				'html' => sprintf( '<p>%s</p>', sprintf( __( 'Use the form below to insert the shortcode for a %s', 'give' ), give_get_forms_label_singular() ) ),
			),
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type' => 'give_forms',
				),
				'name'        => 'id',
				'placeholder' => sprintf( '– %s –', __( 'Select a Form', 'give' ) ),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p style="font-weight: 600 !important; margin-top: 1em;">%s</p>', __( 'Optional form settings', 'give' ) ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_title',
				'label'       => __( 'Show Title:', 'give' ),
				'tooltip'     => __( 'Do you want to display the form title?', 'give' ),
				'options'     => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_goal',
				'label'       => __( 'Show Goal:', 'give' ),
				'tooltip'     => __( 'Do you want to display the donation goal?', 'give' ),
				'options'     => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_content',
				'label'       => __( 'Display Content:', 'give' ),
				'tooltip'     => __( 'Do you want to display the form content?', 'give' ),
				'options'     => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'display_style',
				'label'       => __( 'Payment Fields:', 'give' ),
				'tooltip'     => __( 'How would you like to display payment information?', 'give' ),
				'options'     => array(
					'onpage' => __( 'Show on Page', 'give' ),
					'reveal' => __( 'Reveal Upon Click', 'give' ),
					'modal'  => __( 'Modal Window Upon Click', 'give' ),
				),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'float_labels',
				'label'       => __( 'Floating Labels:', 'give' ),
				'tooltip'     => __( 'Would you like to enable floating labels?', 'give' ),
				'options'     => array(
					'enabled'  => __( 'Enabled', 'give' ),
					'disabled' => __( 'Disabled', 'give' ),
				),
			),
		);
	}

	/**
	 * Adds the "Donation Form" button above the TinyMCE Editor on add/edit screens.
	 *
	 * @return string
	 */
	public function give_shortcode_button()
	{
		global $pagenow, $typenow, $wp_version;

		// Only run in admin post/page creation and edit screens
		if( in_array( $pagenow, ['post.php', 'page.php', 'post-new.php', 'post-edit.php'] )
			&& $typenow != 'give_forms'
			&& $typenow != 'give_campaigns' ) {

			$button_text = sprintf( __( 'Add Donation %s', 'give' ), give_get_forms_label_singular() );

			// check current WP version
			$img = ( version_compare( $wp_version, '3.5', '<' ) )
				? '<img src="' . GIVE_PLUGIN_URL . 'assets/images/give-media.png" alt="' . $button_text . '"/>'
				: '<span class="wp-media-buttons-icon" id="give-media-button" style="background-image: url(' . give_svg_icons( 'give_grey' ) . ');"></span>';

			printf( '<button class="button give-shortcode-button" title="%s">%s %s</button>',
				__( 'Insert Donation Form Shortcode', 'give' ),
				$img,
				$button_text
			);
		}
	}
}

new Give_Form_Shortcode;



























// function give_media_button() {

// 	global $pagenow, $typenow, $wp_version;

// 	$output = '';

// 	/** Only run in post/page creation and edit screens */
// 	if ( in_array( $pagenow, array(
// 			'post.php',
// 			'page.php',
// 			'post-new.php',
// 			'post-edit.php'
// 		) ) && $typenow != 'give_forms' && $typenow != 'give_campaigns'
// 	) {
// 		/* check current WP version */
// 		if ( version_compare( $wp_version, '3.5', '<' ) ) {
// 			$img    = '<img src="' . GIVE_PLUGIN_URL . 'assets/images/give-media.png" alt="' . sprintf( __( 'Add Donation %s', 'give' ), give_get_forms_label_singular() ) . '"/>';
// 			$output = '<a href="#TB_inline?width=640&inlineId=choose-give-form" class="thickbox" title="' . __( 'Insert Donation Form Shortcode', 'give' ) . '">' . $img . '</a>';
// 		} else {
// 			$img    = '<span class="wp-media-buttons-icon" id="give-media-button" style="background-image:url(' . give_svg_icons( 'give_grey' ) . ');margin-right:5px;"></span>';
// 			$output = '<a href="#TB_inline?width=640&inlineId=choose-give-form" class="thickbox button give-thickbox" title="' . sprintf( __( 'Insert Donation %s Shortcode', 'give' ), give_get_forms_label_singular() ) . '" style="padding-left: .4em;">' . $img . sprintf( __( 'Add Donation %s', 'give' ), give_get_forms_label_singular() ) . '</a>';
// 		}
// 	}
// 	echo $output;
// }

// add_action( 'media_buttons', 'give_media_button', 11 );

