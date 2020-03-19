<?php

// Get headline and description
$headline    = $this->theme_options['introduction']['headline'];
$description = $this->theme_options['introduction']['description'];
$image       = $this->theme_options['introduction']['image'];
$color       = $this->theme_options['introduction']['primary_color'];
?>

<div class="give-section introduction">
	<h2>
		<?php echo $headline; ?>
	</h2>
	<div class="seperator" style="background: <?php echo $color; ?>"></div>
	<p>
		<?php echo $description; ?>
	</p>
	<div class="image-container">
		<img src="<?php echo $image; ?>" />
	</div>
</div>
