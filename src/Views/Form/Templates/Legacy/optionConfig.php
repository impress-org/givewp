<?php

use Give\Form\Template\Options;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables; this file is included inside a function, so they are not globals.

$price_placeholder = give_format_decimal('1.00', false, false);

return [
    'display_settings' => [
        'name' => __('Form Display', 'give'),
        'desc' => __('Step description will show up here if any', 'give'),
        'fields' => [
            Options::getDonationLevelsDisplayStyleField(),
            Options::getDisplayOptionsField(
                [
                    'modal' => __('Modal', 'give'),
                    'reveal' => __(
                        'Reveal',
                        'give'
                    ),
                ]
            ),
            Options::getContinueToDonationFormField(),
            Options::getCheckoutLabelField(),
            Options::getFloatLabelsField(),
            Options::getDisplayContentField(),
            Options::getContentPlacementField(),
            Options::getFormContentField(),
        ],
    ],
];
