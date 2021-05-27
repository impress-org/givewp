<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<?php echo $field->getLabel(); ?>
<?php if ( $field->isRequired() ) : ?>
  &nbsp<span class="give-required-indicator">*</span>
<?php endif; ?>
