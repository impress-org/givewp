<?php
global $post;

// Get headline and description
$headline    = ! empty( $this->themeOptions['introduction']['headline'] ) ? $this->themeOptions['introduction']['headline'] : $post->post_title;
$description = ! empty( $this->themeOptions['introduction']['description'] ) ? $this->themeOptions['introduction']['description'] : $post->post_excerpt;
$image       = ! empty( $this->themeOptions['introduction']['image'] ) ? $this->themeOptions['introduction']['image'] : $post->post_thumbnail;
?>

<div class="give-section introduction">
	<h2>
		<?php echo $headline; ?>
	</h2>
	<?php if ( ! empty( $description ) ) : ?>
		<div class="seperator"></div>
		<p>
			<?php echo $description; ?>
		</p>
	<?php endif; ?>
	<?php if ( ! empty( $image ) ) : ?>
		<div class="image-container">
			<img src="<?php echo $image; ?>" />
		</div>
	<?php endif; ?>

	<?php
		require 'income-stats.php';
		require 'progress-bar.php';
	?>

</div>
