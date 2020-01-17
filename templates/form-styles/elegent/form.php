<?php
/**
 * Fires while outputting donation form, before the form.
 *
 * @param int              Give_Donate_Form::ID The form ID.
 * @param array            $args An array of form arguments.
 * @param Give_Donate_Form $form Form object.
 *
 * @since 1.0
 */
do_action( 'give_pre_form', $form->ID, $args, $form );

// Set form html tags.
$form_html_tags = array(
	'id'      => "give-form-{$args['id_prefix']}",
	'class'   => $form->get_form_classes( $args ),
	'action'  => esc_url_raw( $form_action ),
	'data-id' => $args['id_prefix'],
);

/**
 * Filter the form html tags.
 *
 * @param array            $form_html_tags Array of form html tags.
 * @param Give_Donate_Form $form           Form object.
 *
 * @since 1.8.17
 */
$form_html_tags = apply_filters( 'give_form_html_tags', (array) $form_html_tags, $form );
?>
	<form <?php echo give_get_attribute_str( $form_html_tags ); ?> method="post">
		<!-- The following field is for robots only, invisible to humans: -->
		<span class="give-hidden" style="display: none !important;">
			<label for="give-form-honeypot-<?php echo $form->ID; ?>"></label>
			<input id="give-form-honeypot-<?php echo $form->ID; ?>" type="text" name="give-honeypot" class="give-honeypot give-hidden"/>
		</span>
		<?php require 'sections/introduction.php'; ?>
		<?php
		/**
		 * Fires while outputting donation form, before all other fields.
		 *
		 * @param int              Give_Donate_Form::ID The form ID.
		 * @param array            $args An array of form arguments.
		 * @param Give_Donate_Form $form Form object.
		 *
		 * @since 1.0
		 */
		do_action( 'give_donation_form_top', $form->ID, $args, $form );

		/**
		 * Fires while outputting donation form, for payment gateway fields.
		 *
		 * @param int              Give_Donate_Form::ID The form ID.
		 * @param array            $args An array of form arguments.
		 * @param Give_Donate_Form $form Form object.
		 *
		 * @since 1.7
		 */
		do_action( 'give_payment_mode_select', $form->ID, $args, $form );

		/**
		 * Fires while outputting donation form, after all other fields.
		 *
		 * @param int              Give_Donate_Form::ID The form ID.
		 * @param array            $args An array of form arguments.
		 * @param Give_Donate_Form $form Form object.
		 *
		 * @since 1.0
		 */
		do_action( 'give_donation_form_bottom', $form->ID, $args, $form );

		?>
	</form>

<?php
/**
 * Fires while outputting donation form, after the form.
 *
 * @param int              Give_Donate_Form::ID The form ID.
 * @param array            $args An array of form arguments.
 * @param Give_Donate_Form $form Form object.
 *
 * @since 1.0
 */
do_action( 'give_post_form', $form->ID, $args, $form );
