<?php
/**
 * Milestone block/shortcode template
 * Styles for this template are defined in 'blocks/milestone/common.scss'
 *
 */
?>

<div class="give-milestone">
	<div class="give-milestone__section">
		<?php if ( ! empty( $this->getImage() ) ) : ?>
		<div class="give-milestone__image">
			<img src="<?php echo $this->getImage(); ?>"/>
		</div>
		<?php endif; ?>
	</div>
	<div class="give-milestone__section">
		<div class="give-milestone__title">
			<?php echo $this->getTitle(); ?>
		</div>
		<div class="give-milestone__description">
			<?php echo $this->getDescription(); ?>
		</div>
		<?php if ( ! empty( $this->getLinkUrl() ) && ! empty( $this->getLinkText() ) ) : ?>
			<div class="give-milestone__link">
				<a href="<?php echo $this->getLinkUrl(); ?>" target="<?php echo $this->getLinkTarget(); ?>"><?php echo $this->getLinkText(); ?></a>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $this->getGoal() ) ) : ?>
		<div class="give-milestone__progress">
			<?php $percent = ( $this->getTotal() / $this->getGoal() ) * 100; ?>
			<div class="give-milestone__progress-bar" style="width: <?php echo $percent < 100 ? $percent : 100; ?>%"></div>
		</div>
		<?php endif; ?>
		<div class="give-milestone__context">
			<span> 
				<?php echo $this->getFormattedTotal(); ?>
				<?php
				if ( ! empty( $this->getGoal() ) ) {
					echo __( ' of ', 'give' );
					echo $this->getFormattedGoal();
				}
				?>
			</span>
			<?php if ( ! empty( $this->getDeadline() ) ) : ?>
			<span>
				<?php
					$days = $this->getDaysToGo();
				if ( $days > 0 ) {
					$format = _n( '%s Day To Go', '%s Days To Go', $days, 'give' );
					echo sprintf( $format, $days );
				}
				?>
			</span>
			<?php endif; ?>
		</div>
	</div>
</div>
