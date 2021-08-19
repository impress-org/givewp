<?php /** @var Give\Framework\FieldsAPI\Checkbox $field */ ?>
<?php /** @var string $typeAttribute */ ?>
<label class="give-label">
	<input
		type="checkbox"
		name="<?php echo $field->getName(); ?>"
		<?php echo $field->isRequired() ? 'required' : ''; ?>
		<?php echo $field->isChecked() ? 'checked' : ''; ?>
		<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	>
	<?php echo $field->getLabel(); ?>
</label>
