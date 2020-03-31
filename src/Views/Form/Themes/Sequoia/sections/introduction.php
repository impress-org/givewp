<?php

use function Give\Helpers\Form\Theme\Utils\Frontend\getFormId;

$formInfo = get_post( getFormId() );

// Get headline and description
$headline    = ! empty( $this->themeOptions['introduction']['headline'] ) ? $this->themeOptions['introduction']['headline'] : $formInfo->post_title;
$description = ! empty( $this->themeOptions['introduction']['description'] ) ? $this->themeOptions['introduction']['description'] : $formInfo->post_excerpt;
$image       = ! empty( $this->themeOptions['introduction']['image'] ) ? $this->themeOptions['introduction']['image'] : $formInfo->post_thumbnail;
?>

<div class="give-section introduction">
	<h2 class="headline">
		<?php echo $headline; ?>
	</h2>
	<?php if ( ! empty( $description ) ) : ?>
		<div class="seperator"></div>
		<p class="description">
			<?php echo $description; ?>
		</p>
	<?php endif; ?>
	<?php if ( ! empty( $image ) ) : ?>
		<div class="image">
			<img src="<?php echo $image; ?>" />
		</div>
	<?php endif; ?>

	<?php
		require 'income-stats.php';
		require 'progress-bar.php';
	?>

</div>
