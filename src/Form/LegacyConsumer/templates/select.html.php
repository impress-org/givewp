<?php /** @var Give\Framework\FieldsAPI\Select $field */ ?>
<select
	name="<?php echo $field->getName(); ?><?php echo $field->getAllowMultiple() ? '[]' : ''; ?>"
	id="give-<?php echo $field->getName(); ?>"
	<?php echo $field->getAllowMultiple() ? 'multiple' : ''; ?>
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
	<?php
	if ( $placeholder = $field->getPlaceholder() ) {
		printf(
			'<option value="" %2$s>%1$s</option>',
			$placeholder,
			$field->isRequired() ? 'disabled' : ''
		);
	}
	?>
	<?php foreach ( $field->getOptions() as $option ) : ?>
		<?php $value = esc_attr( $option->getValue() ); ?>
		<?php $label = $option->getLabel(); ?>
		<?php $default = $field->getDefaultValue() === $option->getValue(); ?>
		<option
			<?php echo $label ? "value=\"$value\"" : ''; ?>
			<?php echo $default ? 'selected' : ''; ?>
		>
			<?php echo $label ?: $value; ?>
		</option>
	<?php endforeach; ?>
</select>
