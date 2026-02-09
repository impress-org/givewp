<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions;

/**
 * @unreleased
 */
class HandleApiRefresh
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'giveTgbNonce')) {
            wp_send_json_error(['message' => __('Invalid nonce. Please refresh the page and try again.', 'give')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'give')]);
        }

        $organizationId = sanitize_text_field(wp_unslash($_POST['organizationId'] ?? ''));

        if (empty($organizationId)) {
            wp_send_json_error(['message' => __('Organization ID is required.', 'give')]);
        }

        $result = RenderOrganizationDetails::refreshFromApi($organizationId);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'message' => __('Organization data refreshed successfully from API.', 'give')
        ]);
    }
}
