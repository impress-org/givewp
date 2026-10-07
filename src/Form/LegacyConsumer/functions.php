<?php

use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Facades\Factory;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param string $type
 * @param string $name
 *
 * @return Node
 */
function give_field($type, $name)
{
    return Factory::make($type, $name);
}
