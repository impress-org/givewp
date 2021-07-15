<?php /** @var Give\Framework\FieldsAPI\Field $field */ ?>
<?php echo $field->getLabel(); ?>
<?php if ( $field->isRequired() ) : ?>
<span class="give-required-indicator"> *</span>
<?php endif; ?>
