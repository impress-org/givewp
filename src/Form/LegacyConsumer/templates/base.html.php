<?php
/** @since TBD Escape output. */
/** @var \Give\Framework\FieldsAPI\Text $field */ ?>
<?php
/** @var string $typeAttribute */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>
<input
    type="<?php
    echo esc_attr($typeAttribute); ?>"
    name="<?php
    echo esc_attr($field->getName()); ?>"
    placeholder="<?php
    echo esc_attr($field->getPlaceholder()); ?>"
    id="<?php
    echo esc_attr($fieldIdAttribute); ?>"
    value="<?php
    echo esc_attr($field->getDefaultValue()); ?>"
    <?php
    echo $field->isRequired() ? 'required' : ''; ?>
    <?php
    echo $field->isReadOnly() ? 'readonly' : ''; ?>

    <?php
    if( method_exists( $field, 'getMaxLength' ) ) {
        echo ($maxLength = $field->getMaxLength()) ? 'maxlength="' . (int) $maxLength . '"' : '';
    }
    ?>
>
