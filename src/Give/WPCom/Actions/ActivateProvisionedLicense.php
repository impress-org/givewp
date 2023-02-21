<?php

namespace Give\WPCom\Actions;

class ActivateProvisionedLicense
{
    public function __invoke(bool $result, array $payload, string $eventType)
    {
        if ( $eventType !== 'provision_license' ) {
            return $result;
        }

    }
}
