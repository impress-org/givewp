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
			$output = '<a href="#TB_inline?width=640&inlineId=choose-give-form" class="thickbox" title="' . __( 'Insert Donation Form Shortcode', 'give' ) . '">' . $img . '</a>';
		} else {
			$img    = '<span class="wp-media-buttons-icon" id="give-media-button" style="background-image:url(' . give_svg_icons( 'give_grey' ) . ');margin-right:5px;"></span>';
			$output = '<a href="#TB_inline?width=640&inlineId=choose-give-form" class="thickbox button give-thickbox" title="' . sprintf( __( 'Insert Donation %s Shortcode', 'give' ), give_get_forms_label_singular() ) . '" style="padding-left: .4em;">' . $img . sprintf( __( 'Add Donation %s', 'give' ), give_get_forms_label_singular() ) . '</a>';
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
		) ) && $typenow != 'give_forms' && $typenow != 'give_campaigns' ) {
		?>
		<script type="text/javascript">
			function insertGiveForm() {

				var tb = jQuery( '#TB_giveForm' );

				var id            = tb.find( '#forms' ).val();
				var show_title    = tb.find( '#show_title option' ).filter( ':selected' ).val();
				var show_goal     = tb.find( '#show_goal option' ).filter( ':selected' ).val();
				var show_content  = tb.find( '#show_content option' ).filter( ':selected' ).val();
				var display_style = tb.find( '#display_style option' ).filter( ':selected' ).val();
				var float_labels  = tb.find( '#float_labels option' ).filter( ':selected' ).val();

				// Return early if no form  is selected
				if ( id === '0' ) {
					alert( '<?php _e( "You must choose a form", "give" ); ?>' );
					return;
				}

				show_title    = show_title && ' show_title="' + show_title + '"';
				show_goal     = show_goal && ' show_goal="' + show_goal + '"';
				show_content  = show_content && ' show_content="' + show_content + '"';
				float_labels  = float_labels && ' float_labels="' + float_labels + '"';
				display_style = display_style && ' display_style="' + display_style + '"';

				// Send the shortcode to the editor
				window.send_to_editor(
					'[give_form id="' + id + '"' + show_title + show_goal + show_content + display_style + float_labels + ']'
				);
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

		<style type="text/css">

			#TB_giveForm .row {
				display: block;
				min-height: 30px;
				line-height: 30px;
				margin: 5px 0;
			}
			#TB_giveForm p.row {
				padding-top: 15px;
				margin-top: 0;
			}
			#TB_giveForm label {
				width: 110px;
				display: inline-block;
			}

			/* Modify the thickbox frame and size
			-------------------------------------- */
			#TB_giveForm .row {
				padding: 0 20px;
			}
			#TB_giveForm p.submit {
				text-align: right;
				background: #fcfcfc;
				border-top: 1px solid #dfdfdf;
				padding: 12px;
				margin: 20px 0 0;
			}
			#TB_window {
				background: transparent !important;
				height: auto !important;
				-webkit-box-shadow: none;
				        box-shadow: none;
			}
			#TB_title {
				position: relative;
				max-width: 480px;
				height: 50px;
				margin: 0 auto;
				z-index: 1;
			}
			#TB_ajaxWindowTitle {
				width: calc( 100% - 70px );
				font-size: 18px;
				line-height: 50px;
				padding: 0 50px 0 20px;
			}
			.tb-close-icon {
				width: 50px;
				height: 50px;
				line-height: 50px;
			}
			.tb-close-icon:before {
				line-height: 50px;
			}
			#TB_ajaxContent {
				position: relative;
				top: -51px;
				background: white;
				height: auto !important;
				width: auto !important;
				max-width: 480px;
				padding: 51px 0 0;
				margin: 0 auto;
				-webkit-box-shadow: 0 3px 6px rgba( 0, 0, 0, 0.3 );
				        box-shadow: 0 3px 6px rgba( 0, 0, 0, 0.3 );
			}
		</style>

		<div id="choose-give-form" style="display: none;">
			<div id="TB_giveForm">

				<p class="row"><em><?php printf( __( 'Use the form below to insert the shortcode for a %s', 'give' ), give_get_forms_label_singular() ); ?></em></p>

				<div class="row">
					<?php echo Give()->html->forms_dropdown( array( 'chosen' => true ) ); ?>
				</div>

				<p class="row"><em><?php _e( 'Optional form settings:', 'give' ); ?></em></p>

				<div class="row">
					<label for="show_title"><?php _e( 'Show Title', 'give' ); ?>:</label>
					<select id="show_title" name="show_title">
						<option value="">– <?php _e( 'Select', 'give' ); ?> –</option>
						<option value="true"><?php _e( 'Show', 'give' ); ?></option>
						<option value="false"><?php _e( 'Hide', 'give' ); ?></option>
					</select>
				</div>

				<div class="row">
					<label for="show_goal"><?php _e( 'Show Goal', 'give' ); ?>:</label>
					<select id="show_goal" name="show_goal">
						<option value="">– <?php _e( 'Select', 'give' ); ?> –</option>
						<option value="true"><?php _e( 'Show', 'give' ); ?></option>
						<option value="false"><?php _e( 'Hide', 'give' ); ?></option>
					</select>
				</div>

				<div class="row">
					<label for="show_content"><?php _e( 'Display Content', 'give' ); ?>:</label>
					<select id="show_content" name="show_content">
						<option value="">– <?php _e( 'Select', 'give' ); ?> –</option>
						<option value="true"><?php _e( 'Show', 'give' ); ?></option>
						<option value="false"><?php _e( 'Hide', 'give' ); ?></option>
					</select>
				</div>

				<div class="row">
					<label for="display_style"><?php _e( 'Payment Fields', 'give' ); ?>:</label>
					<select id="display_style" name="display_style">
						<option value="">– <?php _e( 'Select', 'give' ); ?> –</option>
						<option value="onpage"><?php _e( 'Show on Page', 'give' ); ?></option>
						<option value="reveal"><?php _e( 'Reveal Upon Click', 'give' ); ?></option>
						<option value="modal"><?php _e( 'Modal Window Upon Click', 'give' ); ?></option>
					</select>
				</div>

				<div class="row">
					<label for="float_labels"><?php _e( 'Floating Labels', 'give' ); ?>:</label>
					<select id="float_labels" name="float_labels">
						<option value="">– <?php _e( 'Select', 'give' ); ?> –</option>
						<option value="enabled"><?php _e( 'Enabled', 'give' ); ?></option>
						<option value="disabled"><?php _e( 'Disabled', 'give' ); ?></option>
					</select>
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
