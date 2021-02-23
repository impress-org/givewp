<select
	name="give_<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	class="give-input required"
	placeholder="<?php echo $field->getLabel(); ?>"
	<?php if ( $field->isRequired() ) : ?>    
		required="" aria-required="true"
	<?php endif; ?>
	tabindex="1"
	@attributes
>
	<?php foreach ( $field->getOptions() as $key => $value ) : ?>
	<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
