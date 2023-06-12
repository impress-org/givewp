<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Routes\DonateRouteSignature;

class GenerateDonateRouteSignatureArgs
{
    /**
     * @unreleased
     */
    public function __invoke(DonateRouteSignature $signature, string $signatureId): array
    {
        return [
            'givewp-route-signature' => $signature->toHash(),
            'givewp-route-signature-id' => $signatureId,
            'givewp-route-signature-expiration' => $signature->expiration,
        ];
    }
}