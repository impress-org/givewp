<?php

// Get headline and description
$headline    = $this->themeOptions['introduction']['headline'];
$description = $this->themeOptions['introduction']['description'];
$image       = $this->themeOptions['introduction']['image'];
$color       = $this->themeOptions['introduction']['primary_color'];
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
