<?php /** @var Give\Framework\FieldsAPI\File $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<input
	type="file"
	name="<?php echo $field->getName(); ?><?php echo $field->getAllowMultiple() ? '[]' : ''; ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	<?php echo $field->getAllowMultiple() ? 'multiple' : ''; ?>
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php echo include dirname( __FILE__ ) . '/conditional-visibility-attribute.html.php'; ?>
>
