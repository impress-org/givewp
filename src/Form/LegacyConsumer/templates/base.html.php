<?php /** @var Give\Framework\FieldsAPI\Field $field */ ?>
<?php /** @var string $typeAttribute */ ?>
<input
	type="<?php echo $typeAttribute; ?>"
	name="<?php echo $field->getName(); ?>"
	placeholder="<?php echo $field->getLabel(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	value="<?php echo $field->getDefaultValue(); ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
