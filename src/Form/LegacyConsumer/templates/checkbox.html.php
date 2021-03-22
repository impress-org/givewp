<input
	type="checkbox"
	class="give-input required"
	name="give_<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	<?php if ( $field->isRequired() ) : ?>    
		required="" aria-required="true"
	<?php endif; ?>
	tabindex="1"
	@attributes
	/>

