<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock\Bootstrap;

use Give\Framework\EnqueueScript;

/**
 * @unreleased
 */
class RegisterDonationFormBlock
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        // register scripts
        $enqueueScripts = new EnqueueScript(
            'give-next-gen-donation-form-block-js',
            'public/js/give-next-gen-donation-form-block.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );

        $enqueueScripts->loadInFooter()->enqueue();

        // register styles
        wp_enqueue_style(
            'give-next-gen-donation-form-block-css',
            GIVE_NEXT_GEN_URL . 'public/js/give-next-gen-donation-form-block.css',
            [],
            GIVE_NEXT_GEN_VERSION
        );

        // register block
        register_block_type(
            GIVE_NEXT_GEN_URL . 'src/NextGen/DonationForm/Blocks/DonationFormBlock/registration',
            ['render_callback' => [$this, 'renderBlock']]
        );
    }
}
