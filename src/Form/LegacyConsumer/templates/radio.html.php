<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<?php /** @var string $classAttribute */ ?>
<?php /* Fieldsets + legends are terrible to style, so we just use the semantic markup and style something else. */ ?>
<fieldset>
	<legend class="screen-reader-text">
		<?php include plugin_dir_path( __FILE__ ) . '/label-content.html.php'; ?>
	</legend>
	<div class="give-label" aria-hidden="true">
		<?php include plugin_dir_path( __FILE__ ) . '/label-content.html.php'; ?>
	</div>
	<?php foreach ( $field->getOptions() as $option ) : ?>
	<label>
		<input
			type="radio"
			name="give_<?php echo $field->getName(); ?>"
			class="<?php echo $classAttribute; ?>"
			<?php echo $field->isRequired() ? 'required' : ''; ?>
			<?php echo $option->getValue() === $field->getDefaultValue() ? 'checked' : ''; ?>
			value="<?php echo $option->getValue(); ?>"
			@attributes
		>
		<?php echo $option->getLabel() ?: $option->getValue(); ?>
	</label>
	<?php endforeach; ?>
</fieldset>
