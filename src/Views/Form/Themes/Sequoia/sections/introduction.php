<?php

// Get Theme options
$theme_options = give_get_meta( $form->ID, '_give_sequoia_form_theme_settings', true, null );

// Get headline and description
$headline    = $theme_options['introduction']['headline'];
$description = $theme_options['introduction']['description'];
$image       = $theme_options['introduction']['image'];
?>

<div class="give-section introduction">
	<img src="<?php echo $image; ?>" />
	<div class="heading">
		<h2 class="give-form-title"><?php echo $headline; ?></h2>
	</div>
	<div class="subheading text">
		<p><?php echo $description; ?></p>
	</div>
	<img src="<?php echo get_the_post_thumbnail_url( $form ); ?>" alt="">
</div>
