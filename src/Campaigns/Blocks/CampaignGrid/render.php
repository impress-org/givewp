<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @since TBD Replace short echo tags with escaped echo.
 *
 * @var array $attributes
 */
?>
<div data-givewp-campaign-grid data-attributes="<?php echo esc_attr(json_encode($attributes)); ?>"></div>
