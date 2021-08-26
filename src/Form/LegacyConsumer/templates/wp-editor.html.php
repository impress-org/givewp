<?php
/** @var Give\Framework\FieldsAPI\WpEditor $field */
wp_editor(
	$field->getDefaultValue(),
	$field->getName(),
	$field->getEditorConfig()
);

