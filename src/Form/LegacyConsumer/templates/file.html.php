<?php /** @var Give\Framework\FieldsAPI\File $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<?php /** @var string $visibilityConditionsAttribute */ ?>

<input
	type="file"
	name="<?php echo $field->getName(); ?><?php echo $field->getAllowMultiple() ? '[]' : ''; ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	<?php echo $field->getAllowMultiple() ? 'multiple' : ''; ?>
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php echo $visibilityConditionsAttribute ?>
>
