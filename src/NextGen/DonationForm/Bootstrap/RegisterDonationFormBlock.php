<?php

namespace Give\NextGen\DonationForm\Bootstrap;

/**
 * @unreleased
 */
class RegisterDonationFormBlock  {
    const SCRIPT_HANDLE = 'give-next-gen-donation-form-block-js';
    const STYLE_HANDLE = 'give-next-gen-donation-form-block-css';

    /*
     * @unreleased
     */
    public function __invoke()
    {
        // register scripts
        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            GIVE_NEXT_GEN_URL . 'public/js/give-next-gen-donation-form-block.js',
            [],
            GIVE_NEXT_GEN_VERSION,
            true
        );

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
