<?php /** @var Give\Framework\FieldsAPI\File $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<input
	type="file"
	name="<?php echo $field->getName(); ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
