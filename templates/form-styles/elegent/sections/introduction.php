<?php
$form_title = sprintf(
	'<h2 class="give-form-title">%1$s</h2>',
	get_the_title( $form->ID )
);
?>
<div class="give-section introduction">
	<div class="heading">
		<strong><?php echo $form_title; ?></strong>
	</div>
	<div class="headline"></div>
	<div class="subheading text">
		<p><?php echo get_the_excerpt( $form ); ?></p>
	</div>
	<img src="<?php echo get_the_post_thumbnail_url( $form ); ?>" alt="">
</div>
