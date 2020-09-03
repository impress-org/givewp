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
		<?php if ( ! empty( $this->getGoal() ) ) : ?>
		<div class="give-milestone__progress">
			<?php $percent = ( $this->getEarnings() / $this->getGoal() ) * 100; ?>
			<div class="give-milestone__progress-bar" style="width: <?php echo $percent < 100 ? $percent : 100; ?>%"></div>
		</div>
		<?php endif; ?>
		<div class="give-milestone__context">
			<span> 
				<?php
				echo give_currency_filter(
					give_format_amount(
						$this->getEarnings(),
						[
							'sanitize' => false,
							'decimal'  => false,
						]
					)
				);
				?>
				 of 
				<?php
				echo give_currency_filter(
					give_format_amount(
						$this->getGoal(),
						[
							'sanitize' => false,
							'decimal'  => false,
						]
					)
				);
				?>
			</span>
			<?php if ( ! empty( $this->getDeadline() ) ) : ?>
			<span>
				<?php echo $this->getDaysToGo(); ?> Days To Go
			</span>
			<?php endif; ?>
		</div>
	</div>
</div>
