<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<?php /** @var string|null $typeAttribute */ ?>
<input
	type="<?php echo $typeAttribute ?: 'text'; ?>"
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	class="give-input<?php echo $field->isRequired() ? ' required' : ''; ?>"
	value="<?php echo $field->getDefaultValue(); ?>"
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php $field->isRequired() ? 'required' : ''; ?>
	data-required="<?php echo $field->isRequired(); ?>"
	@attributes
>
