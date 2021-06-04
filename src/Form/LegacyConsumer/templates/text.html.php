<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<input
	type="text"
	class="give-input<?php echo $field->isRequired() ? ' required' : ''; ?>"
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	@attributes
>
