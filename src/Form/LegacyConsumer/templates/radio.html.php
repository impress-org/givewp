<?php /** @var Give\Framework\FieldsAPI\Radio $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<?php /** @var string $visibilityConditionsAttribute */ ?>
<?php /* Fieldsets + legends are terrible to style, so we just use the semantic markup and style something else. */ ?>
<fieldset <?php echo $visibilityConditionsAttribute ?>>
	<legend class="screen-reader-text">
		<?php include plugin_dir_path( __FILE__ ) . 'label-content.html.php'; ?>
	</legend>
	<div class="give-label" aria-hidden="true">
		<?php include plugin_dir_path( __FILE__ ) . 'label-content.html.php'; ?>
	</div>
	<?php foreach ( $field->getOptions() as $index => $option ) : ?>
		<?php $id = $fieldIdAttribute . '-' . $index; ?>
		<label for="<?php echo $id; ?>">
			<input
				type="radio"
				name="<?php echo $field->getName(); ?>"
				id="<?php echo $id; ?>"
				<?php echo $field->isRequired() ? 'required' : ''; ?>
				<?php echo $option->getValue() === $field->getDefaultValue() ? 'checked' : ''; ?>
				value="<?php echo $option->getValue(); ?>"
			>
			<?php echo $option->getLabel() ?: $option->getValue(); ?>
		</label>
	<?php endforeach; ?>
</fieldset>
