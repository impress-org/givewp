<?php /** @var Give\Framework\FieldsAPI\Field $field */ ?>
<?php echo $field->getLabel(); ?>
<?php if ( $field->isRequired() ) : ?>
	<span class="give-required-indicator">
		<span aria-hidden="true">*</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Required', 'give' ); ?></span>
	</span>
<?php endif; ?>
<?php
echo ( $helpText = $field->getHelpText() ) ?
	Give()->tooltips->render_help( $helpText ) :
	'';
?>
