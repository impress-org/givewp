<input
	type="text"
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	placeholder="<?php echo $field->getLabel(); ?>"
	class="give-ffm-date give-ffm-datepicker-<?php echo $field->getName(); ?>"
	data-type="text"
	<?php /* TODO: Add the following values*/ ?>
	data-dateformat=""
	data-timeformat=""
	size="30"
	value="<?php $field->getDefaultValue(); ?>"
	<?php if ( $field->isRequired() ) : ?>
	data-required="true"
	required
	<?php endif; ?>
	@attributes
>
