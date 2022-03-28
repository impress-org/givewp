<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock;

use Give\Framework\EnqueueScript;
use Give\Framework\FieldsAPI\Email;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Hidden;
use Give\Framework\FieldsAPI\Text;

class Block
{
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
     * @param  array  $attributes
     *
     * @return string
     * @throws EmptyNameException
     */
    public function render($attributes)
    {
        $donationForm = $this->createForm($attributes);

        $exports = [
            'attributes' => $attributes,
            'form' => $donationForm->jsonSerialize()
        ];

        // enqueue front-end scripts
        // since this is using render_callback viewScript in blocks.json will not work.
        $enqueueScripts = new EnqueueScript(
            'give-next-gen-donation-form-block-js',
            'src/NextGen/DonationForm/Blocks/DonationFormBlock/build/view.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );

        $enqueueScripts->loadInFooter()->enqueue();

        ob_start(); ?>

        <div id="root-give-next-gen-donation-form-block"></div>

        <script>window.giveNextGenExports = <?= wp_json_encode($exports) ?>;</script>

        <?php
        return ob_get_clean();
    }

    /**
     * @unreleased
     *
     * @param  array  $attributes
     * @return Form
     * @throws EmptyNameException
     */
    private function createForm($attributes)
    {
        $donationForm = new Form('DonationForm');

        $donationForm->append(
            Text::make('firstName')
                ->label(__('First Name', 'give'))
                ->required(),

            Text::make('lastName')
                ->label(__('Last Name', 'give'))
                ->required(),

            Email::make('email')
                ->label(__('Email', 'give'))
                ->required()
                ->emailTag('email'),

            Text::make('donationAmount')
                ->label(__('Donation Amount', 'give'))
                ->defaultValue(50)
                ->required(),

            Hidden::make('formId')
                ->defaultValue($attributes['formId'])
        );

        return $donationForm;
    }
}
