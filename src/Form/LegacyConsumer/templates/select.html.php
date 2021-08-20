<?php /** @var Give\Framework\FieldsAPI\Select $field */ ?>
<select
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	<?php echo $field->getAllowMultiple() ? 'multiple' : ''; ?>
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
	<?php
	if ( $placeholder = $field->getPlaceholder() ) {
		printf(
			'<option value="" disabled %2$s>%1$s</option>',
			$placeholder,
			$field->getDefaultValue() ? '' : 'selected'
		);
	}
	?>
	<?php foreach ( $field->getOptions() as $option ) : ?>
		<?php $value = $option->getValue(); ?>
		<?php $label = $option->getLabel(); ?>
		<?php $default = $field->getDefaultValue() === $option->getValue(); ?>
		<option
			<?php echo "value=\"{$value}\""; ?>
			<?php echo $default ? 'selected' : ''; ?>
		>
			<?php echo $label ?: $value; ?>
		</option>
	<?php endforeach; ?>
</select>
