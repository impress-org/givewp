<?php

use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;

$formInfo = get_post( FrontendFormTemplateUtils::getFormId() );

// Get headline and description
$headline    = ! empty( $this->templateOptions['introduction']['headline'] ) ? $this->templateOptions['introduction']['headline'] : $formInfo->post_title;
$description = ! empty( $this->templateOptions['introduction']['description'] ) ? $this->templateOptions['introduction']['description'] : $formInfo->post_excerpt;
$image       = ! empty( $this->templateOptions['introduction']['image'] ) ? $this->templateOptions['introduction']['image'] : get_the_post_thumbnail_url( FrontendFormTemplateUtils::getFormId() );
?>

<div class="give-section introduction">
	<h2 class="headline">
		<?php
		if ( empty( $headline ) || $headline === __( 'Auto Draft' ) ) {
			_e( 'Support Our Cause', 'give' );
		} else {
			esc_html_e( $headline, 'give' );
		}
		?>
	</h2>
	<?php if ( ! empty( $description ) ) : ?>
		<div class="seperator"></div>
		<p class="description">
			<?php esc_html_e( $description, 'give' ); ?>
		</p>
	<?php endif; ?>
	<?php if ( ! empty( $image ) ) : ?>
		<div class="image">
			<img src="<?php esc_html_e( $image, 'give' ); ?>" />
		</div>
	<?php endif; ?>

	<?php
		require 'income-stats.php';
		require 'progress-bar.php';
	?>
