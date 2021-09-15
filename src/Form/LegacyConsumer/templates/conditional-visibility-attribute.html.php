<?php
/* @var \Give\Framework\FieldsAPI\Field $field */
if( $conditions = $field->getVisibilityConditions() ) {
	$conditions = esc_attr( json_encode( $conditions ) );
	echo "data-field-visibility-conditions=\"$conditions\"";
}
