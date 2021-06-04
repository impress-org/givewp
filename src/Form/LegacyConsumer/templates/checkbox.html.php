<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<input
	type="checkbox"
	class="give-input<?php if ( $field->isRequired() ) echo ' required'; ?>"
	name="give_<?= $field->getName() ?>"
	id="give-<?= $field->getName() ?>"
	<?php if ( $field->isRequired() ) : ?>
	required
	<?php endif; ?>
	@attributes
/>

