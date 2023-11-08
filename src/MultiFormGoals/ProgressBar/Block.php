<?php

namespace Give\MultiFormGoals\ProgressBar;

use Give\MultiFormGoals\ProgressBar\Model as ProgressBar;

class Block
{

    /**
     * Registers Multi-Form Goals block
     *
     * @since 2.9.0
     **/
    public function addBlock()
    {
        register_block_type(
            'give/progress-bar',
            [
                'render_callback' => [$this, 'renderCallback'],
                'attributes' => [
                    'ids' => [
                        'type' => 'array',
                        'default' => [],
                    ],
                    'categories' => [
                        'type' => 'array',
                        'default' => [],
                    ],
                    'tags' => [
                        'type' => 'array',
                        'default' => [],
                    ],
                    'goal' => [
                        'type' => 'string',
                        'default' => '1000',
                    ],
                    'enddate' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'color' => [
                        'type' => 'string',
                        'default' => '#28c77b',
                    ],
                ],

            ]
        );
    }

    /**
     * Returns Progress Bar block markup
     *
     * @since 3.1.0 Use static function on array_map callback to pass the id as reference for _give_redirect_form_id to prevent warnings on PHP 8.0.1 or plus
     * @since 2.9.0
     **/
    public function renderCallback($attributes)
    {
        $progressBar = new ProgressBar(
            [
                'ids' => array_map(
                    static function ($id) {
                        _give_redirect_form_id($id);

                        return $id;
                    },
                    $attributes['ids']
                ),
                'tags' => $attributes['tags'],
                'categories' => $attributes['categories'],
                'goal' => $attributes['goal'],
                'enddate' => $attributes['enddate'],
                'color' => $attributes['color'],
            ]
        );

        return $progressBar->getOutput();
    }

    public function localizeAssets()
    {
        $defaultColorPalette = [
            [
                'name' => __('Red', 'give'),
                'color' => '#dd3333',
            ],
            [
                'name' => __('Orange', 'give'),
                'color' => '#dd9933',
            ],
            [
                'name' => __('Green', 'give'),
                'color' => '#28C77B',
            ],
            [
                'name' => __('Blue', 'give'),
                'color' => '#1e73be',
            ],
            [
                'name' => __('Purple', 'give'),
                'color' => '#8224e3',
            ],
            [
                'name' => __('Grey', 'give'),
                'color' => '#777777',
            ],
        ];
        $editorColorPalette = get_theme_support('editor-color-palette'); // Return value is in a nested array.

        wp_localize_script(
            'give-blocks-js',
            'giveProgressBarThemeSupport',
            [
                'editorColorPalette' => is_array($editorColorPalette) ? array_shift(
                    $editorColorPalette
                ) : $defaultColorPalette,
            ]
        );
    }
}
