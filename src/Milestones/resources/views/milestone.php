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
		<div class="give-milestone__progress">
			<div class="give-milestone__progress-bar" style="width: 60%"></div>
		</div>
		<div class="give-milestone__context">
			<span>
				$600 of $1,000
			</span>
			<span>
				15 Days to Go
			</span>
		</div>
	</div>
</div>
