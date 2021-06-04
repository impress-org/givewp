<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<input
	type="text"
	class="give-input<?php if ( $field->isRequired() ) echo ' required'; ?>"
	name="<?= $field->getName() ?>"
	id="give-<?= $field->getName() ?>"
	<?php if ( $field->isRequired() ) : ?>
    required
	<?php endif; ?>
	@attributes
>
