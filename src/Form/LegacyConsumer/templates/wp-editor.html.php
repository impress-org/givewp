<?php
/** @var Give\Framework\FieldsAPI\WPEditor $field */
wp_editor(
	$field->getDefaultValue(),
	$field->getName(),
	$field->getEditorConfig()
);

