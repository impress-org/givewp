<?php

namespace Give\Campaigns\Shortcodes;

use Give\Campaigns\Actions\LoadCampaignPublicOptions;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.2.0
 */
class CampaignGridShortcode
{
    /**
     * @since 4.7.0 updated to use ShortcodeRenderController
     * @since 4.2.0
     *
     * @param array $atts
     *
     * @return string
     */
    public function renderShortcode($atts): string
    {
        $this->loadAssets();
        $attributes = $this->parseAttributes($atts);

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignGrid/render.php';

        return ShortcodeRenderController::renderWithBlockContext(
            $renderFile,
            'givewp/campaign-grid-block',
            $attributes
        );
    }

    /**
     * @since 4.3.0 Use info from asset.php file and set script translations
     * @since 4.2.0
     */
    public function loadAssets()
    {
        give(LoadCampaignPublicOptions::class)();

        $handleName = 'givewp-campaign-grid-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignGridApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignGridApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignGridApp.css',
            [],
            $asset['version']
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * @since TBD Sanitize string attributes and validate against the block attribute schema.
     * @since 4.2.0
     */
    private function parseAttributes($atts): array
    {
        $atts = shortcode_atts([
            'layout'           => 'full',
            'show_image'       => true,
            'show_description' => true,
            'show_goal'        => true,
            'sort_by'          => 'date',
            'order_by'         => 'desc',
            'per_page'         => 6,
            'show_pagination'  => true,
            'filter_by'        => null,
        ], $atts, 'givewp_campaign_grid');

        return [
            'layout'          => $this->restrictToBlockAttribute('layout', $atts['layout']),
            'showImage'       => filter_var($atts['show_image'], FILTER_VALIDATE_BOOLEAN),
            'showDescription' => filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN),
            'showGoal'        => filter_var($atts['show_goal'], FILTER_VALIDATE_BOOLEAN),
            'sortBy'          => $this->restrictToBlockAttribute('sortBy', $atts['sort_by']),
            'orderBy'         => $this->restrictToBlockAttribute('orderBy', $atts['order_by']),
            'filterBy'        => $atts['filter_by'] ? sanitize_text_field($atts['filter_by']) : $atts['filter_by'],
            'perPage'         => (int)$atts['per_page'],
            'showPagination'  => filter_var($atts['show_pagination'], FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Restricts a value to the choices defined by the block's own attribute schema.
     *
     * @since TBD
     */
    private function restrictToBlockAttribute(string $attribute, $value)
    {
        $schema = $this->getBlockAttributes()[$attribute] ?? [];

        if (empty($schema['enum']) || in_array($value, $schema['enum'], true)) {
            return $value;
        }

        return $schema['default'] ?? $value;
    }

    /**
     * Returns the block's attribute definitions, the same metadata used by the editor controls.
     *
     * @since TBD
     */
    private function getBlockAttributes(): array
    {
        static $attributes;

        if ($attributes === null) {
            $metadata = wp_json_file_decode(
                GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignGrid/block.json',
                ['associative' => true]
            );

            $attributes = isset($metadata['attributes']) ? $metadata['attributes'] : [];
        }

        return $attributes;
    }
}
