<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock\Bootstrap;

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
        // register block
        register_block_type_from_metadata(
            GIVE_NEXT_GEN_DIR . 'src/NextGen/DonationForm/Blocks/DonationFormBlock/registration',
            ['render_callback' => [$this, 'renderBlock']]
        );
    }

    /**
     * @return false|string
     */
    public function renderBlock($attributes, $content)
    {
        ob_start();

        echo '<div id="root-give-next-gen-donation-form-block"><p>Hello</p></div>';

        return ob_get_clean();
    }
}
