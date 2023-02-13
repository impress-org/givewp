<?php

namespace Give\Framework\Http;

use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     * @unreleased
     */
    public function register()
    {
        give()->singleton(ConnectClient::class, static function () {
            $giveConnectUrl = (defined('GIVE_CONNECT_URL') && GIVE_CONNECT_URL)
                ? GIVE_CONNECT_URL
                : 'https://connect.givewp.com';

            return new ConnectClient($giveConnectUrl);
        });
    }

    /**
     * @inheritDoc
     * @unreleased
     */
    public function boot()
    {
    }
}
