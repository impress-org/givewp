<?php
/**
 * Milestone block/shortcode template
 * Styles for this template are defined in 'blocks/milestone/common.scss'
 *
 * @param array $attributes[
 *  'title' => 'string',
 * ]
 */
?>

<div class="give-milestone">
	<div class="give-milestone__section">
		<?php if ( ! empty( $attributes['image'] ) ) : ?>
		<div class="give-milestone__image">
			<img src="<?php echo $attributes['image']; ?>"/>
		</div>
		<?php endif; ?>
	</div>
	<div class="give-milestone__section">
		<div class="give-milestone__title">
			<?php echo $attributes['title']; ?>
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
