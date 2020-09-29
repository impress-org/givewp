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
		?>
		<div class="give-multi-form-goal-block__content">
			<div class="give-multi-form-goal-block__image">
				<img src="<?php echo $this->getImageSrc(); ?>" />
			</div>
			<div class="give-multi-form-goal-block__text">
				<div class="give-multi-form-goal-block__heading">
					<?php echo $this->getHeading(); ?>
				</div>
				<div class="give-multi-form-goal-block__summary">
					<?php echo $this->getSummary(); ?>
				</div>
			</div>
		</div>
		<?php echo $this->getProgressBarOutput(); ?>
	<?php } ?>
</div>
