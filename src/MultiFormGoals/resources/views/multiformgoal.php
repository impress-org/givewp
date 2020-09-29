<?php
/**
 * Multi-Form Goals block/shortcode template
 * Styles for this template are defined in 'blocks/multi-form-goals/common.scss'
 *
 */
?>

<div class="give-multi-form-goal-block">
	<?php
	if ( ! empty( $this->innerBlocks ) ) {
		echo $this->innerBlocks;
	} else {
		echo $this->getProgressBarOutput();
	}
	?>
</div>
