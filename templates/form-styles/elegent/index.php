<div id="give-form-<?php echo $form->ID; ?>-wrap" class="<?php echo $form->get_form_wrap_classes( $args ); ?>">
	<?php
	if ( $form->is_close_donation_form() ) {
		include 'close-form-notice.php';
	} else {
		include 'form.php';
	}
	?>
</div><!--end #give-form-<?php echo absint( $form->ID ); ?>-->
