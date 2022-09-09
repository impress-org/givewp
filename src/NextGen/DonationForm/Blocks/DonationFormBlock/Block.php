<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock;

use Give\NextGen\DonationForm\Blocks\DonationFormBlock\Controllers\BlockRenderController;

class Block
{

    /**
     * @unreleased
     *
     * @return void
     */
    public function register()
    {
        register_block_type(
            __DIR__,
            [
                'render_callback' => [(new BlockRenderController()), 'render']
            ]
        );
    }
}
