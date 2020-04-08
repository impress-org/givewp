<?php
use function \Give\Helpers\Form\Theme\Utils\Frontend\getFormId;

$formId = getFormId();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>  style="margin-top: 0 !important;">
	<head>
		<meta charset="utf-8">
		<title><?php echo apply_filters( 'the_title', get_post_field( 'post_title', $formId ) ); ?></title>
		<?php
		/**
		 * Fire the action hook in header
		 */
		do_action( 'give_embed_head' );
		?>
	</head>
	<body class="give-form-templates">
		<?php

		// Fetch the Give Form.
		give_get_donation_form( [ 'id' => $formId ] );

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );
		?>
	</body>
</html>
