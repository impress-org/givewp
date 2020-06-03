
<div class="form-footer">
	<div class="navigator-tracker">
		<div class="step-tracker current" data-step="0"></div>
		<div class="step-tracker" data-step="1"></div>
		<div class="step-tracker" data-step="2"></div>
	</div>
	<div class="secure-notice">
	<?php if ( is_ssl() ) : ?>
		<i class="fas fa-lock"></i>
		<?php _e( 'Secure Donation', 'give' ); ?>
	<?php else : ?>
		<i class="fas fa-exclamation-triangle"></i>
		<?php _e( 'Insecure Donation', 'give' ); ?>
	<?php endif; ?>
	</div>
</div>
