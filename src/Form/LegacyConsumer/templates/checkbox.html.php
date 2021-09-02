<?php /** @var Give\Framework\FieldsAPI\Checkbox $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<input
	type="checkbox"
	id="<?php echo $fieldIdAttribute; ?>"
	name="<?php echo $field->getName(); ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isChecked() ? 'checked' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
<label class="give-label" for="<?php echo $fieldIdAttribute; ?>">
	<?php echo $field->getLabel(); ?>
</label>
