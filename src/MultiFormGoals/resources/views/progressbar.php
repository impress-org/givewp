<?php
/**
 * Multi-Form Goals block/shortcode template
 * Styles for this template are defined in 'blocks/multi-form-goals/common.scss'
 *
 */
?>

<div class="give-progress-bar-block">
	<div class="give-progress-bar-block__goal">
		<div class="give-progress-bar-block__progress">
			<?php $percent = ( $this->getTotal() / $this->getGoal() ) * 100; ?>
			<div class="give-progress-bar-block__progress-bar" style="width: <?php echo min( [ $percent, 100 ] ); ?>%; background: linear-gradient(180deg, <?php echo $this->getColor(); ?> 0%, <?php echo $this->getColor(); ?> 100%), linear-gradient(180deg, #fff 0%, #ccc 100%);"></div>
		</div>
	</div>
	<div class="give-progress-bar-block__stats">
		<div class="give-progress-bar-block__stat">
			<?php echo $this->getFormattedTotal(); ?>
		</div>
		<div class="give-progress-bar-block__stat">
			<?php echo $this->getDonationCount(); ?>
		</div>
		<div class="give-progress-bar-block__stat">
			<?php echo $this->getFormattedGoal(); ?>
		</div>
	</div>
</div>
