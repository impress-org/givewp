<?php /** @var Give\Framework\FieldsAPI\Checkbox $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<label class="give-label" for="<?php echo $fieldIdAttribute; ?>">
	<input
		type="checkbox"
		name="<?php echo $field->getName(); ?>"
		id="<?php echo $fieldIdAttribute; ?>"
		<?php echo $field->isRequired() ? 'required' : ''; ?>
		<?php echo $field->isChecked() ? 'checked' : ''; ?>
		<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	>
	<?php echo $field->getLabel(); ?>
</label>
