<?php
/** @since TBD Escape output. */
/** @var Give\Framework\FieldsAPI\File $field */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>

<input
    type="file"
    name="<?php
    echo esc_attr($field->getName()); ?><?php
    echo $field->getAllowMultiple() ? '[]' : ''; ?>"
    id="<?php
    echo esc_attr($fieldIdAttribute); ?>"
    <?php
    echo $field->getAllowMultiple() ? 'multiple' : ''; ?>
    <?php
    echo $field->isRequired() ? 'required' : ''; ?>
    <?php
    echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
