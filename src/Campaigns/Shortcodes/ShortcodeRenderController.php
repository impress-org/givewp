<?php

namespace Give\Campaigns\Shortcodes;

/**
 * Controller for rendering blocks in shortcode context.
 *
 * This controller ensures that WordPress block functions like get_block_wrapper_attributes()
 * work properly when rendering blocks through shortcodes by setting up the proper
 * block context that WordPress expects.
 *
 * @since 4.7.0
 */
class ShortcodeRenderController
{
    /**
     * Renders a block file with proper WordPress block context.
     *
     * This method temporarily sets up the block context that WordPress block functions
     * expect, renders the block file, then restores the previous context.
     *
     * @since 4.7.0
     *
     * @param string $renderFilePath The absolute path to the block render file
     * @param string $blockName      The registered block name (e.g., 'givewp/campaign-stats-block')
     * @param array  $attributes     The block attributes
     * @param array  $extraVars      Optional. Additional variables to make available in the render file
     *
     * @return string The rendered block HTML
     */
    public static function renderWithBlockContext(
        string $renderFilePath,
        string $blockName,
        array $attributes,
        array $extraVars = []
    ): string {
        // Create a proper parsed block structure
        $parsed_block = [
            'blockName' => $blockName,
            'attrs' => $attributes,
        ];

        // Create a proper WP_Block instance if the block type is registered
        $block_type = \WP_Block_Type_Registry::get_instance()->get_registered($blockName);
        if ($block_type) {
            $block = new \WP_Block($parsed_block, []);
        } else {
            // Fallback to mock object if block type isn't registered
            $block = (object) [
                'blockName' => $blockName,
                'attributes' => $attributes,
            ];
        }

        // Set the block context for WordPress block supports (needed for get_block_wrapper_attributes)
        $previous_block_to_render = \WP_Block_Supports::$block_to_render;
        \WP_Block_Supports::$block_to_render = $parsed_block;

        // Extract extra variables to make them available in the render file
        if (!empty($extraVars)) {
            extract($extraVars, EXTR_SKIP);
        }

        ob_start();
        include $renderFilePath;
        $output = ob_get_clean();

        // Restore the previous block context
        \WP_Block_Supports::$block_to_render = $previous_block_to_render;

        return $output;
    }
}
