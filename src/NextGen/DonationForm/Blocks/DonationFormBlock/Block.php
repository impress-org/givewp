<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock;

class Block {
    /**
     * @unreleased
     *
     * @return void
     */
    public function register()
    {
        register_block_type_from_metadata(
            __DIR__,
            ['render_callback' => [$this, 'render']]
        );
    }

    /**
     * @unreleased
     *
     * @param array $attributes
     *
     * @return string
     */
    public function render($attributes)
    {
        ob_start(); ?>

        <div id="root-give-next-gen-donation-form-block"></div>

        <script>window.giveNextGenExports = <?= wp_json_encode( $attributes ) ?>;</script>

        <?php return ob_get_clean();
    }
}
