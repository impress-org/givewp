<?php

// Get headline and description
$headline    = $this->themeOptions['introduction']['headline'];
$description = $this->themeOptions['introduction']['description'];
$image       = $this->themeOptions['introduction']['image'];
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
