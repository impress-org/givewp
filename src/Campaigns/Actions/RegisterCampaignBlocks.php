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

        foreach ($blocks as $block) {
            register_block_type(dirname(__DIR__) . '/Blocks/' . basename($block));
        }

        $this->enqueueBlocksAssets();
    }

    /**
     * @unreleased
     */
    private function enqueueBlocksAssets()
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
}
