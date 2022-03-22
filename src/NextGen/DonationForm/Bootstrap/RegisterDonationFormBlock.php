<?php

namespace Give\NextGen\DonationForm\Bootstrap;

use Give\Framework\EnqueueScript;

/**
 * @unreleased
 */
class RegisterDonationFormBlock
{
    const SCRIPT_HANDLE = 'give-next-gen-donation-form-block-js';
    const STYLE_HANDLE = 'give-next-gen-donation-form-block-css';

    /*
     * @unreleased
     */
    public function __invoke()
    {
        $enqueueScripts = new EnqueueScript(
            self::SCRIPT_HANDLE,
            'public/js/give-next-gen-donation-form-block.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );

        $enqueueScripts->loadInFooter()->enqueue();

        // register styles
        wp_enqueue_style(
            self::STYLE_HANDLE,
            GIVE_NEXT_GEN_URL . 'public/js/give-next-gen-donation-form-block.css',
            [],
            GIVE_NEXT_GEN_VERSION
        );

        // register block
        register_block_type(
            GIVE_NEXT_GEN_URL . 'src/NextGen/DonationForm/resources/js/blocks/DonationFormBlock'
        );
    }
}
