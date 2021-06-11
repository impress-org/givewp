<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<?php /** @var string $classAttribute */ ?>
<?php /** @var array $selectedOptions */ ?>
<select
	name="give_<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	class="<?php echo $classAttribute; ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	@attributes
>
	<?php foreach ( $field->getOptions() as $option ) : ?>
		<?php $label = $option->getLabel(); ?>
		<?php $value = $option->getValue(); ?>
	<option
		<?php echo $label ? "value={$value}" : ''; ?>
		<?php echo in_array( $value, $selectedOptions, true ) ? 'selected' : ''; ?>
	>
		<?php echo $label ?: $value; ?>
	</option>
	<?php endforeach; ?>
</select>
