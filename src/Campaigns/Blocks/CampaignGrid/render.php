<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var array $attributes
 */
?>
<div data-givewp-campaign-grid data-attributes="<?= esc_attr(json_encode($attributes)) ?>"></div>
