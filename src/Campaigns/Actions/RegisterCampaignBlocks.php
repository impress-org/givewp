<?php

namespace Give\Campaigns\Actions;

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
    }
}
