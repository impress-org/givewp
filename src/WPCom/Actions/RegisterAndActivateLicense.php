<?php

namespace Give\WPCom\Actions;

use Give\Log\Log;
use Give\WPCom\DataTransferObjects\LicenseActivationResponse;
use Give_License;
use WP_Error;

/**
 * @unreleased
 */
class RegisterAndActivateLicense
{
    /**
     * @unreleased
     *
     * @return WP_Error|void
     */
    public function __invoke(string $license, int $productId, string $productSlug)
    {
        $activationResponse = Give_License::request_license_api(
            [
                'edd_action' => 'activate_license',
                'item_id' => $productId,
                'license' => $license,
            ],
            true
        );

        $failed = false;

        if (is_wp_error($activationResponse)) {
            Log::error('Error occurred activating license from wp.com marketplace', [
                'license' => $license,
                'productId' => $productId,
                'productSlug' => $productSlug,
                'response' => $activationResponse,
            ]);

            $failed = true;
        }

        $response = LicenseActivationResponse::fromArray($activationResponse);

        if ( ! $response->success) {
            Log::error('Failed to activate license from wp.com marketplace', [
                'license' => $license,
                'productSlug' => $productSlug,
                'productId' => $productId,
                'response' => $activationResponse,
            ]);

            $failed = true;
        }

        if ($failed) {
            return new WP_Error(
                'givewp-wpcom-license-activation-error',
                sprintf(
                    __(
                        'There was an error activating your license from the wp.com marketplace. Please contact GiveWP <a href="%s">Customer Support team</a> for assistance.',
                        'give'
                    ),
                    'https://givewp.com/support'
                )
            );
        }

        $licenses = get_option('give_licenses');
        $licenses[$license] = array_merge($activationResponse, [
            'is_all_access_pass' => false,
        ]);
        update_option('give_licenses', $licenses);

        give_refresh_licenses();
    }
}
