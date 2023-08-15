<?php

namespace Give\DonationForms\Blocks\DonationFormBlock;

use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;

class Block
{

    /**
     * @since 3.0.0
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
