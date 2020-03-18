<?php

// Get headline and description
$headline    = $this->theme_options['introduction']['headline'];
$description = $this->theme_options['introduction']['description'];
$image       = $this->theme_options['introduction']['image'];
?>

<div class="give-section introduction">
	<img src="<?php echo $image; ?>" />
	<div class="heading">
		<h2 class="give-form-title"><?php echo $headline; ?></h2>
	</div>
	<div class="subheading text">
		<p><?php echo $description; ?></p>
	</div>
</div>
