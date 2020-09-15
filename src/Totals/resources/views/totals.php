<?php
/**
 * Totals block/shortcode template
 * Styles for this template are defined in 'blocks/total/common.scss'
 *
 */
?>

<div class="give-totals">
	<?php if ( ! empty( $this->getMessage() ) || ! empty( $this->getLinkUrl() ) ) : ?>
	<p class="give-totals__message">
		<?php echo $this->getMessage(); ?>
		<?php if ( ! empty( $this->getLinkUrl() ) && ! empty( $this->getLinkText() ) ) : ?>
		<a href="<?php echo $this->getLinkUrl(); ?>" target="<?php echo $this->getLinkTarget(); ?>"><?php echo $this->getLinkText(); ?></a>
		<?php endif; ?>
	</p>
	<?php endif; ?>
	<?php if ( ! empty( $this->getGoal() ) ) : ?>
	<div class="give-totals__goal">
		<div class="give-totals__progress">
			<?php $percent = ( $this->getTotal() / $this->getGoal() ) * 100; ?>
			<div class="give-totals__progress-bar" style="width: <?php echo $percent < 100 ? $percent : 100; ?>%; background: linear-gradient(180deg, <?php echo $this->getColor(); ?> 0%, <?php echo $this->getColor(); ?> 100%), linear-gradient(180deg, #fff 0%, #ccc 100%);"></div>
		</div>
		<p class="give-totals__progress-text"> 
			<?php
				$total = $this->metric === 'revenue' ? $this->getFormattedTotal() : $this->getTotal();
				echo sprintf( __( '%1$s of %2$s', 'give' ), $total, $this->getFormattedGoal() );
			?>
		</p>
	</div>
	<?php endif; ?>
</div>
