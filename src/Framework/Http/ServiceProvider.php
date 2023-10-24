<?php

namespace Give\Framework\Http;

use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 2.25.0
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     *
     * @since 3.0.0 Remove slash from GIVE_CONNECT_URL.
     * @since 2.25.0
     */
    public function register()
    {
        give()->singleton(ConnectClient::class, static function () {
            $giveConnectUrl = (defined('GIVE_CONNECT_URL') && GIVE_CONNECT_URL)
                ? untrailingslashit(GIVE_CONNECT_URL)
                : 'https://connect.givewp.com';

            return new ConnectClient($giveConnectUrl);
        });
    }

    /**
     * @inheritDoc
     * @since 2.25.0
     */
    public function boot()
    {
    }
}
