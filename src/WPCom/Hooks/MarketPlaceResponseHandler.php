<?php

namespace Hooks;

use RegisterAndActivateLicense;
use WP_Error;

class MarketPlaceResponseHandler
{
    /**
     * @return bool|WP_Error|void
     */
    public function __invoke(bool $result, array $licensePayload, string $eventType)
    {
        if ($eventType !== 'provision_license') {
            return $result;
        }

        $success = give(RegisterAndActivateLicense::class)($licensePayload['license'], $licensePayload['productSlug']);

        if (is_wp_error($success)) {
            return $success;
        }
    }
}
