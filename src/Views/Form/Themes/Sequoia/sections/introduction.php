<?php

// Get Theme options
$theme_options = give_get_meta( $form->ID, '_give_sequoia_form_theme_settings', true, null );

// Get headline and description
$headline    = $theme_options['introduction']['headline'];
$description = $theme_options['introduction']['description'];
$image       = $theme_options['introduction']['image'];
?>

<div class="give-section introduction">
	<h2>
		<?php echo $headline; ?>
	</h2>
	<div class="seperator"></div>
	<p>
		<?php echo $description; ?>
	</p>
	<div class="image-container">
		<img src="<?php echo $image; ?>" />
	</div>
</div>
