<?php /** @var Give\Framework\FieldsAPI\File $field */ ?>
<label>
	<?php echo $field->getLabel(); ?>
	<?php if ( $field->isRequired() ) : ?>
		<span class="give-required-indicator">
			<span aria-hidden="true">*</span>
			<span class="screen-reader-text"><?php esc_html_e( 'Required', 'give' ); ?></span>
		</span>
	<?php endif; ?>
	<input
		type="file"
		name="<?php echo $field->getName(); ?>"
		<?php echo $field->isRequired() ? 'required' : ''; ?>
		<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	>
</label>
