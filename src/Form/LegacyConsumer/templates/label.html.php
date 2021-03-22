<label class="give-label" for="give-<?php echo $field->getName(); ?>">
	<?php echo $field->getLabel(); ?>
	<?php if ( $field->isRequired() ) : ?>
	&nbsp;<span class="give-required-indicator">*</span>
	<?php endif; ?>
</label>
