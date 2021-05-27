<?php /* Fieldsets + legends are terrible to style, so we just use the semantic markup and style something else. */ ?>
<fieldset>
	<legend class="screen-reader-text">
		<?php include plugin_dir_path( __FILE__ ) . '/label-content.html.php'; ?>
	</legend>
	<div class="give-label" aria-hidden="true">
		<?php include plugin_dir_path( __FILE__ ) . '/label-content.html.php'; ?>
	</div>
	<?php foreach ( $field->getOptions() as $option => $label ) : ?>
	<label>
		<input
			type="radio"
			name="give_<?php echo $field->getName(); ?>"
			<?php if ( $field->isRequired() ) : ?>
			required
			<?php endif; ?>
			@attributes
		/>
		<?php echo $label; ?>
	</label>
	<?php endforeach; ?>
</fieldset>
