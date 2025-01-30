<?php

namespace Give\Campaigns\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class RegisterCampaignBlocks
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $blocks = glob(dirname(__DIR__) . '/Blocks/*', GLOB_ONLYDIR);

        array_map('register_block_type', $blocks);
        array_map([$this, 'registerBlockAssets'], $blocks);

        if (is_admin()) {
            $this->enqueueAdminBlocksAssets();
        }

        $this->registerSharedStyles();
    }

    /**
     * @unreleased
     */
    private function enqueueAdminBlocksAssets(): void
    {
        $handleName = 'givewp-campaign-blocks';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignBlocks.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlocks.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);
        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlocks.css',
            /** @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/#usage */
            ['wp-components'],
            $scriptAsset['version']
        );
    }

    /**
     * @unreleased
     */
    private function registerSharedStyles(): void
    {
        wp_register_style(
            'givewp-campaign-blocks-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
        );
    }

    /**
     * @unreleased
     */
    private function registerBlockAssets($block): void
    {
        if ( ! file_exists($block . '/block.json')) {
            return;
        }

        $blockSettings = json_decode(file_get_contents($block . '/block.json'), true);
        $blockName = basename($block);

        if (isset($blockSettings['script'])) {
            wp_register_script(
                "givewp-{$blockName}-script",
                GIVE_PLUGIN_URL . "build/Campaigns/Blocks/{$blockName}/script.js",
                [],
                $blockSettings['version'],
                true
            );
        }

        if (isset($blockSettings['style'])) {
            wp_register_style(
                "givewp-{$blockName}-style",
                GIVE_PLUGIN_URL . "build/Campaigns/Blocks/{$blockName}/style.css",
                ['givewp-design-system-foundation', 'givewp-campaign-blocks-fonts'],
                $blockSettings['version']
            );
        }
    }
}
