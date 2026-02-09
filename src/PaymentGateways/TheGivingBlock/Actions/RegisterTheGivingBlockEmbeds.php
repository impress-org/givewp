<?php

namespace Give\PaymentGateways\TheGivingBlock\Actions;

use Give\PaymentGateways\TheGivingBlock\Embeds\Shortcodes\GiveTgbForm;

/**
 * Registers The Giving Block shortcode and block (embeds).
 *
 * @unreleased
 */
class RegisterTheGivingBlockEmbeds
{
    /**
     * @unreleased
     */
    public function __invoke(): void
    {
        add_shortcode('give_tgb_form', [new GiveTgbForm(), 'renderShortcode']);
        add_action('init', [$this, 'registerBlock']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueTgbEmbedsStyle']);
        add_action('wp_enqueue_scripts', [$this, 'enqueuePopupNoticeModalScript']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
    }

    /**
     * @unreleased
     */
    public function enqueuePopupNoticeModalScript(): void
    {
        wp_enqueue_script(
            'give-tgb-popup-notice-modal',
            GIVE_PLUGIN_URL . 'src/PaymentGateways/TheGivingBlock/assets/js/popupNoticeModal.js',
            [],
            GIVE_VERSION,
            true
        );
    }

    /**
     * @unreleased
     */
    public function enqueueTgbEmbedsStyle(): void
    {
        wp_enqueue_style(
            'give-tgb-embeds',
            GIVE_PLUGIN_URL . 'src/PaymentGateways/TheGivingBlock/assets/css/tgb-embeds.css',
            [],
            GIVE_VERSION
        );
    }

    /**
     * @unreleased
     */
    public function registerBlock(): void
    {
        $block_json = GIVE_PLUGIN_DIR . 'src/PaymentGateways/TheGivingBlock/Embeds/Blocks/DonationFormBlock/block.json';

        if (!file_exists($block_json)) {
            return;
        }

        register_block_type($block_json);
    }

    /**
     * @unreleased
     */
    public function enqueueBlockEditorAssets(): void
    {
        $asset_file = GIVE_PLUGIN_DIR . 'build/tgbDonationFormBlockApp.asset.php';

        if (file_exists($asset_file)) {
            $asset = require $asset_file;
            wp_enqueue_script(
                'give-tgb-donation-form-block',
                GIVE_PLUGIN_URL . 'build/tgbDonationFormBlockApp.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );
        }

        $this->enqueueTgbEmbedsStyle();
        $this->enqueuePopupNoticeModalScript();
    }
}
