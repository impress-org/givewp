<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<?php /** @var string $typeAttribute */ ?>
<?php /** @var string $classAttribute */ ?>
<input
	type="<?php echo $typeAttribute; ?>"
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	class="<?php echo $classAttribute; ?>"
	value="<?php echo $field->getDefaultValue(); ?>"
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	data-required="<?php echo $field->isRequired(); ?>"
	@attributes
>
