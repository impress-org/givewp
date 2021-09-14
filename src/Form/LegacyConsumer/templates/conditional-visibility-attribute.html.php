<?php
/* @var \Give\Framework\FieldsAPI\Field $field */
echo ( $conditions = esc_attr( json_encode( $field->getVisibilityConditions() ) ) ) ? "data-field-visibility-conditions=\"$conditions\"" : '';
