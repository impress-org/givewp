<?php
/**
 * Totals block/shortcode template
 * Styles for this template are defined in 'blocks/total/common.scss'
 *
 */
?>

<div class="give-total">
	<div class="give-total__section">
		<div class="give-total__message">
			<?php echo $this->getMessage(); ?>
		</div>
		<?php if ( ! empty( $this->getLinkUrl() ) && ! empty( $this->getLinkText() ) ) : ?>
			<div class="give-total__link">
				<a href="<?php echo $this->getLinkUrl(); ?>" target="<?php echo $this->getLinkTarget(); ?>"><?php echo $this->getLinkText(); ?></a>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $this->getGoal() ) ) : ?>
		<div class="give-total__progress">
			<?php $percent = ( $this->getTotal() / $this->getGoal() ) * 100; ?>
			<div class="give-total__progress-bar" style="width: <?php echo $percent < 100 ? $percent : 100; ?>%"></div>
		</div>
		<?php endif; ?>
		<div class="give-total__context">
			<span> 
				<?php echo $this->getFormattedTotal(); ?>
				<?php
				if ( ! empty( $this->getGoal() ) ) {
					echo __( ' of ', 'give' );
					echo $this->getFormattedGoal();
				}
				?>
			</span>
		</div>
	</div>
</div>
