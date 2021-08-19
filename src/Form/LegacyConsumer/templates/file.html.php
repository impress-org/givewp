<?php /** @var Give\Framework\FieldsAPI\File $field */ ?>
<label>
	<?php echo $field->getLabel(); ?>
	<input
		type="file"
		name="<?php echo $field->getName(); ?>"
		<?php echo $field->isRequired() ? 'required' : ''; ?>
		<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	>
</label>
