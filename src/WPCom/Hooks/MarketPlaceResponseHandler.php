<?php

namespace Give\WPCom\Hooks;

use Give\WPCom\Actions\RegisterAndActivateLicense;
use WP_Error;

class MarketPlaceResponseHandler
{
    /**
     * @param array{license: string, productId: int, productSlug: string} $licensePayload
     *
     * @return bool|WP_Error|void
     */
    public function __invoke(bool $result, array $licensePayload, string $eventType)
    {
        if ($eventType !== 'provision_license') {
            return $result;
        }

        return give(RegisterAndActivateLicense::class)(
            $licensePayload['license'],
            $licensePayload['productId'],
            $licensePayload['productSlug']
        );
    }
}
