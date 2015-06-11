<?php
/**
 *  Give Form Shortcode
 *
 * @description: Adds the ability for users to add Give forms to Tiny MCE and across the site
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0.0
 * @created    : 1/2/2015
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds an "Insert Donation Form" button above the TinyMCE Editor on add/edit screens.
 *
 * @since 1.0
 * @return string "Add Donation Form" Button
 */
function give_media_button() {

	global $pagenow, $typenow, $wp_version;

	$output = '';

	/** Only run in post/page creation and edit screens */
	if ( in_array( $pagenow, array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php'
		) ) && $typenow != 'give_forms' && $typenow != 'give_campaigns'
	) {
		/* check current WP version */
		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			$img    = '<img src="' . GIVE_PLUGIN_URL . 'assets/images/give-media.png" alt="' . sprintf( __( 'Add Donation %s', 'give' ), give_get_forms_label_singular() ) . '"/>';
			$output = '<a href="#TB_inline?width=640&inlineId=choose-give-form" class="thickbox" title="' . __( 'Add Donation Form', 'give' ) . '">' . $img . '</a>';
		} else {
			$img    = '<span class="wp-media-buttons-icon" id="give-media-button" style="background-image:url(' . give_svg_icons( 'give_grey' ) . ');margin-right:5px;"></span>';
			$output = '<a href="#TB_inline?width=640&inlineId=choose-give-form" class="thickbox button give-thickbox" title="' . sprintf( __( 'Add Donation %s', 'give' ), give_get_forms_label_singular() ) . '" style="padding-left: .4em;">' . $img . sprintf( __( 'Add Donation %s', 'give' ), give_get_forms_label_singular() ) . '</a>';
		}
	}
	echo $output;
}

add_action( 'media_buttons', 'give_media_button', 11 );

/**
 * Admin Footer For Thickbox
 *
 * Prints the footer code needed for the Insert Download
 * TinyMCE button.
 *
 * @since 1.0
 * @global $pagenow
 * @global $typenow
 * @return void
 */
function give_admin_footer_for_thickbox() {
	global $pagenow, $typenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php'
		) ) && $typenow != 'give_forms' && $typenow != 'give_campaigns'
	) {
		?>
		<script type="text/javascript">
			function insertGiveForm() {
				var id = jQuery( '.give-select#forms' ).val();

				// Return early if no form  is selected
				if ( id === '' ) {
					alert( '<?php _e( "You must choose a form", "give" ); ?>' );
					return;
				}

				// Send the shortcode to the editor
				window.send_to_editor( '[give_form id="' + id + '"]' );
			}
			jQuery( document ).ready( function ( $ ) {
				$( '#select-give-style' ).change( function () {
					if ( $( this ).val() === 'button' ) {
						$( '#give-color-choice' ).slideDown();
					} else {
						$( '#give-color-choice' ).slideUp();
					}
				} );
			} );
		</script>

		<div id="choose-give-form" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<div>
					<p><?php echo sprintf( __( 'Use the form below to insert the shortcode for a %s', 'give' ), give_get_forms_label_singular() ); ?></p>
					<?php echo Give()->html->forms_dropdown( array( 'chosen' => true ) ); ?>
				</div>
				<p class="submit">
					<input type="button" id="give-insert-download" class="button-primary" value="<?php echo sprintf( __( 'Insert %s', 'give' ), give_get_forms_label_singular() ); ?>" onclick="insertGiveForm();" />
					<a id="give-cancel-download-insert" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Cancel', 'give' ); ?>"><?php _e( 'Cancel', 'give' ); ?></a>
				</p>
			</div>
		</div>
	<?php
	}
}

add_action( 'admin_footer', 'give_admin_footer_for_thickbox' );