<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<textarea
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	placeholder="<?php echo $field->getPlaceholder() ?: ''; ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
></textarea>
