<?php /** @var Give\Framework\FieldsAPI\Checkbox $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<?php /** @var string $visibilityConditionsAttribute */ ?>

<?php if ( $field->hasOptions() ): ?>
	<fieldset <?php echo $visibilityConditionsAttribute ?>>
		<legend class="screen-reader-text">
			<?php include plugin_dir_path( __FILE__ ) . 'label-content.html.php'; ?>
		</legend>
		<div class="give-label" aria-hidden="true">
			<?php include plugin_dir_path( __FILE__ ) . 'label-content.html.php'; ?>
		</div>
		<?php foreach ( $field->getOptions() as $index => $option ) : ?>
			<?php $id = $fieldIdAttribute . '-' . $index; ?>
			<label class="give-label" for="<?php echo $id; ?>">
				<input
					type="checkbox"
					name="<?php echo $field->getName(); ?>[]"
					id="<?php echo $id; ?>"
					<?php echo in_array( $option->getValue(), $field->getDefaultValue() ) ? 'checked' : ''; ?>
					value="<?php echo $option->getValue(); ?>"
				>
				<?php echo $option->getLabel() ?: $option->getValue(); ?>
			</label>
		<?php endforeach; ?>
	</fieldset>
<?php else: ?>
	<label class="give-label">
		<input
			type="checkbox"
			name="<?php echo $field->getName(); ?>"
			<?php echo $field->isRequired() ? 'required' : ''; ?>
			<?php echo $field->isChecked() ? 'checked' : ''; ?>
			<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
			<?php include plugin_dir_path( __FILE__ ) . 'conditional-visibility-attribute.html.php'; ?>
		>
		<?php echo $field->getLabel(); ?>
	</label>
<?php endif; ?>
