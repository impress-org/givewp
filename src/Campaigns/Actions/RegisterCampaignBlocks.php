<?php

namespace Give\Campaigns\Actions;

class RegisterCampaignBlocks
{
    public function __invoke()
    {
        $blocks = glob(dirname(__DIR__) . '/Blocks/*', GLOB_ONLYDIR);

        foreach ($blocks as $block) {
            register_block_type(dirname(__DIR__) . '/Blocks/' . basename($block));
        }
    }
}
