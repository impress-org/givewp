<?php

namespace Give\Addon\Actions;

use Give\Log\Log;
use Give_License;

/**
 * @unreleased
 */
class AutoActivateLicense
{
    public function __invoke($productId, $license)
    {
        $response = Give_License::request_license_api([
            'edd_action' => 'activate_license',
            'item_id' => $productId,
            'license' => $license,
        ], true);

        if(!$this->validate($response)) {
            Log::error('Failed to activate license for the Visual Donation Form Builder.', [
                'license' => $license,
                'productId' => $productId,
                'response' => $response,
            ]);
            return;
        }

        $licenses = get_option('give_licenses');
        $licenses[$license] = array_merge($response, [
            'is_all_access_pass' => false,
        ]);
        update_option('give_licenses', $licenses);

        give_refresh_licenses();
    }

    protected function validate($response): bool
    {
        return ! is_wp_error($response) && $response['success'];
    }
}
