<?php

namespace Give\Campaigns\AsyncData;

/**
 * @unreleased
 */
class AsyncDataHelpers
{
    /**
     * @unreleased
     */
    public static function getSkeletonPlaceholder($width = '100%', $height = '0.7rem'): string
    {
        return '<span class="give-skeleton js-give-async-data" style="width: ' . esc_attr($width) . '; height: ' . esc_attr($height) . ';"></span>';
    }
}
