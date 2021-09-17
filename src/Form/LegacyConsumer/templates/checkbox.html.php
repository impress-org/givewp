<?php /** @var Give\Framework\FieldsAPI\Checkbox $field */ ?>
<label class="give-label">
	<input
		type="checkbox"
		name="<?php echo $field->getName(); ?>"
		<?php echo $field->isRequired() ? 'required' : ''; ?>
		<?php echo $field->isChecked() ? 'checked' : ''; ?>
		<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
		<?php echo $field->getDefaultValue() ? "value=\"{$field->getDefaultValue()}\"" : ''; ?>
		<?php include dirname( __FILE__ ) . '/conditional-visibility-attribute.html.php'; ?>
	>
	<?php echo $field->getLabel(); ?>
</label>
