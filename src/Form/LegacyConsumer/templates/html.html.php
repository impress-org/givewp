<?php

if (!defined('ABSPATH')) {
    exit;
}

/** @var Give\Framework\FieldsAPI\Html $field */
echo do_shortcode($field->getHtml());
