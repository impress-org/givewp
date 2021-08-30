<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<textarea
	name="<?php echo $field->getName(); ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	placeholder="<?php echo $field->getPlaceholder() ?: ''; ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
></textarea>
