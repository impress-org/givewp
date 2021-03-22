<input
	type="text"
	class="give-input required"
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	placeholder="<?php echo $field->getLabel(); ?>"
	<?php if ( $field->isRequired() ) : ?>    
		required="" aria-required="true"
	<?php endif; ?>
	tabindex="1"
	@attributes
	/>
