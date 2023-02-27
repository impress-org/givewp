<?php

use DataTransferObjects\LicenseActivationResponse;
use Give\Log\Log;

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
    public function __invoke(string $license, string $productSlug)
    {
        $activationResponse = Give_License::request_license_api(
            [
                'edd_action' => 'activate_license',
                'item_name' => $productSlug,
                'license' => $license,
            ],
            true
        );

        $failed = false;

        if (is_wp_error($activationResponse)) {
            Log::error('Error occurred activating license from wp.com marketplace', [
                'license' => $license,
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

        $licenses = get_option('give_licenses', []);
        $licenses[$response->licenseKey] = $activationResponse;

        give_refresh_licenses();
    }
}
