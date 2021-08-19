<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<label>
	<?php echo $field->getLabel(); ?>
	<?php if ( $field->isRequired() ) : ?>
		<span class="give-required-indicator">
			<span aria-hidden="true">*</span>
			<span class="screen-reader-text"><?php esc_html_e( 'Required', 'give' ); ?></span>
		</span>
	<?php endif; ?>
	<textarea
		name="give_<?php echo $field->getName(); ?>"
		<?php echo $field->isRequired() ? 'required' : ''; ?>
		<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	></textarea>
</label>
