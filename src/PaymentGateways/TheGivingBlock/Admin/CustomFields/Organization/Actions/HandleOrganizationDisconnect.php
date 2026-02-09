<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions;

use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * @unreleased
 */
class HandleOrganizationDisconnect
{
    /**
     * @since 1.0.0
     */
    public function __invoke()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'giveTgbNonce')) {
            wp_send_json_error(['message' => __('Invalid nonce. Please refresh the page and try again.', 'give')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'give')]);
        }

        OrganizationRepository::disconnect();

        wp_send_json_success(['message' => __('Organization disconnected successfully', 'give')]);
    }
}
