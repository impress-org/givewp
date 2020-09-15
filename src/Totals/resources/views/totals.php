<?php
/**
 * Totals block/shortcode template
 * Styles for this template are defined in 'blocks/total/common.scss'
 *
 */
?>

<div class="give-totals">
	<div class="give-totals__content">
		<div class="give-totals__message">
			<?php echo $this->getMessage(); ?>
		</div>
		<?php if ( ! empty( $this->getLinkUrl() ) && ! empty( $this->getLinkText() ) ) : ?>
		<div class="give-totals__link">
			<a href="<?php echo $this->getLinkUrl(); ?>" target="<?php echo $this->getLinkTarget(); ?>"><?php echo $this->getLinkText(); ?></a>
		</div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $this->getGoal() ) ) : ?>
	<div class="give-totals__goal">
		<div class="give-totals__progress">
			<?php $percent = ( $this->getTotal() / $this->getGoal() ) * 100; ?>
			<div class="give-totals__progress-bar" style="width: <?php echo $percent < 100 ? $percent : 100; ?>%"></div>
			<div class="give-totals__progress-text"> 
				<?php
					$total = $this->metric === 'revenue' ? $this->getFormattedTotal() : $this->getTotal();
					echo sprintf( __( '%1$s of %2$s', 'give' ), $total, $this->getFormattedGoal() );
				?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
